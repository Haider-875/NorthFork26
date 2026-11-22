const http = require('http');
const fs = require('fs');
const path = require('path');
const url = require('url');

// Load environment variables from .env
const ENV_FILE = path.join(__dirname, '.env');
if (fs.existsSync(ENV_FILE)) {
  const envContent = fs.readFileSync(ENV_FILE, 'utf8');
  envContent.split(/\r?\n/).forEach(line => {
    line = line.trim();
    if (!line || line.startsWith('#')) return;
    const eqIdx = line.indexOf('=');
    if (eqIdx > 0) {
      const key = line.slice(0, eqIdx).trim();
      let val = line.slice(eqIdx + 1).trim();
      val = val.replace(/^["']|["']$/g, '');
      if (!process.env[key]) {
        process.env[key] = val;
      }
    }
  });
}

const PORT = process.env.PORT || 8000;
const DB_FILE = path.join(__dirname, 'storage', 'app', 'database.json');
const LIVE_SERVICES_FILE = path.join(__dirname, 'live_Service.json');
const CONV_FILE = path.join(__dirname, 'storage', 'app', 'chat_conversations.json');

function loadConversations() {
  try {
    if (fs.existsSync(CONV_FILE)) {
      return JSON.parse(fs.readFileSync(CONV_FILE, 'utf8') || '{}');
    }
  } catch (e) {}
  return {};
}

function saveConversations(data) {
  try {
    const dir = path.dirname(CONV_FILE);
    if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });
    fs.writeFileSync(CONV_FILE, JSON.stringify(data, null, 2), 'utf8');
  } catch (e) {}
}

// Chatbot in-memory rate-limiting store
const chatRateLimits = new Map(); // key => { lastReq: timestamp, reqCount: number }

function checkChatRateLimit(sessionId, clientIp) {
  const now = Date.now();
  const key = sessionId || clientIp;
  let record = chatRateLimits.get(key);
  if (!record) {
    record = { lastReq: now, reqCount: 1 };
    chatRateLimits.set(key, record);
    return true;
  }
  if (now - record.lastReq > 60000) {
    record.lastReq = now;
    record.reqCount = 1;
    return true;
  }
  record.reqCount += 1;
  return record.reqCount <= 25; // max 25 messages per minute
}

// System prompt for Cloudflare Workers AI - Concise Automotive Service Advisor
const NFA_SYSTEM_PROMPT = `You are the official AI Auto Service Advisor for North Fork Auto in Talkeetna, Alaska (Shop Phone: (907) 733-3030, Emergency Towing: (907) 232-3859, Mon-Fri 8am-5pm, closed weekends, Location: Talkeetna Spur Road).
Website: https://www.northforkauto.com/

RULES:
1. Respond in 1 to 3 short, helpful, conversational sentences (under 60 words total).
2. ALWAYS remember and reference the customer's vehicle and previous issues when responding.
3. For brake inspections, state it takes roughly 30–60 minutes. Do not invent exact repair prices without an inspection.
4. If check engine light is solid, advise a diagnostic scan. If flashing, warn them to pull over immediately.
5. Do NOT output markdown bullet lists, numbered lists, or lengthy essays.
6. Be warm, professional, and invite them to schedule online or call our shop.`;

// Shop ground truth information for instant deterministic responses (<20ms)
const SHOP_INFO = {
  name: 'North Fork Auto',
  phone: '(907) 733-3030',
  emergencyPhone: '+1 (907) 232-3859',
  hours: 'Monday through Friday, 8:00 AM – 5:00 PM',
  weekend: 'Closed Saturday and Sunday',
  location: 'Talkeetna Spur Road, Talkeetna, AK 99676',
  warranty: '12-month / 12,000-mile warranty on qualifying parts and labor',
};

function extractVehicleAndIssueContext(text, context) {
  if (!context) return;
  const raw = text || '';
  const lower = raw.toLowerCase();

  const ym = lower.match(/\b(19[89]\d|20[0-2]\d)\b/);
  if (ym) context.vehicle_year = ym[1];

  const mm = lower.match(/\b(ford|chevy|chevrolet|gmc|dodge|ram|toyota|honda|subaru|nissan|jeep|bmw|audi|mercedes|volkswagen|vw|hyundai|kia|lexus|mazda)\b/i);
  if (mm) context.vehicle_make = mm[1].charAt(0).toUpperCase() + mm[1].slice(1).toLowerCase();

  const modm = lower.match(/\b(f-?150|f-?250|f-?350|silverado|sierra|outback|forester|crosstrek|tacoma|tundra|camry|corolla|civic|accord|cr-?v|wrangler|cherokee|grand cherokee|ram 1500|ram 2500|explorer|escape|ranger)\b/i);
  if (modm) context.vehicle_model = modm[1].toUpperCase();

  const parts = [context.vehicle_year, context.vehicle_make, context.vehicle_model].filter(Boolean);
  if (parts.length > 0) context.vehicle_full = parts.join(' ');

  if (/\b(brake|brakes|squeak\w*|grind\w*|rotor|caliper|pad)\b/i.test(lower)) {
    context.current_issue = 'Brake noise / inspection';
    context.service_needed = 'Brake Repair & Replacement';
  } else if (/\b(check engine|engine light|cel|warning light|misfire)\b/i.test(lower)) {
    context.current_issue = 'Check Engine Light diagnostics';
    context.service_needed = 'Diagnostic Inspection';
  } else if (/\b(oil|oil change|lube|filter)\b/i.test(lower)) {
    context.current_issue = 'Oil & Filter Service';
    context.service_needed = 'Oil & Filter Service';
  }
}

function getPreviousTopic(history) {
  if (!Array.isArray(history) || history.length === 0) return null;
  const recent = history.slice(-4).reverse();
  for (const msg of recent) {
    const text = (msg.content || '').toLowerCase();
    if (text.includes('brake')) return 'brakes';
    if (text.includes('engine') || text.includes('motor')) return 'engine';
    if (text.includes('oil')) return 'oil_change';
    if (text.includes('transmission')) return 'transmission';
    if (text.includes('battery') || text.includes('electrical')) return 'electrical';
    if (text.includes('appointment') || text.includes('booking')) return 'appointment';
  }
  return null;
}

function classifyAndResolveIntent(message, history = [], database = { bookings: [] }, context = {}) {
  const text = (message || '').trim();
  const lower = text.toLowerCase();
  const prevTopic = getPreviousTopic(history);

  // 0A. VEHICLE MEMORY INTENT: "What vehicle did I tell you I have?"
  if (/\bwhat (vehicle|truck|car|automobile)\b.*\b(tell you|mention|say|told you|have|drive)\b/i.test(lower) ||
      /\bwhat did i (tell you|say)\b.*\b(have|drive|my vehicle|my car|my truck)\b/i.test(lower)) {
    if (context.vehicle_full) {
      return {
        intent: 'VEHICLE_MEMORY',
        reply: `You mentioned you have a ${context.vehicle_full}.`
      };
    } else if (context.vehicle_make) {
      return {
        intent: 'VEHICLE_MEMORY',
        reply: `You mentioned you drive a ${context.vehicle_make}.`
      };
    }
  }

  // 0B. PARTS INQUIRY INTENT: "Can you check whether you have parts for it?"
  if (/\b(parts?|components?)\b.*\b(have|stock|available|for it|in stock)\b/i.test(lower) ||
      /\bdo you have parts for (it|my truck|my car)\b/i.test(lower)) {
    const veh = context.vehicle_full || context.vehicle_make || 'your vehicle';
    if (context.current_issue === 'Brake noise / inspection' || lower.includes('brake') || prevTopic === 'brakes') {
      return {
        intent: 'PARTS_INQUIRY',
        reply: `Which brake part do you need? We stock and source quality pads, rotors, and calipers for ${veh}, but I'll be glad to verify our current inventory with our parts team.`
      };
    }
    return {
      intent: 'PARTS_INQUIRY',
      reply: `Which part does your vehicle need? We stock and source parts for ${veh}, but I'll need to check our live inventory once we confirm your vehicle specs.`
    };
  }

  // 0C. CHECK ENGINE LIGHT FOLLOW-UP: "It is staying solid." / "It is flashing."
  if (/\b(staying solid|is solid|it's solid|steady light|solid light)\b/i.test(lower) ||
      (prevTopic === 'engine' && lower.includes('solid') && !lower.includes('flashing'))) {
    return {
      intent: 'DIAGNOSTIC_CEL_SOLID',
      reply: `A steady check engine light indicates an emissions or sensor trouble code that should be scanned. Since it isn't flashing, it is generally safe to drive to our shop on Talkeetna Spur Road for a computerized diagnostic inspection.`
    };
  }
  if (/\b(is flashing|it's flashing|blinking|flashing)\b/i.test(lower) && (prevTopic === 'engine' || lower.includes('engine') || lower.includes('light'))) {
    return {
      intent: 'DIAGNOSTIC_CEL_FLASHING',
      reply: `A flashing check engine light indicates an active engine misfire that can cause severe catalytic converter damage. Please safely pull over, avoid driving, and call our shop at ${SHOP_INFO.phone} or our 24/7 emergency dispatch at ${SHOP_INFO.emergencyPhone}.`
    };
  }

  // 0D. PURE VEHICLE STATEMENT: "I have a 2018 Ford F-150."
  if (/^(i (have|drive|got)|my (truck|car|vehicle) is)\s+(a\s+)?(19\d\d|20\d\d)?\s*([a-z0-9\s\-]+)$/i.test(text) && !/\b(noise|light|broken|fix|repair|squeak|leak|oil|smoke|grind)\b/i.test(lower)) {
    const veh = context.vehicle_full || 'truck';
    return {
      intent: 'VEHICLE_INFO_STATED',
      reply: `Got it! What issue are you experiencing with your ${veh}?`
    };
  }

  // 0E. APPOINTMENT POLICY QUESTION: "Do I need an appointment for an oil change?"
  if (/\b(do i need|is (it|an?)|are)\b.*\b(appointment|booking|scheduled?)\b/i.test(lower) ||
      /\b(appointment required|need an appointment)\b/i.test(lower)) {
    return {
      intent: 'APPOINTMENT_POLICY_INQUIRY',
      reply: `Appointments are recommended so our technicians have a dedicated bay ready for your vehicle, but we also welcome walk-ins during regular shop hours (Monday–Friday, 8:00 AM–5:00 PM). Would you like to schedule an appointment?`
    };
  }

  // 0F. GENERAL MAINTENANCE / SHOP RECOMMENDATION
  if (/\b(maintain|maintenance|reliable place|tune up|service my|service our|fix my truck|fix my car)\b/i.test(lower)) {
    const veh = context.vehicle_full || (context.vehicle_make ? context.vehicle_make : 'your vehicle');
    return {
      intent: 'GENERAL_MAINTENANCE_INQUIRY',
      reply: `You've come to the right place! At North Fork Auto in Talkeetna, we specialize in reliable maintenance, diagnostics, and repairs for ${veh} and all vehicle makes. Give us a call at ${SHOP_INFO.phone} or schedule online!`
    };
  }

  // 1. APPOINTMENT LOOKUP
  const confId = extractConfirmationId(text);
  if (confId || (/\b(check|status|track|find|look up|where is|when is)\b.*\b(appointment|booking|meeting|schedule)\b/i.test(lower) && !/\b(book|new|make|schedule an?)\b/i.test(lower))) {
    if (confId) {
      const norm = normalizeConfirmationId(confId);
      const bookings = database.bookings || [];
      const found = bookings.find(b => {
        const bId = normalizeConfirmationId(b.booking_number || b.booking_reference || b.confirmation_id || String(b.id || ''));
        return bId === norm;
      });

      if (found) {
        const service = found.service_name || found.service_type || 'Automotive Service';
        const date = found.preferred_date || 'Upcoming';
        const time = found.preferred_time || 'Regular Shop Hours';
        const status = (found.status || 'pending').toUpperCase();
        const veh = [found.vehicle_year, found.vehicle_make, found.vehicle_model].filter(Boolean).join(' ') || 'Vehicle on file';

        return {
          intent: 'APPOINTMENT_LOOKUP_FOUND',
          reply: `I found your appointment!\n\n• Appointment ID: ${norm}\n• Date: ${date}\n• Time: ${time}\n• Service: ${service}\n• Status: ${status}\n\nWould you like help with anything else?`,
          appointment: {
            confirmation_id: norm,
            date,
            time,
            service_name: service,
            status,
            vehicle: veh,
            verified: true
          }
        };
      } else {
        return {
          intent: 'APPOINTMENT_LOOKUP_NOT_FOUND',
          reply: `I checked our booking records but couldn't locate an appointment with confirmation number ${confId}. Please double-check the ID or call our Talkeetna shop at ${SHOP_INFO.phone}.`
        };
      }
    } else {
      return {
        intent: 'APPOINTMENT_LOOKUP_PROMPT',
        reply: `I can check your appointment details right away! Please provide your confirmation number (like NFA-2026-8659) or the phone number used during booking.`
      };
    }
  }

  // 2. APPOINTMENT RESCHEDULING & CANCELLATION
  if (/\b(reschedule|change|move|postpone)\b.*\b(appointment|date|time|booking)\b/i.test(lower) || /\b(cancel|drop)\b.*\b(appointment|booking)\b/i.test(lower)) {
    if (confId) {
      return {
        intent: 'APPOINTMENT_RESCHEDULE_PROMPT',
        reply: `I can help update appointment ${confId}. What new date and time between Monday–Friday, 8:00 AM–5:00 PM works best for you? You can also call us at ${SHOP_INFO.phone}.`
      };
    }
    return {
      intent: 'APPOINTMENT_RESCHEDULE_NEED_ID',
      reply: `I would be happy to help you reschedule or adjust your service! Please provide your appointment confirmation number (or call us at ${SHOP_INFO.phone}) so I can update your booking.`
    };
  }

  // 3. APPOINTMENT PREVIOUS QUESTION
  if (/\b(previous|past|earlier|last)\b.*\b(appointment|visit|repair|service)\b/i.test(lower)) {
    return {
      intent: 'APPOINTMENT_HISTORY',
      reply: `I'd be happy to help look up your previous service records! Please provide your confirmation ID or the phone number associated with your vehicle, or call us at ${SHOP_INFO.phone}.`
    };
  }

  // 4. BOOKING AVAILABILITY & REQUEST
  if (/\b(can i come tomorrow|available tomorrow|availability|next available|available slots?|open slots?)\b/i.test(lower) ||
      (/\b(book|schedule|make|reserve)\b.*\b(appointment|service|inspection|oil|brake|repair)\b/i.test(lower)) ||
      (lower.includes('come tomorrow') || lower.includes('bring my car') || lower.includes('bring my truck'))) {
    return {
      intent: 'APPOINTMENT_BOOKING',
      reply: `Sure! I can help check appointment availability. Our shop is open Monday through Friday, 8:00 AM to 5:00 PM. What service does your vehicle need?`,
      action: 'open_booking_modal'
    };
  }

  // 5. BUSINESS HOURS & SCHEDULE
  if (/\b(saturday|sunday|weekend)\b/i.test(lower)) {
    return {
      intent: 'BUSINESS_HOURS_WEEKEND',
      reply: `We are closed on Saturday and Sunday. North Fork Auto is open Monday through Friday from 8:00 AM to 5:00 PM. Would you like to schedule an appointment during the week?`
    };
  }
  if (/\b(close|closing time|open|hours|what time do you close|operating hours|work hours|when are you open)\b/i.test(lower)) {
    return {
      intent: 'BUSINESS_HOURS_GENERAL',
      reply: `We are open Monday through Friday from 8:00 AM to 5:00 PM (closed Saturday and Sunday). Is there a specific day you'd like to bring your vehicle in?`
    };
  }

  // 6. LOCATION & DIRECTIONS
  if (/\b(where are you|location|address|directions|where is the shop|how do i find you|where located)\b/i.test(lower)) {
    return {
      intent: 'LOCATION_DIRECTIONS',
      reply: `We are located on Talkeetna Spur Road in Talkeetna, Alaska (AK 99676), proudly serving Talkeetna and the entire Mat-Su Valley.`
    };
  }

  // 7. CONTACT, PHONE, EMERGENCY, HUMAN ASSISTANCE
  if (/\b(speak to someone|talk to someone|speak to a person|human|receptionist|advisor|call you|phone number|contact number)\b/i.test(lower)) {
    return {
      intent: 'CONTACT_HUMAN',
      reply: `You can reach our service team directly at ${SHOP_INFO.phone} Monday–Friday from 8:00 AM to 5:00 PM. For after-hours emergency towing, call ${SHOP_INFO.emergencyPhone}.`
    };
  }
  if (/\b(emergency|towing|after hours|breakdown)\b/i.test(lower)) {
    return {
      intent: 'CONTACT_EMERGENCY',
      reply: `For after-hours emergencies or roadside towing in the Talkeetna area, please call our 24/7 emergency dispatch line at ${SHOP_INFO.emergencyPhone}. For regular service, call ${SHOP_INFO.phone}.`
    };
  }

  // 8. WALK-IN AVAILABILITY
  if (/\b(walk in|without an appointment|drop in|no appointment)\b/i.test(lower)) {
    return {
      intent: 'WALK_IN_POLICY',
      reply: `We welcome walk-ins for urgent inspections and safety concerns whenever our bay schedule permits! However, booking in advance guarantees your time slot without waiting.`
    };
  }

  // 9. WARRANTY
  if (/\b(warranty|guarantee|peace of mind)\b/i.test(lower)) {
    return {
      intent: 'WARRANTY_INFO',
      reply: `Yes! We stand behind our workmanship with a nationwide 12-month / 12,000-mile warranty on all qualifying parts and repair services.`
    };
  }

  // 10. CONTEXTUAL FOLLOW-UP: DURATION ("How long does it take?" / "How much time will it take?")
  if (/\bhow (much time|long) (will it take|does it take|is it)\b/i.test(lower) || /\bhow long\b/i.test(lower) || /\bhow much time\b/i.test(lower)) {
    if (context.current_issue === 'Brake noise / inspection' || prevTopic === 'brakes' || lower.includes('brake')) {
      return {
        intent: 'DURATION_BRAKES',
        reply: `Brake inspection usually takes around 30–60 minutes, depending on the issue. If repairs are needed, we'll confirm the estimated time after inspection.`
      };
    }
    if (prevTopic === 'oil_change' || lower.includes('oil')) {
      return {
        intent: 'DURATION_OIL',
        reply: `A comprehensive oil, filter, and multi-point vehicle inspection typically takes 30 to 45 minutes.`
      };
    }
    return {
      intent: 'DURATION_GENERAL',
      reply: `Service turnaround times vary by vehicle and the specific repairs needed. Once we inspect your vehicle, we'll give you a reliable time estimate.`
    };
  }

  // 11. PRICING & ESTIMATES (Strict no-hallucination policy; excluded if asking about duration/time)
  if (/\b(cost|price|pricing|quote|estimate|charge|rate)\b/i.test(lower) || (/\bhow much\b/i.test(lower) && !/\b(time|long|hours?|minutes?)\b/i.test(lower))) {
    if (lower.includes('oil')) {
      return {
        intent: 'PRICING_OIL_CHANGE',
        reply: `Oil change pricing varies depending on your engine size, oil capacity, and whether your vehicle requires synthetic or conventional blend. Please call us at ${SHOP_INFO.phone} with your vehicle details for an exact quote!`
      };
    }
    return {
      intent: 'PRICING_GENERAL',
      reply: `Repair and service costs depend on your specific vehicle's year, make, model, and the parts required. We provide honest, transparent estimates after an inspection—give us a call at ${SHOP_INFO.phone}!`
    };
  }

  // 12. SPECIFIC SERVICES & VEHICLE INQUIRIES
  if (/\b(ford|chevy|chevrolet|gmc|dodge|ram|toyota|honda|subaru|nissan|jeep)\b/i.test(lower) && /\b(truck|vehicle|car|suv|repair|work on|service)\b/i.test(lower)) {
    const make = (lower.match(/\b(ford|chevy|chevrolet|gmc|dodge|ram|toyota|honda|subaru|nissan|jeep)\b/i) || [])[0] || 'vehicles';
    const capMake = make.charAt(0).toUpperCase() + make.slice(1);
    return {
      intent: 'VEHICLE_MAKE_EXPERTISE',
      reply: `Yes, we provide diagnostics, brake repairs, engine repairs, and general maintenance for ${capMake} vehicles. What issue are you experiencing with your ${capMake}?`
    };
  }

  if (/\b(brake|brakes)\b/i.test(lower) && /\b(noise|squeak\w*|squeal\w*|grind\w*|sound\w*|rubbing|spongy|soft)\b/i.test(lower)) {
    return {
      intent: 'DIAGNOSTIC_BRAKE_NOISE',
      reply: `Absolutely! That could be related to your brake pads, rotors, or another brake component. We can inspect it and identify the issue. Would you like to schedule a brake inspection?`
    };
  }

  if (/\b(check engine|engine light|cel|warning light)\b/i.test(lower)) {
    return {
      intent: 'DIAGNOSTIC_CHECK_ENGINE',
      reply: `We can help diagnose that. Our computerized diagnostic inspection can identify potential issues. Is the check engine light flashing or staying solid?`
    };
  }

  if (/\b(what services|services do you offer|what do you do|available services)\b/i.test(lower)) {
    return {
      intent: 'SERVICES_LIST',
      reply: `We provide full-service automotive care including computerized diagnostics, brake repair, oil & maintenance, engine repair, transmission service, suspension, batteries & electrical, and AC/cooling. What does your vehicle need?`
    };
  }

  return null;
}

async function callCloudflareWorkersAI(historyMessages, context = {}) {
  const accountId = process.env.CLOUDFLARE_ACCOUNT_ID || 'e170edb3cd40ca17903aaa54eaa71c5e';
  let token = (process.env.CLOUDFLARE_API_TOKEN || Buffer.from('Y2Z1dF9ld3pYR1dPNUZLakFVQlJ0WHNETThNeEJ6TlphNzBYMmNRVGNYa1BVYWM3Yzg0YWQ=', 'base64').toString('utf8')).trim();
  token = token.replace(/^(Authorization:\s*Bearer\s*|Bearer\s*)/i, '').replace(/^["']|["']$/g, '');
  const model = process.env.CLOUDFLARE_AI_MODEL || '@cf/google/gemma-4-26b-a4b-it';

  const endpoint = `https://api.cloudflare.com/client/v4/accounts/${accountId}/ai/run/${model}`;

  const contextLines = [];
  if (context.vehicle_full) {
    contextLines.push(`Customer Vehicle: ${context.vehicle_full}`);
  } else if (context.vehicle_make) {
    contextLines.push(`Customer Vehicle: ${context.vehicle_make}`);
  }
  if (context.current_issue) {
    contextLines.push(`Current Issue: ${context.current_issue}`);
  }
  const contextSummary = contextLines.length > 0 ? `\nActive Conversation Memory:\n${contextLines.join('\n')}\n` : '';

  const systemPrompt = `${NFA_SYSTEM_PROMPT}${contextSummary}`;

  const payloadMessages = [
    { role: 'system', content: systemPrompt }
  ];

  const recent = historyMessages.slice(-8);
  for (const m of recent) {
    if (m.content && ['user', 'assistant'].includes(m.role)) {
      payloadMessages.push({
        role: m.role,
        content: String(m.content).slice(0, 1000)
      });
    }
  }

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 20000);

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        messages: payloadMessages,
        max_tokens: 800
      }),
      signal: controller.signal
    });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const content = data?.result?.choices?.[0]?.message?.content || data?.result?.response || data?.result?.content;
      if (content && content.trim()) {
        return { success: true, reply: content.trim() };
      }
    }

    if (res.status === 429) {
      return {
        success: false,
        reply: "Our AI Assistant is currently experiencing high volume. Please give our shop a call directly at (907) 733-3030 for immediate service!"
      };
    }

    const errText = await res.text().catch(() => '');
    console.error('Cloudflare AI Error:', res.status, errText);
    return { success: false, reply: null };
  } catch (err) {
    clearTimeout(timeoutId);
    console.error('Cloudflare AI fetch failed:', err.message);
    return { success: false, reply: null };
  }
}

function getChatbotHtml() {
  const componentPath = path.join(__dirname, 'resources', 'views', 'components', 'chatbot.blade.php');
  if (fs.existsSync(componentPath)) {
    return fs.readFileSync(componentPath, 'utf8');
  }
  return '';
}

// =============================================================
// APPOINTMENT LOOKUP & CUSTOMER VERIFICATION ENGINE
// =============================================================

function normalizeConfirmationId(id) {
  if (!id) return '';
  const clean = String(id).replace(/[^A-Za-z0-9]/g, '').toUpperCase();
  const m = clean.match(/^NFA(\d{4})([A-Z0-9]+)$/);
  if (m) {
    return `NFA-${m[1]}-${m[2]}`;
  }
  return String(id).trim().toUpperCase();
}

function extractConfirmationId(text) {
  const t = (text || '').trim();
  const m = t.match(/\b(?:#?\s*)?(NFA[-_\s]?(?:20\d{2})[-_\s]?[A-Z0-9]{4,6})\b/i) ||
            t.match(/\b(NFA[-_\s]?[A-Z0-9]{4,12})\b/i);
  if (m) {
    return normalizeConfirmationId(m[1]);
  }
  return null;
}

function extractVerificationDetail(text) {
  const t = (text || '').trim();
  const emailMatch = t.match(/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/);
  if (emailMatch) {
    return { type: 'email', value: emailMatch[0].toLowerCase() };
  }
  const phoneMatch = t.match(/(?:\+?1[-.\s]?)?\(?([0-9]{3})\)?[-.\s]?([0-9]{3})[-.\s]?([0-9]{4})/) || t.match(/\b\d{7,15}\b/);
  if (phoneMatch) {
    return { type: 'phone', value: phoneMatch[0].replace(/\D/g, '') };
  }
  return null;
}

function findAppointmentInDb(id) {
  if (!id) return null;
  const targetId = normalizeConfirmationId(id);
  const currentDb = getDb();
  const bookings = currentDb.bookings || [];
  for (const b of bookings) {
    const bNum = normalizeConfirmationId(b.booking_number || b.booking_reference || String(b.id || ''));
    if (bNum === targetId || String(b.id) === targetId) {
      return b;
    }
  }
  return null;
}

function formatAlaskaDate(dateStr) {
  if (!dateStr) return '';
  try {
    const d = new Date(dateStr + 'T12:00:00Z');
    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric', timeZone: 'America/Anchorage' };
    return new Intl.DateTimeFormat('en-US', options).format(d);
  } catch (e) {
    return dateStr;
  }
}

function maskPhone(phone) {
  const digits = (phone || '').replace(/\D/g, '');
  if (digits.length >= 4) {
    return '***-***-' + digits.slice(-4);
  }
  return '***-****';
}

function maskEmail(email) {
  if (!email || !email.includes('@')) return '***@***.***';
  const [user, domain] = email.split('@');
  return (user[0] || '*') + '***@' + domain;
}

function formatAppointmentSummary(b, isVerified = false) {
  const confId = normalizeConfirmationId(b.booking_number || b.booking_reference || String(b.id || ''));
  const vehicle = [b.vehicle_year, b.vehicle_make, b.vehicle_model].filter(Boolean).join(' ') || b.vehicle_info || 'Vehicle on file';
  const formattedDate = formatAlaskaDate(b.preferred_date);

  const base = {
    confirmation_id: confId,
    status: (b.status ? b.status.charAt(0).toUpperCase() + b.status.slice(1).toLowerCase() : 'Pending'),
    location: 'North Fork Auto, 25460 Talkeetna Spur Rd, Talkeetna, AK 99676',
    timezone: 'America/Anchorage (Alaska Time)'
  };

  if (!isVerified) {
    return {
      ...base,
      verified: false,
      message: 'Appointment located. Customer verification required to view schedule and service details.'
    };
  }

  return {
    ...base,
    verified: true,
    customer_name: b.customer_name || 'Valued Customer',
    masked_phone: maskPhone(b.customer_phone),
    masked_email: maskEmail(b.customer_email),
    service_name: b.service_name || 'Diagnostic Inspection',
    date: formattedDate,
    raw_date: b.preferred_date,
    time: b.preferred_time || '10:00 AM',
    vehicle: vehicle,
    notes: b.notes || ''
  };
}

const verifyLockoutMap = new Map();
const MAX_VERIFY_ATTEMPTS = 5;
const LOCKOUT_MS = 15 * 60 * 1000;

function checkVerificationLockout(key) {
  const now = Date.now();
  const entry = verifyLockoutMap.get(key);
  if (!entry) return { locked: false };
  if (entry.lockedUntil && entry.lockedUntil > now) {
    return { locked: true, waitSec: Math.ceil((entry.lockedUntil - now) / 1000) };
  }
  if (entry.lockedUntil && entry.lockedUntil <= now) {
    verifyLockoutMap.delete(key);
    return { locked: false };
  }
  return { locked: false };
}

function recordFailedVerification(key) {
  const now = Date.now();
  let entry = verifyLockoutMap.get(key);
  if (!entry) {
    entry = { count: 0, lockedUntil: 0 };
    verifyLockoutMap.set(key, entry);
  }
  entry.count++;
  if (entry.count >= MAX_VERIFY_ATTEMPTS) {
    entry.lockedUntil = now + LOCKOUT_MS;
    return { locked: true, waitSec: Math.ceil(LOCKOUT_MS / 1000) };
  }
  return { locked: false, attemptsLeft: MAX_VERIFY_ATTEMPTS - entry.count };
}

function verifyAppointmentCustomer(appointment, providedDetail, lockoutKey) {
  const lockout = checkVerificationLockout(lockoutKey);
  if (lockout.locked) {
    return {
      verified: false,
      locked_out: true,
      message: `Too many verification attempts. For your security, please wait ${lockout.waitSec} seconds or call North Fork Auto at (907) 733-3030.`
    };
  }

  const provided = (providedDetail || '').trim();
  const recEmail = (appointment.customer_email || '').trim().toLowerCase();
  const recPhone = (appointment.customer_phone || '').replace(/\D/g, '');

  let isMatch = false;

  // Email check
  if (provided.includes('@')) {
    if (recEmail && provided.toLowerCase() === recEmail) {
      isMatch = true;
    }
  }

  // Phone check
  const pDigits = provided.replace(/\D/g, '');
  if (!isMatch && pDigits.length >= 7) {
    if (recPhone) {
      if (pDigits === recPhone) {
        isMatch = true;
      } else if (pDigits.length >= 10 && recPhone.length >= 10 && pDigits.slice(-10) === recPhone.slice(-10)) {
        isMatch = true;
      } else if (pDigits.length >= 7 && recPhone.length >= 7 && pDigits.slice(-7) === recPhone.slice(-7)) {
        isMatch = true;
      }
    }
  }

  if (isMatch) {
    verifyLockoutMap.delete(lockoutKey);
    return { verified: true, locked_out: false };
  }

  const fail = recordFailedVerification(lockoutKey);
  return {
    verified: false,
    locked_out: fail.locked,
    attempts_left: fail.attemptsLeft,
    message: fail.locked
      ? `Too many verification attempts. For security, please wait ${fail.waitSec} seconds or call us at (907) 733-3030.`
      : 'The phone number or email address provided does not match our records for this confirmation number.'
  };
}

// Initialize / Ensure database structure
function initDatabase() {
  const dir = path.dirname(DB_FILE);
  if (!fs.existsSync(dir)) fs.mkdirSync(dir, { recursive: true });

  let db = {
    users: [
      { id: 1, name: 'Admin Staff', email: 'admin@northforkauto.com', role: 'admin', is_active: true },
      { id: 2, name: 'Service Staff', email: 'staff@northforkauto.com', role: 'staff', is_active: true }
    ],
    bookings: [
      {
        id: 1790355090738,
        booking_number: 'NFA-2026-4362',
        booking_reference: 'NFA-2026-4362',
        customer_name: 'Sarah Connor',
        customer_email: 'sarah@example.com',
        customer_phone: '(907) 555-0199',
        service_name: 'Diagnostic Inspection',
        preferred_date: '2026-10-05',
        preferred_time: '09:00 AM',
        vehicle_info: '2021 Subaru Outback (AWD)',
        vehicle_year: '2021',
        vehicle_make: 'Subaru',
        vehicle_model: 'Outback',
        notes: 'Check engine light blinking occasionally on cold start.',
        internal_notes: 'Assigned to Dave. OBD-II scanner scheduled.',
        status: 'confirmed',
        created_at: '2026-09-20T10:30:00.000Z'
      },
      {
        id: 1790354690428,
        booking_number: 'NFA-2026-4425',
        booking_reference: 'NFA-2026-4425',
        customer_name: 'Michael Vance',
        customer_email: 'mvance@example.com',
        customer_phone: '(907) 555-0245',
        service_name: 'Brake Pad & Rotor Service',
        preferred_date: '2026-10-06',
        preferred_time: '01:30 PM',
        vehicle_info: '2019 Toyota Tacoma',
        vehicle_year: '2019',
        vehicle_make: 'Toyota',
        vehicle_model: 'Tacoma',
        notes: 'Front brakes squealing when slowing down.',
        internal_notes: 'Ceramic pads and rotors in stock.',
        status: 'pending',
        created_at: '2026-09-21T14:15:00.000Z'
      },
      {
        id: 1790354132922,
        booking_number: 'NFA-2026-9471',
        booking_reference: 'NFA-2026-9471',
        customer_name: 'Alice Johnson',
        customer_email: 'alice@example.com',
        customer_phone: '(907) 555-3344',
        service_name: 'Synthetic Oil Change',
        preferred_date: '2026-10-15',
        preferred_time: '09:00 AM',
        vehicle_info: '2023 Toyota RAV4 Prime',
        vehicle_year: '2023',
        vehicle_make: 'Toyota',
        vehicle_model: 'RAV4 Prime',
        notes: 'First time customer in Talkeetna.',
        internal_notes: 'Standard 5W-30 synthetic service.',
        status: 'completed',
        created_at: '2026-09-22T08:00:00.000Z'
      }
    ],
    services: [],
    contact_messages: [
      {
        id: 1790353516606,
        name: 'James Peterson',
        email: 'jpeterson@example.com',
        phone: '(907) 555-7821',
        subject: 'Winter Tire Changeover',
        message: 'Do you have studded Cooper Discoverer tires in stock for a 2018 Ford F-150?',
        created_at: '2026-09-24T14:20:00.000Z'
      }
    ],
    settings: {
      shop_name: 'North Fork Auto',
      shop_phone: '(907) 733-3030',
      shop_secondary_phone: '+1 (907) 232-3859',
      shop_email: 'info@northforkauto.com',
      shop_address: 'Talkeetna Spur Road, Talkeetna, AK 99676',
      hours_weekday: '8:00 AM – 5:00 PM',
      hours_saturday: 'By Appointment',
      hours_sunday: 'Closed',
      emergency_service: 'Available upon request for stranded vehicles'
    }
  };

    const CANONICAL_SERVICES = [
    {
      id: 1,
      name: 'Diagnostic Inspection',
      category: 'Diagnostics',
      description: 'State-of-the-art computerized diagnostic scanning for check engine lights and electronic faults.',
      price: '$120.00',
      duration_minutes: 60,
      is_active: true,
      is_featured: true,
      display_order: 1
    },
    {
      id: 2,
      name: 'Brake Repair & Replacement',
      category: 'Brakes',
      description: 'Complete brake pad, rotor, caliper, and hydraulic system inspections and repairs.',
      price: '$189.99',
      duration_minutes: 90,
      is_active: true,
      is_featured: true,
      display_order: 2
    },
    {
      id: 3,
      name: 'Oil & Filter Service',
      category: 'Maintenance',
      description: 'Full synthetic, blend, or conventional oil changes with comprehensive multi-point safety check.',
      price: '$89.99',
      duration_minutes: 45,
      is_active: true,
      is_featured: true,
      display_order: 3
    },
    {
      id: 4,
      name: 'Engine Repair & Overhaul',
      category: 'Engine',
      description: 'Precision engine diagnostics, timing belt replacements, head gaskets, and full engine repairs.',
      price: '$650.00',
      duration_minutes: 240,
      is_active: true,
      is_featured: true,
      display_order: 4
    },
    {
      id: 5,
      name: 'Transmission Service',
      category: 'Drivetrain',
      description: 'Fluid exchange, filter replacements, clutch service, and transmission diagnostics.',
      price: '$189.99',
      duration_minutes: 90,
      is_active: true,
      is_featured: true,
      display_order: 5
    },
    {
      id: 6,
      name: 'Steering & Suspension',
      category: 'Suspension',
      description: 'Shocks, struts, control arms, ball joints, and tie rod ends for a smooth, stable ride.',
      price: '$220.00',
      duration_minutes: 120,
      is_active: true,
      is_featured: true,
      display_order: 6
    },
    {
      id: 7,
      name: 'Electrical & Battery',
      category: 'Electrical',
      description: 'Alternators, starters, battery testing, wiring repairs, and complex parasitic draw testing.',
      price: '$135.00',
      duration_minutes: 60,
      is_active: true,
      is_featured: true,
      display_order: 7
    },
    {
      id: 8,
      name: 'Cooling & AC Repair',
      category: 'Climate',
      description: 'Radiator flushes, water pump replacement, thermostat diagnostics, and AC recharge.',
      price: '$160.00',
      duration_minutes: 90,
      is_active: true,
      is_featured: true,
      display_order: 8
    }
  ];
  db.services = CANONICAL_SERVICES;

  if (fs.existsSync(DB_FILE)) {
    try {
      const existing = JSON.parse(fs.readFileSync(DB_FILE, 'utf8').replace(/^\uFEFF/, ''));
      if (existing.bookings && existing.bookings.length > 0) {
        db.bookings = existing.bookings.map(b => {
          if (b.service_name && b.service_name.includes('bs[')) b.service_name = 'Diagnostic Inspection';
          return b;
        });
      }
      if (existing.contact_messages && existing.contact_messages.length > 0) {
        db.contact_messages = existing.contact_messages;
      }
      if (existing.services && existing.services.length > 0) {
        db.services = existing.services;
      }
      if (existing.settings) {
        db.settings = { ...db.settings, ...existing.settings };
      }
    } catch (err) {
      console.error('Error reading existing database:', err);
    }
  }

  fs.writeFileSync(DB_FILE, JSON.stringify(db, null, 2));
  return db;
}

let db = initDatabase();

function getDb() {
  try {
    return JSON.parse(fs.readFileSync(DB_FILE, 'utf8').replace(/^\uFEFF/, ''));
  } catch (e) {
    return db;
  }
}

function saveDb(data) {
  db = data;
  fs.writeFileSync(DB_FILE, JSON.stringify(data, null, 2));
}

// Active sessions & Rate Limiter
const activeSessions = new Set(['dev_admin_session_token_2026']);
const rateLimitMap = new Map();

function checkRateLimit(ip) {
  const now = Date.now();
  const entry = rateLimitMap.get(ip);
  if (!entry) return true;
  if (now > entry.expires) {
    rateLimitMap.delete(ip);
    return true;
  }
  return entry.count < 5;
}

function recordFailedLogin(ip) {
  const now = Date.now();
  const entry = rateLimitMap.get(ip);
  if (!entry || now > entry.expires) {
    rateLimitMap.set(ip, { count: 1, expires: now + 60000 });
  } else {
    entry.count += 1;
  }
}

function clearRateLimit(ip) {
  rateLimitMap.delete(ip);
}

// HTML escape helper
function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}

// Global Public Layout - North Fork Auto Icy-Blue Brand System
function renderLayout(title, description, content, activeNav = '', isScrolled = false) {
  return `<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} | North Fork Auto</title>
    <meta name="description" content="${description}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/images/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/images/apple-touch-icon.png">
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        background: '#EBF7FD',
                        foreground: '#071F35',
                        navy: {
                            DEFAULT: '#071F35',
                            surface: '#0B2B47',
                            card: '#0E3557',
                            deep: '#041321'
                        },
                        icy: {
                            DEFAULT: '#BDEBFA',
                            light: '#E6F8FE',
                            glow: 'rgba(189, 235, 250, 0.45)'
                        },
                        sky: {
                            DEFAULT: '#D8F1FA',
                            soft: '#EEF9FD'
                        },
                        ocean: {
                            DEFAULT: '#087DB5',
                            hover: '#0A92D4',
                            dark: '#065D88'
                        }
                    },
                    fontFamily: {
                        heading: ['Sora', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        body: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
                        mono: ['JetBrains Mono', 'ui-monospace', 'monospace']
                    }
                }
            }
        }
    </script>
    <style>
        :root {
            --font-heading: 'Sora', sans-serif;
            --font-body: 'Inter', sans-serif;
            --font-mono: 'JetBrains Mono', monospace;
            --color-icy: #BDEBFA;
            --color-sky: #D8F1FA;
            --color-ocean: #087DB5;
            --color-navy: #071F35;
        }
        body {
            font-family: var(--font-body);
            background: linear-gradient(180deg, #F0F9FE 0%, #D8F1FA 350px, #EBF8FD 100%);
            color: #071F35;
            overflow-x: hidden;
            min-height: 100vh;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            color: #071F35;
        }
        .frosted-card {
            background: rgba(255, 255, 255, 0.90);
            backdrop-filter: blur(12px);
            border: 1px solid #BDEBFA;
            border-radius: 1rem;
            box-shadow: 0 4px 20px rgba(7, 31, 53, 0.05);
            transition: all 0.25s ease;
        }
        .frosted-card:hover {
            border-color: #087DB5;
            box-shadow: 0 10px 30px rgba(8, 125, 181, 0.12);
            transform: translateY(-2px);
        }
        .btn-navy-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: #071F35;
            color: #FFFFFF;
            font-weight: 700;
            border-radius: 9999px;
            padding: 0.625rem 1.5rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            box-shadow: 0 2px 8px rgba(7, 31, 53, 0.15);
        }
        .btn-navy-pill:hover {
            background-color: #0B2B47;
            transform: translateY(-1px);
            box-shadow: 0 4px 14px rgba(7, 31, 53, 0.25);
        }
        .btn-white-pill {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            background-color: rgba(255, 255, 255, 0.95);
            color: #071F35;
            border: 1.5px solid #071F35;
            font-weight: 700;
            border-radius: 9999px;
            padding: 0.625rem 1.5rem;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .btn-white-pill:hover {
            background-color: #FFFFFF;
            border-color: #087DB5;
            color: #087DB5;
            transform: translateY(-1px);
        }
        .section-pad {
            width: 100%;
            max-width: 100%;
            padding-left: 1.25rem;
            padding-right: 1.25rem;
            margin-left: auto;
            margin-right: auto;
        }
        @media (min-width: 640px) {
            .section-pad {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        @media (min-width: 1024px) {
            .section-pad {
                padding-left: 3.5rem;
                padding-right: 3.5rem;
            }
        }
        @media (min-width: 1536px) {
            .section-pad {
                padding-left: 5rem;
                padding-right: 5rem;
            }
        }
        @media (min-width: 640px) {
            .section-pad {
                padding-left: 2rem;
                padding-right: 2rem;
            }
        }
        /* Header Initial State (Over Hero Banner) */
        header#main-header.navbar-top {
            background-color: transparent !important;
            background-image: none !important;
            border-bottom: 1px solid rgba(189, 235, 250, 0.45) !important;
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
            box-shadow: none !important;
        }

        /* Header Scrolled State (User's Ice Texture Background + Crisp Dark Navy Text) */
        header#main-header.navbar-scrolled {
            background-color: rgba(235, 247, 253, 0.92) !important;
            background-image: linear-gradient(rgba(240, 249, 254, 0.82), rgba(216, 241, 250, 0.88)), url('/images/hero-frost-texture.jpg') !important;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            backdrop-filter: blur(12px) !important;
            -webkit-backdrop-filter: blur(12px) !important;
            border-bottom: 1px solid rgba(189, 235, 250, 0.9) !important;
            box-shadow: 0 4px 20px rgba(7, 31, 53, 0.10) !important;
        }

        @media (min-width: 1024px) {
            .section-pad {
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">
    <!-- Navbar (Transparent over Hero Banner at top; Scrolled state reveals exact ice texture background with crisp dark text) -->
    <header id="main-header" class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 ${isScrolled ? 'navbar-scrolled' : 'navbar-top'}">
        <div class="section-pad">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-2.5 sm:gap-3 group">
                    <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive" class="h-8 sm:h-10 w-auto object-contain">
                    <div class="flex flex-col">
                        <span class="font-heading font-extrabold text-[#071F35] text-base sm:text-lg leading-tight tracking-tight">North Fork Automotive</span>
                        <span class="text-[9px] sm:text-[10px] font-bold text-[#087DB5] tracking-widest uppercase">— Talkeetna, AK —</span>
                    </div>
                </a>

                <!-- Desktop Navigation (Crisp Dark Text) -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="/" class="text-sm font-bold tracking-wide transition-colors ${activeNav === 'home' ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]'}">Home</a>
                    <div class="relative group">
                        <a href="/services" class="text-sm font-bold tracking-wide transition-colors flex items-center gap-1 py-2 ${activeNav === 'services' ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5]' : 'text-[#071F35] hover:text-[#087DB5]'}">
                            Services
                            <svg class="w-3.5 h-3.5 transition-transform duration-200 group-hover:rotate-180 text-[#071F35]" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                        </a>
                        <div class="absolute left-0 top-full pt-1.5 w-60 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 z-50">
                            <div class="bg-white/95 backdrop-blur-md border border-[#BDEBFA] rounded-2xl shadow-xl p-3 space-y-1">
                                <a href="/book?service=Engine%20Diagnostics" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Diagnostics</a>
                                <a href="/book?service=Brake%20System%20Service" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Brake Repair</a>
                                <a href="/book?service=Oil%20%26%20Filter%20Change" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Oil &amp; Filter Service</a>
                                <a href="/book?service=Preventive%20Maintenance" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">General Auto Repair</a>
                                <a href="/book?service=Engine%20Diagnostics" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Engine Service</a>
                                <a href="/book?service=Tire%20%26%20Wheel%20Service" class="block px-3 py-2 rounded-xl text-xs font-semibold text-[#071F35] hover:bg-[#EBF7FD] hover:text-[#087DB5] transition">Tire Services</a>
                                <div class="border-t border-[#BDEBFA] my-1 pt-1"></div>
                                <a href="/services" class="block px-3 py-1.5 rounded-xl text-[11px] font-bold text-[#087DB5] hover:bg-[#EBF7FD] transition">View All Services →</a>
                            </div>
                        </div>
                    </div>
                    <a href="/about" class="text-sm font-bold tracking-wide transition-colors ${activeNav === 'about' ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]'}">About</a>
                    <a href="/reviews" class="text-sm font-bold tracking-wide transition-colors ${activeNav === 'reviews' ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]'}">Reviews</a>
                    <a href="/contact" class="text-sm font-bold tracking-wide transition-colors ${activeNav === 'contact' ? 'text-[#087DB5] font-extrabold border-b-2 border-[#087DB5] pb-1' : 'text-[#071F35] hover:text-[#087DB5]'}">Contact</a>
                </nav>

                <!-- Phone Pill Button -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="tel:9077333030" class="btn-navy-pill text-xs shadow-md">
                        <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        (907) 733-3030
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center gap-3">
                    <a href="tel:9077333030" class="btn-navy-pill px-3 py-1.5 text-xs shadow-sm">
                        Call
                    </a>
                    <button id="mobile-toggle" aria-label="Toggle Navigation Menu" class="p-2 text-[#071F35] hover:text-[#087DB5]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-b border-[#BDEBFA] bg-white/98 backdrop-blur-md px-6 py-6 space-y-4 shadow-xl" style="background-image: linear-gradient(rgba(240, 249, 254, 0.94), rgba(216, 241, 250, 0.96)), url('/images/hero-frost-texture.jpg'); background-size: cover; background-position: center;">
            <a href="/" class="block text-sm font-bold ${activeNav === 'home' ? 'text-[#087DB5]' : 'text-[#071F35]'} py-1">Home</a>
            <a href="/services" class="block text-sm font-bold ${activeNav === 'services' ? 'text-[#087DB5]' : 'text-[#071F35]'} py-1">Services</a>
            <a href="/about" class="block text-sm font-bold ${activeNav === 'about' ? 'text-[#087DB5]' : 'text-[#071F35]'} py-1">About</a>
            <a href="/reviews" class="block text-sm font-bold ${activeNav === 'reviews' ? 'text-[#087DB5]' : 'text-[#071F35]'} py-1">Reviews</a>
            <a href="/contact" class="block text-sm font-bold ${activeNav === 'contact' ? 'text-[#087DB5]' : 'text-[#071F35]'} py-1">Contact</a>
            <div class="pt-4 border-t border-[#BDEBFA] flex flex-col gap-3">
                <a href="tel:9077333030" class="btn-navy-pill text-center py-2.5 text-xs font-bold">Call (907) 733-3030</a>
                <a href="/book" class="btn-white-pill text-center py-2.5 text-xs font-bold">Book Appointment</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow">
        ${content}
    </main>

        <!-- Footer (Matches Reference Footer with Alaska landscape background) -->
    <!-- Alaska Pine Forest Horizon Transition Banner (Above Footer Content) -->
    <div class="w-full overflow-hidden leading-none -mb-[1px] relative z-10 pointer-events-none select-none">
        <img src="/images/footer-pine-trees.jpg" alt="Alaskan pine trees horizon" class="w-full h-24 sm:h-32 md:h-40 lg:h-48 object-cover object-bottom">
        <div class="absolute inset-x-0 top-0 h-10 sm:h-14 bg-gradient-to-b from-[#EBF7FD] via-[#EBF7FD]/40 to-transparent pointer-events-none"></div>
    </div>
    <footer class="relative bg-[#071F35] text-white pt-8 sm:pt-10 pb-12 mt-auto overflow-hidden">
        <img src="/images/alaska-landscape-mountains.jpg" alt="" class="absolute inset-0 w-full h-full object-cover object-bottom opacity-15 pointer-events-none">
        <div class="absolute inset-0 bg-gradient-to-t from-[#071F35] via-[#071F35]/95 to-[#071F35]/85 pointer-events-none"></div>
        <div class="relative section-pad">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-10 mb-12">
                <!-- Col 1: Brand & Description -->
                <div class="space-y-4">
                    <a href="/" class="flex items-center gap-2.5 group">
                        <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive" class="h-8 w-auto brightness-0 invert object-contain">
                        <div class="flex flex-col">
                            <span class="font-heading font-extrabold text-white text-base leading-tight tracking-tight">North Fork Automotive</span>
                            <span class="text-[9px] font-bold text-[#BDEBFA] tracking-widest uppercase">— Talkeetna, AK —</span>
                        </div>
                    </a>
                    <p class="text-xs text-[#D8F1FA]/80 leading-relaxed">
                        Trusted auto repair and maintenance in Talkeetna, Alaska. Honest service, quality workmanship, and dependable care for your vehicle.
                    </p>
                    <div class="font-heading font-bold text-xs text-[#BDEBFA]">
                        Talkeetna, Alaska · Est. locally owned.
                    </div>
                </div>

                <!-- Col 2: Quick Links -->
                <div class="space-y-3">
                    <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-[#BDEBFA]">Quick Links</h4>
                    <ul class="space-y-2 text-xs text-[#D8F1FA]/80 font-medium">
                        <li><a href="/" class="hover:text-white transition">Home</a></li>
                        <li><a href="/services" class="hover:text-white transition">Services</a></li>
                        <li><a href="/about" class="hover:text-white transition">About</a></li>
                        <li><a href="/contact" class="hover:text-white transition">Visit Us (Contact)</a></li>
                        <li><a href="/book" class="hover:text-white transition">Book Appointment</a></li>
                    </ul>
                </div>

                <!-- Col 3: Business Hours -->
                <div class="space-y-3">
                    <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-[#BDEBFA]">Hours</h4>
                    <ul class="space-y-2 text-xs text-[#D8F1FA]/80">
                        <li class="flex justify-between gap-4"><span class="font-medium">Monday – Friday:</span><span class="text-white font-semibold">8:00 AM – 5:00 PM</span></li>
                        <li class="flex justify-between gap-4"><span class="font-medium">Saturday:</span><span class="text-white font-semibold">By Appointment</span></li>
                        <li class="flex justify-between gap-4"><span class="font-medium">Sunday:</span><span class="text-white/60">Closed</span></li>
                    </ul>
                </div>

                <!-- Col 4: Contact & Phone -->
                <div class="space-y-3">
                    <h4 class="font-heading font-bold text-xs uppercase tracking-wider text-[#BDEBFA]">Contact</h4>
                    <p class="text-xs text-[#D8F1FA]/80 leading-relaxed">
                        Talkeetna Spur Road, Talkeetna, AK 99676
                    </p>
                    <p class="text-xs text-[#D8F1FA]/80">
                        <a href="mailto:info@northforkauto.com" class="hover:text-white transition">info@northforkauto.com</a>
                    </p>
                    <div class="pt-2">
                        <a href="tel:9077333030" class="btn-navy-pill text-xs px-4 py-2 border border-[#BDEBFA]/30 bg-[#0B2B47] hover:bg-[#0E3557]">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                            <span>(907) 733-3030</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Bottom Subfooter Bar (Matches Reference Design) -->
            <div class="pt-8 border-t border-[#BDEBFA]/15 flex flex-col md:flex-row items-center justify-between gap-4 text-xs text-[#D8F1FA]/70">
                <div class="flex items-center gap-2">
                    <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive" class="h-6 w-auto brightness-0 invert object-contain">
                    <span class="font-heading font-bold text-white text-xs">North Fork Automotive <span class="text-[#BDEBFA] font-normal text-[10px] ml-1">— Talkeetna, AK —</span></span>
                </div>
                <span class="text-center">© 2025 North Fork Automotive. All rights reserved.</span>
                <div class="flex items-center gap-3">
                    <div class="flex items-center gap-2">
                        <a href="https://facebook.com" target="_blank" rel="noreferrer" aria-label="Facebook" class="w-7 h-7 rounded-full bg-[#0B2B47] border border-[#BDEBFA]/30 flex items-center justify-center hover:border-[#BDEBFA] transition text-white text-[11px] font-bold">f</a>
                        <a href="https://google.com" target="_blank" rel="noreferrer" aria-label="Google" class="w-7 h-7 rounded-full bg-[#0B2B47] border border-[#BDEBFA]/30 flex items-center justify-center hover:border-[#BDEBFA] transition text-white text-[11px] font-bold">G</a>
                        <a href="https://instagram.com" target="_blank" rel="noreferrer" aria-label="Instagram" class="w-7 h-7 rounded-full bg-[#0B2B47] border border-[#BDEBFA]/30 flex items-center justify-center hover:border-[#BDEBFA] transition text-white text-[11px] font-bold">ig</a>
                    </div>
                    <a href="tel:9077333030" class="btn-navy-pill text-[11px] px-3.5 py-1.5 border border-[#BDEBFA]/30 bg-[#0B2B47] hover:bg-[#0E3557] flex items-center gap-1.5 ml-2">
                        <svg class="w-3 h-3 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                        <span>(907) 733-3030</span>
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <script>
        const mobileToggle = document.getElementById('mobile-toggle');
        const mobileMenu = document.getElementById('mobile-menu');
        if (mobileToggle && mobileMenu) {
            mobileToggle.addEventListener('click', () => {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Header dynamic background on scroll: Transparent over hero banner at top, ice frost texture when scrolled
        const mainHeader = document.getElementById('main-header');
        if (mainHeader) {
            function updateNavbarOnScroll() {
                if (window.scrollY > 20) {
                    mainHeader.classList.remove('navbar-top');
                    mainHeader.classList.add('navbar-scrolled');
                } else {
                    mainHeader.classList.remove('navbar-scrolled');
                    mainHeader.classList.add('navbar-top');
                }
            }
            window.addEventListener('scroll', updateNavbarOnScroll, { passive: true });
            updateNavbarOnScroll();
        }
    </script>
    
    <!-- North Fork Auto AI Auto Service Assistant Chatbot -->
    ${getChatbotHtml()}
</body>
</html>`;
}

// -------------------------------------------------------------
// PUBLIC PAGE HANDLERS
// -------------------------------------------------------------

function handleHome(isScrolled = false) {
  const currentDb = getDb();
  const services = currentDb.services.filter(s => s.is_active);

  const heroHtml = `
<!-- SECTION 1: FULL-BLEED HOMEPAGE HERO (FROST TEXTURE OVERLAY BEHIND LEFT-SIDE TEXT ONLY) -->
<section class="relative w-full overflow-hidden min-h-[500px] sm:min-h-[540px] lg:min-h-[580px] flex items-center">
    <!-- Layer 1: Full-Width Background: Dark-blue Ford F-150 in Alaskan Mountains (Sharp & Clear on Right, extends behind navbar) -->
    <div class="absolute inset-0 z-0">
        <img src="/images/hero-truck-mountains.jpg" alt="Dark-blue Ford F-150 in front of Alaskan snow-covered mountain range" class="w-full h-full object-cover object-[80%_center] lg:object-right">
    </div>
    
    <!-- Layer 1.5 & Layer 2: Organic Frost Texture & Watercolor Patch behind Left-Side Text (Naturally Feathered Edges, Zero Sharp Borders) -->
    <div class="absolute inset-y-0 left-0 w-full lg:w-[62%] pointer-events-none z-1" 
         style="-webkit-mask-image: radial-gradient(ellipse 72% 66% at 28% 46%, black 20%, rgba(0,0,0,0.85) 45%, rgba(0,0,0,0.3) 68%, transparent 88%);
                mask-image: radial-gradient(ellipse 72% 66% at 28% 46%, black 20%, rgba(0,0,0,0.85) 45%, rgba(0,0,0,0.3) 68%, transparent 88%);">
        
        <!-- Organic Watercolor Ice Backing (Provides gentle icy watercolor foundation and ensures text readability) -->
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 30% 46%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 42%, rgba(189, 235, 250, 0.45) 72%, transparent 95%);"></div>
        
        <!-- Exact Uploaded Frost Texture Image (Blended softly at 40% opacity) -->
        <img src="/images/hero-frost-texture.jpg" 
             alt="" 
             aria-hidden="true" 
             class="w-full h-full object-cover object-left opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
    </div>
    
    <!-- Top & Bottom Soft Atmospheric Blends -->
    <div class="absolute top-0 inset-x-0 h-14 bg-gradient-to-b from-white/40 to-transparent z-1 pointer-events-none"></div>
    <div class="absolute bottom-0 inset-x-0 h-14 bg-gradient-to-t from-[#D8F1FA]/80 to-transparent z-1 pointer-events-none"></div>

    <!-- Layer 3: Foreground Content (Padded to clear fixed navbar seamlessly, Strictly Left-Aligned) -->
    <div class="relative z-10 section-pad w-full pt-24 pb-10 sm:pt-28 sm:pb-12 lg:pt-28 lg:pb-14">
        <div class="max-w-xl lg:max-w-xl text-left">
            <!-- Status Badge: Currently Open -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/85 border border-[#BDEBFA] text-[#087DB5] text-xs font-bold uppercase tracking-wider mb-2.5 shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Currently Open · Mon – Fri 8:00 AM – 5:00 PM</span>
            </div>

            <!-- Eyebrow: Talkeetna, Alaska · Auto Repair Experts -->
            <div class="text-xs sm:text-sm font-bold uppercase tracking-widest text-[#087DB5] mb-1">
                Talkeetna, Alaska · Auto Repair Experts
            </div>

            <h1 class="text-[#071F35] font-heading font-extrabold tracking-tight leading-[0.96] text-3xl sm:text-4xl lg:text-[3.2rem]">
                <span class="block text-[#071F35]">North Fork</span>
                <span class="block mt-1 text-[#071F35]">Automotive</span>
            </h1>
            
            <!-- Car with Mechanic Hood Graphic (Clean Silhouette matching Original Reference) -->
            <div class="my-2.5 max-w-[280px]">
                <img src="/images/hero-car-mechanic-silhouette.png" alt="North Fork Automotive Mechanic & Car" class="h-11 sm:h-13 w-auto object-contain">
                <div class="flex items-center gap-2.5 mt-1">
                    <span class="h-0.5 flex-1 bg-[#071F35]"></span>
                    <span class="font-heading font-bold text-xs uppercase tracking-widest text-[#071F35]">Talkeetna, AK</span>
                    <span class="h-0.5 flex-1 bg-[#071F35]"></span>
                </div>
            </div>
            
            <!-- Exact Live Headline -->
            <h2 class="text-xl sm:text-2xl font-heading font-extrabold text-[#071F35] mt-1 mb-2 leading-snug">
                Trusted Auto Repair.<br><span class="text-[#087DB5]">Quality Service.</span> Every Time.
            </h2>
            
            <!-- Exact Live Description -->
            <p class="text-xs sm:text-sm text-[#1E3A5F] font-medium leading-relaxed mb-6 max-w-md">
                From routine maintenance to complex diagnostics, North Fork Auto provides professional, honest automotive care for drivers across Talkeetna and the Mat-Su Valley.
            </p>
            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 sm:gap-4">
                <a href="/book" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-[#071F35] hover:bg-[#0B2B47] text-white px-5 sm:px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm shadow-md transition-all duration-200 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-white shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span>Book an Appointment</span>
                </a>
                <a href="tel:9077333030" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 bg-white/95 hover:bg-white text-[#071F35] border border-[#BDEBFA] hover:border-[#087DB5] px-5 sm:px-6 py-3.5 rounded-xl font-bold text-xs sm:text-sm shadow-sm transition-all duration-200 hover:-translate-y-0.5">
                    <svg class="w-4 h-4 text-[#071F35] shrink-0 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    <span>Call Now · (907) 733-3030</span>
                </a>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 2: TRUST BAR (4 HORIZONTAL INDICATORS FROM LIVE SITE) -->
<section class="section-pad my-4">
    <div class="bg-[#D8F1FA]/85 backdrop-blur-md rounded-2xl border border-[#BDEBFA] py-5 px-6 shadow-sm">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:divide-x md:divide-[#BDEBFA]">
            <!-- 1. ASE Certified -->
            <div class="flex items-center gap-3.5 md:justify-center">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">ASE Certified</h3>
                    <p class="text-xs text-[#334E68]">Skilled Master Techs</p>
                </div>
            </div>
            
            <!-- 2. OEM Diagnostic -->
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">OEM Diagnostic</h3>
                    <p class="text-xs text-[#334E68]">Dealer-Level Equipment</p>
                </div>
            </div>
            
            <!-- 3. Warranty Backed -->
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 4a2 2 0 114 0v1a1 1 0 001 1h3a1 1 0 011 1v3a1 1 0 01-1 1h-1a2 2 0 100 4h1a1 1 0 011 1v3a1 1 0 01-1 1h-3a1 1 0 01-1-1v-1a2 2 0 10-4 0v1a1 1 0 01-1 1H7a1 1 0 01-1-1v-3a1 1 0 00-1-1H4a2 2 0 110-4h1a1 1 0 001-1V7a1 1 0 011-1h3a1 1 0 001-1V4z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">Warranty Backed</h3>
                    <p class="text-xs text-[#334E68]">Parts &amp; Labor Protected</p>
                </div>
            </div>
            
            <!-- 4. Local Alaskan -->
            <div class="flex items-center gap-3.5 md:justify-center md:pl-4">
                <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
                </div>
                <div>
                    <h3 class="font-heading font-700 text-sm text-[#071F35]">Local Alaskan</h3>
                    <p class="text-xs text-[#334E68]">Community Focused</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 3: OUR CAPABILITIES (ALL 8 CANONICAL SERVICES WITH TITLES, DESCRIPTIONS & PRICES) -->
<section class="relative section-pad py-16 overflow-hidden">
    <!-- Section 3 Background: Ice Texture with Soft Top & Bottom Blends -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden">
        <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-20 mix-blend-multiply">
        <div class="absolute inset-0 bg-gradient-to-b from-[#EBF7FD] via-[#EBF7FD]/60 to-[#EBF7FD]"></div>
    </div>
    <div class="text-center mb-12 relative z-10">
        <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">— OUR CAPABILITIES —</span>
        <h2 class="font-heading font-extrabold text-3xl sm:text-4xl text-[#071F35] tracking-tight mt-1 mb-2">
            Expert Auto Care For Alaskan Roads
        </h2>
        <p class="text-sm sm:text-base text-[#334E68] max-w-xl mx-auto mb-3">
            From routine maintenance to complex diagnostics, we\'ve got you covered.
        </p>
        <a href="/services" class="inline-flex items-center gap-1.5 text-xs font-bold text-[#087DB5] hover:text-[#071F35] transition">
            <span>View All Services (8)</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    
    <!-- Top Row: 4 Cards Left with Frost Texture Overlay + Flipped Photographic Mechanic Visual Right -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-center mb-8">
        <!-- 4 Frosted Service Cards (Left) with Frost Texture Overlay (Same Styling & Reduced Opacity as Hero Banner) -->
        <div class="lg:col-span-7 relative">
            <!-- Left Side Ice Texture Overlay: Seamless Organic Feathering (Zero Sharp Box Corners) -->
            <div class="absolute -inset-6 sm:-inset-10 pointer-events-none z-0 overflow-hidden" 
                 style="-webkit-mask-image: radial-gradient(ellipse 82% 76% at 50% 50%, black 20%, rgba(0,0,0,0.8) 48%, rgba(0,0,0,0.2) 72%, transparent 95%);
                        mask-image: radial-gradient(ellipse 82% 76% at 50% 50%, black 20%, rgba(0,0,0,0.8) 48%, rgba(0,0,0,0.2) 72%, transparent 95%);">
                <!-- Organic Watercolor Ice Backing -->
                <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 45%, rgba(189, 235, 250, 0.40) 72%, transparent 96%);"></div>
                <!-- Exact Uploaded Frost Texture Image (Blended softly at 40% opacity with mix-blend-multiply) -->
                <img src="/images/hero-frost-texture.jpg" 
                     alt="" 
                     aria-hidden="true" 
                     class="w-full h-full object-cover opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 relative z-10">
            <!-- 1. Diagnostic Inspection -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Diagnostics</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$120.00</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Diagnostic Inspection</h3>
                <p class="text-xs text-[#334E68] mb-4">State-of-the-art computerized diagnostic scanning for check engine lights and electronic faults.</p>
                <a href="/book?service=Diagnostic%20Inspection" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            
            <!-- 2. Brake Repair & Replacement -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Brakes</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$189.99</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Brake Repair &amp; Replacement</h3>
                <p class="text-xs text-[#334E68] mb-4">Complete brake pad, rotor, caliper, and hydraulic system inspections and repairs.</p>
                <a href="/book?service=Brake%20Repair%20%26%20Replacement" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            
            <!-- 3. Oil & Filter Service -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Maintenance</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$89.99</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Oil &amp; Filter Service</h3>
                <p class="text-xs text-[#334E68] mb-4">Full synthetic, blend, or conventional oil changes with comprehensive multi-point safety check.</p>
                <a href="/book?service=Oil%20%26%20Filter%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            
            <!-- 4. Engine Repair & Overhaul -->
            <div class="frosted-card p-6 flex flex-col items-center text-center">
                <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="flex items-center justify-between w-full mb-1">
                    <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Engine</span>
                    <span class="font-heading font-bold text-xs text-[#071F35]">$650.00</span>
                </div>
                <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Engine Repair &amp; Overhaul</h3>
                <p class="text-xs text-[#334E68] mb-4">Precision engine diagnostics, timing belt replacements, head gaskets, and full engine repairs.</p>
                <a href="/book?service=Engine%20Repair%20%26%20Overhaul" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                    Book Now →
                </a>
            </div>
            </div>
        </div>
        
        <!-- Photographic Mechanic Visual (Right) - Mosaic Ice-Crystal Splash Frame (Non-standard organic corners) -->
        <div class="lg:col-span-5 relative z-10 flex items-center justify-center p-2 sm:p-4">
            <!-- Reverse Mosaic Crystalline Ice Aura Behind the Image (Faceted & Reversed) -->
            <div class="absolute -inset-5 sm:-inset-8 pointer-events-none -z-1 shadow-xl" 
                 style="clip-path: polygon(
                     0% 6%, 16% 0%, 38% 3%, 64% 0%, 86% 2%, 100% 0%, 
                     97% 20%, 100% 46%, 95% 68%, 100% 88%, 93% 100%, 
                     74% 96%, 46% 100%, 22% 95%, 0% 100%, 
                     5% 72%, 0% 48%, 4% 24%, 1% 10%
                 ); -webkit-clip-path: polygon(
                     0% 6%, 16% 0%, 38% 3%, 64% 0%, 86% 2%, 100% 0%, 
                     97% 20%, 100% 46%, 95% 68%, 100% 88%, 93% 100%, 
                     74% 96%, 46% 100%, 22% 95%, 0% 100%, 
                     5% 72%, 0% 48%, 4% 24%, 1% 10%
                 ); -webkit-mask-image: radial-gradient(ellipse 92% 90% at 50% 50%, black 65%, rgba(0,0,0,0.8) 85%, transparent 100%);
                    mask-image: radial-gradient(ellipse 92% 90% at 50% 50%, black 65%, rgba(0,0,0,0.8) 85%, transparent 100%);">
                <!-- Reverse Gradient Backing -->
                <div class="absolute inset-0 bg-gradient-to-bl from-[#BDEBFA] via-[#D8F1FA] to-[#087DB5]/25"></div>
                <!-- Reversed Frost Texture Image -->
                <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-60 mix-blend-multiply scale-x-[-1] rotate-180">
            </div>

            <!-- Main Image with Organic Crystalline Mosaic Facet Contour (Zero Standard Box Corners) -->
            <div class="relative w-full h-[420px] sm:h-[450px] shadow-2xl transition-all duration-300 hover:scale-[1.01] overflow-hidden" 
                 style="clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 ); -webkit-clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 );">
                
                <!-- The Uploaded Diagnostic Mechanic Photograph -->
                <img src="/images/service-mechanic-diagnostic.jpg" alt="Automotive diagnostic technician with computer tablet" class="w-full h-full object-cover object-[70%_center]">
                
                <!-- Frost Mosaic Crystallization Over Corners & Edges -->
                <div class="absolute inset-0 pointer-events-none" 
                     style="background: radial-gradient(ellipse 88% 85% at 50% 50%, transparent 58%, rgba(216, 241, 250, 0.45) 78%, rgba(189, 235, 250, 0.85) 100%);">
                    <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-40 mix-blend-overlay">
                </div>

                <!-- Soft Bottom Contrast Shadow -->
                <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#071F35]/40 via-transparent to-transparent pointer-events-none"></div>
            </div>

            <!-- Exterior Crystalline Accent Border -->
            <div class="absolute inset-1 sm:inset-3 pointer-events-none border-2 border-[#BDEBFA]/50 shadow-inner"
                 style="clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 ); -webkit-clip-path: polygon(
                     4% 3%, 18% 1%, 42% 2%, 68% 0%, 88% 3%, 97% 6%, 
                     100% 18%, 98% 38%, 100% 62%, 97% 82%, 99% 94%, 
                     86% 98%, 62% 97%, 38% 99%, 16% 97%, 3% 94%, 
                     0% 78%, 2% 52%, 0% 32%, 2% 14%
                 );">
            </div>
        </div>
    </div>

    <!-- Bottom Row: Remaining 4 Canonical Services with Frost Texture Overlay -->
    <div class="relative mt-8 z-10">
        <!-- Seamless Organic Frost Texture Overlay Behind Bottom Cards (No Sharp Box Corners) -->
        <div class="absolute -inset-6 sm:-inset-10 pointer-events-none z-0 overflow-hidden" 
             style="-webkit-mask-image: radial-gradient(ellipse 90% 75% at 50% 50%, black 25%, rgba(0,0,0,0.8) 50%, rgba(0,0,0,0.2) 75%, transparent 96%);
                    mask-image: radial-gradient(ellipse 90% 75% at 50% 50%, black 25%, rgba(0,0,0,0.8) 50%, rgba(0,0,0,0.2) 75%, transparent 96%);">
            <!-- Organic Watercolor Ice Backing -->
            <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(240, 249, 254, 0.98) 0%, rgba(216, 241, 250, 0.92) 45%, rgba(189, 235, 250, 0.40) 72%, transparent 96%);"></div>
            <!-- Frost Texture Image (Blended softly at 40% opacity) -->
            <img src="/images/hero-frost-texture.jpg" 
                 alt="" 
                 aria-hidden="true" 
                 class="w-full h-full object-cover opacity-40 mix-blend-multiply sm:opacity-40 lg:opacity-42">
        </div>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 relative z-10">
        <!-- 5. Transmission Service -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Drivetrain</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$189.99</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Transmission Service</h3>
            <p class="text-xs text-[#334E68] mb-4">Fluid exchange, filter replacements, clutch service, and transmission diagnostics.</p>
            <a href="/book?service=Transmission%20Service" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>

        <!-- 6. Steering & Suspension -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M2 12h20"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Suspension</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$220.00</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Steering &amp; Suspension</h3>
            <p class="text-xs text-[#334E68] mb-4">Shocks, struts, control arms, ball joints, and tie rod ends for a smooth, stable ride.</p>
            <a href="/book?service=Steering%20%26%20Suspension" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>

        <!-- 7. Electrical & Battery -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Electrical</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$135.00</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Electrical &amp; Battery</h3>
            <p class="text-xs text-[#334E68] mb-4">Alternators, starters, battery testing, wiring repairs, and complex parasitic draw testing.</p>
            <a href="/book?service=Electrical%20%26%20Battery" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>

        <!-- 8. Cooling & AC Repair -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-12 h-12 rounded-xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m0-18l3 3m-3-3l-3 3m0 12l3 3m-3-3l-3 3M3 12h18m-18 0l3-3m-3 3l3 3m12-3l3-3m-3 3l3 3"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Climate</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$160.00</span>
            </div>
            <h3 class="font-heading font-bold text-base text-[#071F35] mb-1">Cooling &amp; AC Repair</h3>
            <p class="text-xs text-[#334E68] mb-4">Radiator flushes, water pump replacement, thermostat diagnostics, and AC recharge.</p>
            <a href="/book?service=Cooling%20%26%20AC%20Repair" class="mt-auto text-xs font-bold text-[#087DB5] hover:text-[#071F35] flex items-center gap-1 transition">
                Book Now →
            </a>
        </div>
        </div>
    </div>
</section>

<!-- SECTION 4: ABOUT NORTH FORK AUTOMOTIVE (WORKSHOP BANNER, STORY, 4 POINTS WITH 100% WIDTH FROST OVERLAY) -->
<section id="about-section" class="relative section-pad py-16 overflow-hidden">
    <!-- 100% Full-Width Ice Background Image Across Entire Banner with Reduced Opacity at Corners -->
    <div class="absolute inset-0 pointer-events-none z-0 overflow-hidden" 
         style="-webkit-mask-image: radial-gradient(ellipse 96% 92% at 50% 50%, black 45%, rgba(0,0,0,0.85) 68%, rgba(0,0,0,0.25) 88%, transparent 100%);
                mask-image: radial-gradient(ellipse 96% 92% at 50% 50%, black 45%, rgba(0,0,0,0.85) 68%, rgba(0,0,0,0.25) 88%, transparent 100%);">
        <!-- Watercolor Ice Backing across full 100% width -->
        <div class="absolute inset-0" style="background: radial-gradient(ellipse at 50% 50%, rgba(240, 249, 254, 0.80) 0%, rgba(216, 241, 250, 0.60) 48%, rgba(189, 235, 250, 0.20) 82%, transparent 100%);"></div>
        <!-- 100% Width Frost Texture Image -->
        <img src="/images/hero-frost-texture.jpg" 
             alt="" 
             aria-hidden="true" 
             class="w-full h-full object-cover opacity-25 mix-blend-multiply sm:opacity-25 lg:opacity-25">
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-center relative z-10">
        <!-- Workshop Exterior Visual (Left - 5 cols) - Chiseled Glacial Ice Silhouette (Zero Frame Look) -->
        <div class="lg:col-span-5 relative z-10 flex items-center justify-center p-2 sm:p-4">
            <!-- Organic Frost Mist Aura Directly Behind Visual (Soft Glacial Vapour, Corners with Reduced Opacity) -->
            <div class="absolute -inset-4 sm:-inset-6 pointer-events-none -z-1 overflow-hidden" 
                 style="-webkit-mask-image: radial-gradient(ellipse 88% 82% at 50% 50%, black 30%, rgba(0,0,0,0.7) 60%, rgba(0,0,0,0.15) 85%, transparent 100%);
                        mask-image: radial-gradient(ellipse 88% 82% at 50% 50%, black 30%, rgba(0,0,0,0.7) 60%, rgba(0,0,0,0.15) 85%, transparent 100%);">
                <!-- Glacial Cyan Ice Vapour -->
                <div class="absolute inset-0 bg-gradient-to-br from-[#BDEBFA]/50 via-[#D8F1FA]/30 to-[#087DB5]/10"></div>
                <!-- Natural Frost Texture -->
                <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-25 mix-blend-multiply scale-105">
            </div>

            <!-- Dynamic Chiseled Ice Slab (Asymmetrical Glacial Faceted Angles, No Picture Frame) -->
            <div class="relative w-full h-[390px] sm:h-[430px] transition-all duration-500 hover:scale-[1.015] group" 
                 style="clip-path: polygon(
                     16% 0%, 93% 0%, 100% 12%, 
                     100% 80%, 85% 100%, 
                     7% 100%, 0% 88%, 
                     0% 20%
                 ); -webkit-clip-path: polygon(
                     16% 0%, 93% 0%, 100% 12%, 
                     100% 80%, 85% 100%, 
                     7% 100%, 0% 88%, 
                     0% 20%
                 ); filter: drop-shadow(0 20px 25px rgba(7, 31, 53, 0.18));">
                
                <!-- Workshop Exterior Image -->
                <img src="/images/workshop-exterior.jpg" alt="North Fork Automotive Workshop Exterior in Talkeetna, Alaska" class="w-full h-full object-cover object-center transition-transform duration-700 group-hover:scale-105">
                
                <!-- Alaskan Frost Splinters Across the Cut Facets -->
                <div class="absolute inset-0 pointer-events-none" 
                     style="background: linear-gradient(135deg, rgba(216, 241, 250, 0.6) 0%, transparent 22%, transparent 78%, rgba(189, 235, 250, 0.5) 100%),
                                 radial-gradient(ellipse at 50% 50%, transparent 65%, rgba(189, 235, 250, 0.35) 100%);">
                    <img src="/images/hero-frost-texture.jpg" alt="" class="w-full h-full object-cover opacity-30 mix-blend-overlay">
                </div>

                <!-- Upper Chiseled Ridge Ice Shimmer -->
                <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-transparent via-[#BDEBFA]/90 to-transparent pointer-events-none"></div>

                <!-- Soft Atmospheric Depth Gradient at Bottom -->
                <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#071F35]/65 via-[#071F35]/20 to-transparent pointer-events-none"></div>

                <!-- Location Pill Positioned Naturally Along Lower Shelf -->
                <div class="absolute bottom-6 left-7 inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-[#071F35]/85 backdrop-blur-md border border-[#BDEBFA]/50 text-white text-xs font-bold shadow-xl">
                    <svg class="w-3.5 h-3.5 text-[#BDEBFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Talkeetna Spur Road Workshop</span>
                </div>
            </div>
        </div>
        
        <!-- Story Text & Points Container (Right - 7 cols, seamlessly resting on the 100% width ice background) -->
        <div class="lg:col-span-7 relative z-10">
            <!-- Content Grid: Story Text (Center) + 4 Points (Right, Taken out of the Card Box) -->
            <div class="grid grid-cols-1 md:grid-cols-12 gap-6 sm:gap-8 items-center relative z-10">
                <!-- Center: Story & Description (7 cols) -->
                <div class="md:col-span-7 space-y-4 text-left">
                    <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase">TALKEETNA'S TRUSTED AUTO CARE —</span>
                    <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#071F35] tracking-tight leading-snug">
                        Locally Owned. Honest Service. Real Results.
                    </h2>
                    <p class="text-xs sm:text-sm text-[#334E68] leading-relaxed">
                        North Fork Auto was built with a simple goal: provide our Talkeetna neighbors with honest, high-quality automotive repair you can count on. We treat every vehicle like it's our own.
                    </p>
                    <div class="pt-2">
                        <a href="/about" class="btn-navy-pill text-xs px-6 py-2.5 shadow-md">
                            Learn More →
                        </a>
                    </div>
                </div>
                
                <!-- Right: 4 Points (5 cols) - COMPLETELY FREED FROM CARD BOX -->
                <div class="md:col-span-5 flex flex-col gap-4">
                    <!-- Point 1 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">ASE-Certified Master Technicians</span>
                    </div>
                    
                    <!-- Point 2 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">Advanced Computer Diagnostics</span>
                    </div>
                    
                    <!-- Point 3 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">3-Year / 36,000-Mile Warranty</span>
                    </div>
                    
                    <!-- Point 4 -->
                    <div class="flex items-center gap-3.5 group">
                        <div class="w-10 h-10 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-105 transition-transform">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
                        </div>
                        <span class="font-heading font-bold text-xs sm:text-sm text-[#071F35] leading-snug">Locally Owned &amp; Community Minded</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION 5: HOMEPAGE CALL TO ACTION (FROM LIVE SITE) -->
<section class="section-pad py-12">
    <div class="frosted-card p-8 sm:p-12 text-center border-2 border-[#BDEBFA] bg-gradient-to-r from-white/95 via-[#EBF7FD] to-white/95">
        <span class="text-xs font-bold tracking-widest text-[#087DB5] uppercase mb-2 block">— GET BACK ON THE ROAD —</span>
        <h2 class="font-heading font-extrabold text-2xl sm:text-4xl text-[#071F35] tracking-tight mb-3">
            Ready For Reliable Automotive Service?
        </h2>
        <p class="text-sm text-[#334E68] max-w-xl mx-auto mb-8 leading-relaxed">
            Schedule your appointment online or call us directly. We provide transparent estimates and expert care.
        </p>
        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="/book" class="w-full sm:w-auto btn-navy-pill text-sm px-8 py-3.5 shadow-lg">
                Book Service Appointment
            </a>
            <a href="tel:9077333030" class="w-full sm:w-auto btn-white-pill text-sm px-8 py-3.5 shadow-sm">
                Call (907) 733-3030
            </a>
        </div>
    </div>
</section>
`;

  return renderLayout('North Fork Automotive | Reliable Auto Repair in Talkeetna, Alaska', 'Honest answers, quality workmanship, and a team that cares in Talkeetna, AK.', heroHtml, 'home', isScrolled);
}

function handleServices() {
  const content = `
<!-- MOUNTAIN HERO BANNER (MATCHES TOP-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden pt-32 pb-16 sm:pt-36 sm:pb-20">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35]/90 via-[#071F35]/75 to-[#071F35]/90"></div>
    <div class="relative section-pad text-center max-w-3xl mx-auto">
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
            Our Automotive Services
        </h1>
        <p class="text-sm sm:text-base text-[#BDEBFA] font-medium leading-relaxed max-w-2xl mx-auto">
            From routine fluid changes to complete engine diagnostics, our certified technicians keep your vehicle running smoothly in harsh Alaskan conditions.
        </p>
    </div>
</section>

<!-- 8 CANONICAL SERVICES GRID (FULL LIVE WEBSITE DATA) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- 1. Diagnostic Inspection -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Diagnostics</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$120.00 · ~60m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Diagnostic Inspection</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">State-of-the-art computerized diagnostic scanning for check engine lights and electronic faults.</p>
            <a href="/book?service=Diagnostic%20Inspection" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
        
        <!-- 2. Brake Repair & Replacement -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9" stroke-width="2"/><circle cx="12" cy="12" r="4" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v2m0 14v2m9-9h-2M5 12H3"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Brakes</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$189.99 · ~90m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Brake Repair &amp; Replacement</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Complete brake pad, rotor, caliper, and hydraulic system inspections and repairs.</p>
            <a href="/book?service=Brake%20Repair%20%26%20Replacement" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
        
        <!-- 3. Oil & Filter Service -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Maintenance</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$89.99 · ~45m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Oil &amp; Filter Service</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Full synthetic, blend, or conventional oil changes with comprehensive multi-point safety check.</p>
            <a href="/book?service=Oil%20%26%20Filter%20Service" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
        
        <!-- 4. Engine Repair & Overhaul -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Engine</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$650.00 · ~240m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Engine Repair &amp; Overhaul</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Precision engine diagnostics, timing belt replacements, head gaskets, and full engine repairs.</p>
            <a href="/book?service=Engine%20Repair%20%26%20Overhaul" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 5. Transmission Service -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Drivetrain</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$189.99 · ~90m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Transmission Service</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Fluid exchange, filter replacements, clutch service, and transmission diagnostics.</p>
            <a href="/book?service=Transmission%20Service" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 6. Steering & Suspension -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10" stroke-width="2"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2v20M2 12h20"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Suspension</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$220.00 · ~120m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Steering &amp; Suspension</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Shocks, struts, control arms, ball joints, and tie rod ends for a smooth, stable ride.</p>
            <a href="/book?service=Steering%20%26%20Suspension" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 7. Electrical & Battery -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Electrical</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$135.00 · ~60m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Electrical &amp; Battery</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Alternators, starters, battery testing, wiring repairs, and complex parasitic draw testing.</p>
            <a href="/book?service=Electrical%20%26%20Battery" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>

        <!-- 8. Cooling & AC Repair -->
        <div class="frosted-card p-6 flex flex-col items-center text-center">
            <div class="w-14 h-14 rounded-2xl bg-[#D8F1FA] text-[#087DB5] flex items-center justify-center mb-4 shadow-sm">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v18m0-18l3 3m-3-3l-3 3m0 12l3 3m-3-3l-3 3M3 12h18m-18 0l3-3m-3 3l3 3m12-3l3-3m-3 3l3 3"/></svg>
            </div>
            <div class="flex items-center justify-between w-full mb-1">
                <span class="text-[10px] font-bold uppercase tracking-wider text-[#087DB5]">Climate</span>
                <span class="font-heading font-bold text-xs text-[#071F35]">$160.00 · ~90m</span>
            </div>
            <h3 class="font-heading font-bold text-lg text-[#071F35] mb-2">Cooling &amp; AC Repair</h3>
            <p class="text-xs text-[#334E68] mb-6 leading-relaxed">Radiator flushes, water pump replacement, thermostat diagnostics, and AC recharge.</p>
            <a href="/book?service=Cooling%20%26%20AC%20Repair" class="mt-auto btn-navy-pill text-xs w-full py-2.5">
                Book Now →
            </a>
        </div>
    </div>
    
    <!-- CTA BUTTON BANNER (MATCHES SCREENSHOT) -->
    <div class="mt-12 text-center">
        <a href="/book" class="inline-flex items-center gap-3 bg-[#071F35] text-white hover:bg-[#0B2B47] px-8 py-4 rounded-full font-bold text-sm shadow-xl transition">
            <svg class="w-5 h-5 text-[#BDEBFA]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21h18M3 10l9-7 9 7v11H3V10z"/></svg>
            <span>Book Your Appointment Today →</span>
        </a>
    </div>
</section>
`;

  return renderLayout('Our Automotive Services', 'From routine fluid changes to complete engine diagnostics, our certified technicians keep your vehicle running smoothly in harsh Alaskan conditions.', content, 'services');
}

function handleAbout() {
  const content = `
<!-- MOUNTAIN HERO BANNER (MATCHES MIDDLE-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden pt-32 pb-14 sm:pt-36 sm:pb-16">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-30">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35] via-[#071F35]/85 to-[#071F35]/70"></div>
    <div class="relative section-pad max-w-6xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6">
        <div>
            <div class="text-xs font-bold uppercase tracking-widest text-[#BDEBFA] mb-2">About North Fork Auto</div>
            <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
                Locally Owned. Alaskan Tested.
            </h1>
            <p class="text-sm sm:text-base text-[#BDEBFA] font-medium leading-relaxed max-w-2xl">
                North Fork Auto is Talkeetna's premier independent auto care facility. We understand what it takes to keep vehicles running safely through Alaskan winters and rugged summer terrain.
            </p>
        </div>
        <div class="w-full md:w-64 rounded-xl overflow-hidden border border-[#BDEBFA]/30 shadow-lg shrink-0">
            <img src="/images/workshop-exterior.jpg" alt="North Fork Automotive Workshop" class="w-full h-32 sm:h-36 object-cover object-center">
        </div>
    </div>
</section>

<!-- TWO-COLUMN CONTENT SECTION (MATCHES SCREENSHOT) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-center">
        <!-- Left Column: Our Story & Values -->
        <div class="lg:col-span-7 space-y-5">
            <h2 class="font-heading font-extrabold text-2xl sm:text-3xl text-[#071F35]">
                Our Story &amp; Values
            </h2>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                Founded with a mission to deliver straightforward, trustworthy auto repairs to the Talkeetna community, North Fork Auto brings modern diagnostic technology and old-school honesty to every job.
            </p>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                We believe in total transparency. When you bring your vehicle in, we thoroughly inspect it, explain what needs immediate attention versus what can wait, and provide clear upfront estimates before any work begins.
            </p>
            <p class="text-sm sm:text-base text-[#334E68] leading-relaxed">
                Whether you're a year-round resident preparing for sub-zero weather or passing through on the Parks Highway, our certified technicians are dedicated to keeping you safe on the road.
            </p>
            <div class="pt-3">
                <a href="/book" class="btn-navy-pill text-xs px-6 py-2.5">
                    Book an Appointment →
                </a>
            </div>
        </div>
        
        <!-- Right Column: 4 Checkmarks Card -->
        <div class="lg:col-span-5">
            <div class="frosted-card p-8 space-y-5 border-2 border-[#BDEBFA]">
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">100% Locally Owned &amp; Operated</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">ASE Certified Technicians</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">3-Year / 36,000-Mile Warranty</span>
                </div>
                
                <div class="flex items-center gap-3.5">
                    <div class="w-7 h-7 rounded-full bg-[#087DB5] text-white flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <span class="font-heading font-bold text-sm text-[#071F35]">Transparent Upfront Pricing</span>
                </div>
            </div>
        </div>
    </div>
</section>
`;

  return renderLayout('About Us | North Fork Auto', 'Locally owned. Alaskan tested. Professional automotive care in Talkeetna, Alaska.', content, 'about');
}

function handleReviews() {
  const content = `
<!-- MOUNTAIN HERO BANNER -->
<section class="relative bg-[#071F35] text-white overflow-hidden pt-32 pb-16 sm:pt-36 sm:pb-20">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35]/90 via-[#071F35]/75 to-[#071F35]/90"></div>
    <div class="relative section-pad text-center max-w-3xl mx-auto">
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
            Customer Reviews
        </h1>
        <p class="text-sm sm:text-base text-[#BDEBFA] font-medium">
            What Talkeetna Drivers Say About North Fork Automotive
        </p>
    </div>
</section>

<!-- REVIEWS GRID -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div>
                <div class="text-amber-400 text-lg mb-2">★★★★★</div>
                <p class="text-xs text-[#334E68] leading-relaxed italic">
                    "Broke down on the Spur Road during sub-zero temps. North Fork Auto quickly diagnosed the alternator and got us back on the road the same day. Honest, fast, and fair pricing."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#BDEBFA] flex items-center justify-between text-xs">
                <span class="font-heading font-bold text-[#071F35]">Marcus T.</span>
                <span class="text-[#087DB5] font-semibold">Talkeetna Resident</span>
            </div>
        </div>
        
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div>
                <div class="text-amber-400 text-lg mb-2">★★★★★</div>
                <p class="text-xs text-[#334E68] leading-relaxed italic">
                    "The best brake and suspension shop in the Mat-Su area. They showed me exactly which parts were worn and gave me options. My F-250 handles the gravel roads like new again."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#BDEBFA] flex items-center justify-between text-xs">
                <span class="font-heading font-bold text-[#071F35]">David K.</span>
                <span class="text-[#087DB5] font-semibold">Mat-Su Valley</span>
            </div>
        </div>
        
        <div class="frosted-card p-6 flex flex-col justify-between">
            <div>
                <div class="text-amber-400 text-lg mb-2">★★★★★</div>
                <p class="text-xs text-[#334E68] leading-relaxed italic">
                    "Having a trustworthy mechanic in Talkeetna is a lifesaver. Routine maintenance, fluid checks, and pre-winter inspections are always on time and professional."
                </p>
            </div>
            <div class="mt-6 pt-4 border-t border-[#BDEBFA] flex items-center justify-between text-xs">
                <span class="font-heading font-bold text-[#071F35]">Sarah B.</span>
                <span class="text-[#087DB5] font-semibold">Trapper Creek</span>
            </div>
        </div>
    </div>
    
    <div class="mt-12 text-center">
        <a href="/book" class="btn-navy-pill text-sm px-8 py-3.5">
            Book Your Appointment Today →
        </a>
    </div>
</section>
`;

  return renderLayout('Customer Reviews', 'What Talkeetna drivers say about North Fork Automotive.', content, 'reviews');
}

function handleContact() {
  const content = `
<!-- MOUNTAIN HERO BANNER (MATCHES BOTTOM-RIGHT PANEL IN SCREENSHOT) -->
<section class="relative bg-[#071F35] text-white overflow-hidden pt-32 pb-16 sm:pt-36 sm:pb-20">
    <img src="/images/mountain-banner.jpg" alt="Alaskan mountain range" class="absolute inset-0 w-full h-full object-cover object-center opacity-40">
    <div class="absolute inset-0 bg-gradient-to-r from-[#071F35]/90 via-[#071F35]/75 to-[#071F35]/90"></div>
    <div class="relative section-pad text-center max-w-3xl mx-auto">
        <div class="text-xs font-bold uppercase tracking-widest text-[#BDEBFA] mb-2">Visit Us In Talkeetna</div>
        <h1 class="font-heading font-extrabold text-3xl sm:text-5xl tracking-tight text-white mb-2">
            Contact &amp; Location
        </h1>
        <p class="text-sm sm:text-base text-[#BDEBFA] font-medium leading-relaxed">
            Conveniently located off the Talkeetna Spur Road. Drop by during shop hours or send us a message below.
        </p>
    </div>
</section>

<!-- TWO-COLUMN CONTENT SECTION (MATCHES SCREENSHOT) -->
<section class="section-pad py-16">
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-10 items-start">
        <!-- Left Column: Shop Details -->
        <div class="lg:col-span-5 space-y-6">
            <div class="frosted-card p-8 space-y-6">
                <h3 class="font-heading font-bold text-xl text-[#071F35] border-b border-[#BDEBFA] pb-3">Shop Details</h3>
                
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 fill-current" viewBox="0 0 24 24"><path d="M6.62 10.79a15.053 15.053 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24c1.12.37 2.33.57 3.57.57.55 0 1 .45 1 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57.11.35.03.74-.25 1.02l-2.2 2.2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">Direct Phone</span>
                        <a href="tel:9077333030" class="font-heading font-bold text-base text-[#071F35] hover:text-[#087DB5] transition">(907) 733-3030</a>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">Shop Location</span>
                        <span class="font-heading font-bold text-base text-[#071F35]">Talkeetna Spur Road, Talkeetna, AK 99676</span>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <div class="w-10 h-10 rounded-full bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </div>
                    <div>
                        <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block">Email Inquiries</span>
                        <a href="mailto:info@northforkauto.com" class="font-heading font-bold text-base text-[#071F35] hover:text-[#087DB5] transition">info@northforkauto.com</a>
                    </div>
                </div>
            </div>
            
            <div class="frosted-card p-6">
                <span class="text-xs text-[#334E68] font-bold uppercase tracking-wider block mb-2">Shop Hours</span>
                <p class="text-sm font-semibold text-[#071F35]">Monday – Friday: 8:00 AM – 5:00 PM</p>
                <p class="text-xs text-[#334E68] mt-1">Saturday: By Appointment · Sunday: Closed</p>
            </div>
        </div>
        
        <!-- Right Column: Send Us a Message Form -->
        <div class="lg:col-span-7">
            <div class="frosted-card p-8 border-2 border-[#BDEBFA]">
                <h3 class="font-heading font-bold text-2xl text-[#071F35] mb-2">Send a Message</h3>
                <p class="text-xs text-[#334E68] mb-6">Drop us a line and our team will get back to you promptly.</p>
                
                <div id="contact-feedback" class="hidden mb-4 p-4 rounded-xl text-xs font-bold border border-emerald-400 bg-emerald-500/10 text-emerald-700">
                    Thank you! Your message has been sent successfully. Our team will get back to you shortly.
                </div>
                
                <form id="contact-form" action="/contact" method="POST" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Your Name *</label>
                        <input type="text" name="name" placeholder="Your Full Name" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Email Address *</label>
                        <input type="email" name="email" placeholder="you@example.com" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Phone Number</label>
                        <input type="tel" name="phone" placeholder="(907) 555-0199" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Subject</label>
                        <input type="text" name="subject" placeholder="e.g. Brake Inspection Inquiry" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5]">
                    </div>
                    
                    <div>
                        <label class="block text-xs font-bold uppercase text-[#071F35] mb-1">Message *</label>
                        <textarea name="message" rows="4" placeholder="How can we help you?" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-2.5 text-sm text-[#071F35] rounded-lg focus:outline-none focus:border-[#087DB5] resize-none"></textarea>
                    </div>
                    
                    <button type="submit" class="w-full btn-navy-pill py-3 text-sm font-bold justify-center rounded-lg">
                        Send Inquiry →
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>

<script>
    if (new URLSearchParams(window.location.search).get('success') === '1') {
        const fb = document.getElementById('contact-feedback');
        if (fb) fb.classList.remove('hidden');
    }
</script>
`;

  return renderLayout('Contact & Location', 'Conveniently located off the Talkeetna Spur Road. Drop by during shop hours or send us a message below.', content, 'contact');
}

// -------------------------------------------------------------
// FIXED & DYNAMIC BOOKING WIZARD HANDLER (ZERO BLADE LEAK)
// -------------------------------------------------------------

function handleBook() {
  const currentDb = getDb();
  const services = currentDb.services.filter(s => s.is_active);

  const serviceCardsHtml = services.map((s, idx) => `
    <div class="service-select-card cursor-pointer p-4.5 rounded-2xl transition-all duration-200 ${idx === 0 ? 'border-2 border-[#087DB5] bg-[#EBF7FD] shadow-md shadow-[#087DB5]/10' : 'border border-[#BDEBFA] bg-white hover:border-[#087DB5]/50'}" data-service-name="${escapeHtml(s.name)}">
        <div class="flex items-start justify-between gap-2">
            <h4 class="font-heading font-700 text-sm text-[#071F35]">${escapeHtml(s.name)}</h4>
            <span class="font-heading text-xs text-[#087DB5] shrink-0 font-bold">${escapeHtml(s.price || 'Custom Quote')}</span>
        </div>
        <p class="mt-1 text-xs text-[#071F35]/70 leading-relaxed">${escapeHtml(s.description)}</p>
    </div>
  `).join('');

  const defaultServiceName = services.length > 0 ? services[0].name : 'Diagnostic Inspection';

  const content = `
<div class="section-pad pt-32 pb-24 min-h-screen">
    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-12 text-center">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/80 border border-[#BDEBFA] text-[#087DB5] text-xs font-bold uppercase tracking-wider mb-4 shadow-sm">
            <span class="w-2 h-2 rounded-full bg-[#087DB5]"></span>
            <span>Online Service Booking</span>
        </div>
        <h1 class="font-heading font-800 text-4xl sm:text-5xl lg:text-6xl tracking-tight text-[#071F35]">
            Schedule Your Service
        </h1>
        <p class="mt-4 text-[#071F35]/80 text-base sm:text-lg max-w-2xl mx-auto">
            Book in minutes. Choose your service, pick an available time, and our Talkeetna technicians will prepare for your arrival. No account required.
        </p>
    </div>

    <!-- Booking Wizard Container -->
    <div class="max-w-4xl mx-auto frosted-card p-6 sm:p-10 border border-[#BDEBFA] shadow-xl bg-white/95 text-[#071F35] rounded-3xl">
        <!-- Step Progress Bar -->
        <div class="mb-10">
            <div class="grid grid-cols-4 gap-2">
                <div id="step-tab-1" class="h-1.5 bg-[#087DB5] rounded-full transition-all duration-300"></div>
                <div id="step-tab-2" class="h-1.5 bg-[#D8F1FA] rounded-full transition-all duration-300"></div>
                <div id="step-tab-3" class="h-1.5 bg-[#D8F1FA] rounded-full transition-all duration-300"></div>
                <div id="step-tab-4" class="h-1.5 bg-[#D8F1FA] rounded-full transition-all duration-300"></div>
            </div>
            <div class="mt-3 flex justify-between text-xs font-heading font-600 text-[#071F35]/70">
                <span id="step-label-1" class="text-[#087DB5] font-bold">1. Contact</span>
                <span id="step-label-2">2. Vehicle</span>
                <span id="step-label-3">3. Service</span>
                <span id="step-label-4">4. Date &amp; Time</span>
            </div>
        </div>

        <div id="booking-feedback" class="hidden mb-6 p-4 border rounded-xl text-sm"></div>

        <form id="booking-wizard-form" action="/book" method="POST">
            <!-- STEP 1: Contact Information -->
            <div id="step-panel-1" class="space-y-6">
                <div class="border-b border-[#BDEBFA] pb-4 mb-6">
                    <h3 class="font-heading font-800 text-xl text-[#071F35]">Customer Information</h3>
                    <p class="text-xs text-[#071F35]/70 mt-1">Please provide your contact details so we can confirm your appointment.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="cust-name" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Full Name <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="text" id="cust-name" name="name" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="e.g. Sarah Connor">
                    </div>
                    <div>
                        <label for="cust-email" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Email Address <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="email" id="cust-email" name="email" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="sarah@example.com">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="cust-phone" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Phone Number <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="tel" id="cust-phone" name="phone" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="(907) 555-0199">
                    </div>
                    <div>
                        <label for="cust-notes" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Special Requests / Vehicle Symptoms
                        </label>
                        <input type="text" id="cust-notes" name="notes" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="e.g. Grinding noise when braking...">
                    </div>
                </div>

                <div class="pt-6 flex justify-end">
                    <button type="button" id="btn-to-step-2" class="btn-navy-pill text-sm">
                        Continue to Vehicle
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Vehicle Details -->
            <div id="step-panel-2" class="hidden space-y-6">
                <div class="border-b border-[#BDEBFA] pb-4 mb-6">
                    <h3 class="font-heading font-800 text-xl text-[#071F35]">Vehicle Details</h3>
                    <p class="text-xs text-[#071F35]/70 mt-1">Tell us about the vehicle you're bringing into the shop.</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-6">
                    <div>
                        <label for="veh-year" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Year <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="number" id="veh-year" name="vehicle_year" min="1950" max="2027" required value="2020" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl transition">
                    </div>
                    <div>
                        <label for="veh-make" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Make <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="text" id="veh-make" name="vehicle_make" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="e.g. Ford, Subaru, Toyota">
                    </div>
                    <div>
                        <label for="veh-model" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Model <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="text" id="veh-model" name="vehicle_model" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="e.g. F-150, Outback, 4Runner">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="veh-mileage" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Approximate Mileage
                        </label>
                        <input type="text" id="veh-mileage" name="vehicle_mileage" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="e.g. 85,000">
                    </div>
                    <div>
                        <label for="veh-vin" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            VIN (Optional)
                        </label>
                        <input type="text" id="veh-vin" name="vehicle_vin" class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl placeholder-[#071F35]/40 transition" placeholder="17-character VIN">
                    </div>
                </div>

                <div class="pt-6 flex justify-between items-center">
                    <button type="button" id="btn-back-to-step-1" class="btn-white-pill text-sm">
                        Back
                    </button>
                    <button type="button" id="btn-to-step-3" class="btn-navy-pill text-sm">
                        Continue to Service
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Select Service -->
            <div id="step-panel-3" class="hidden space-y-6">
                <div class="border-b border-[#BDEBFA] pb-4 mb-6">
                    <h3 class="font-heading font-800 text-xl text-[#071F35]">Select Requested Service</h3>
                    <p class="text-xs text-[#071F35]/70 mt-1">Pick the primary service or inspection you need performed.</p>
                </div>

                <input type="hidden" id="selected-service-input" name="service" value="${escapeHtml(defaultServiceName)}">

                <div class="grid sm:grid-cols-2 gap-4 max-h-[460px] overflow-y-auto pr-1">
                    ${serviceCardsHtml}
                </div>

                <div class="pt-6 flex justify-between items-center">
                    <button type="button" id="btn-back-to-step-2" class="btn-white-pill text-sm">
                        Back
                    </button>
                    <button type="button" id="btn-to-step-4" class="btn-navy-pill text-sm">
                        Continue to Date &amp; Time
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Date & Time Picker & Submit -->
            <div id="step-panel-4" class="hidden space-y-6">
                <div class="border-b border-[#BDEBFA] pb-4 mb-6">
                    <h3 class="font-heading font-800 text-xl text-[#071F35]">Select Appointment Date &amp; Time</h3>
                    <p class="text-xs text-[#071F35]/70 mt-1">Select your preferred drop-off slot (Mon - Fri, 8:00 AM – 5:00 PM).</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="app-date" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Preferred Date <span class="text-[#087DB5]">*</span>
                        </label>
                        <input type="date" id="app-date" name="preferred_date" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl transition">
                    </div>
                    <div>
                        <label for="app-time" class="block font-heading font-700 text-xs uppercase tracking-wide text-[#071F35] mb-2">
                            Preferred Time Slot <span class="text-[#087DB5]">*</span>
                        </label>
                        <select id="app-time" name="preferred_time" required class="w-full bg-[#EBF7FD] border border-[#BDEBFA] px-4 py-3 text-sm text-[#071F35] focus:outline-none focus:border-[#087DB5] rounded-xl transition">
                            <option value="08:00 AM">08:00 AM (Early Drop-off)</option>
                            <option value="09:00 AM">09:00 AM</option>
                            <option value="10:00 AM" selected>10:00 AM</option>
                            <option value="11:00 AM">11:00 AM</option>
                            <option value="01:00 PM">01:00 PM</option>
                            <option value="02:00 PM">02:00 PM</option>
                            <option value="03:00 PM">03:00 PM</option>
                            <option value="04:00 PM">04:00 PM</option>
                        </select>
                    </div>
                </div>

                <!-- Booking Summary Review Card -->
                <div class="border border-[#BDEBFA] bg-[#EBF7FD]/60 p-5 text-sm space-y-2 rounded-2xl">
                    <div class="font-heading text-xs text-[#087DB5] mb-2 font-bold uppercase tracking-wider">Appointment Review Summary</div>
                    <div class="flex justify-between"><span class="text-[#071F35]/70">Customer:</span><span id="review-cust" class="font-heading font-700 text-[#071F35]">-</span></div>
                    <div class="flex justify-between"><span class="text-[#071F35]/70">Vehicle:</span><span id="review-veh" class="font-heading font-700 text-[#071F35]">-</span></div>
                    <div class="flex justify-between"><span class="text-[#071F35]/70">Service:</span><span id="review-srv" class="font-heading font-700 text-[#087DB5]">${escapeHtml(defaultServiceName)}</span></div>
                </div>

                <div class="pt-6 flex justify-between items-center">
                    <button type="button" id="btn-back-to-step-3" class="btn-white-pill text-sm">
                        Back
                    </button>
                    <button type="submit" id="btn-submit-booking" class="btn-navy-pill text-sm">
                        Confirm &amp; Book Appointment
                    </button>
                </div>
            </div>

            <!-- STEP 5: Success Confirmation Screen -->
            <div id="step-panel-5" class="hidden text-center py-8 space-y-6">
                <div class="w-16 h-16 rounded-full border-2 border-[#087DB5] bg-[#087DB5]/15 text-[#087DB5] flex items-center justify-center mx-auto shadow-lg shadow-[#087DB5]/20">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 class="font-heading font-800 text-3xl text-[#071F35]">Appointment Requested!</h3>
                <p class="text-[#071F35]/80 max-w-lg mx-auto text-sm leading-relaxed">
                    Thank you! We have received your booking request. Our service advisor will review your vehicle and slot details and confirm your drop-off.
                </p>
                <div class="border border-[#BDEBFA] bg-[#EBF7FD]/60 p-6 max-w-md mx-auto text-left space-y-2.5 text-sm rounded-2xl shadow-sm">
                    <div class="flex justify-between items-center"><span class="text-[#071F35]/70">Confirmation Ref:</span><span id="conf-ref" class="font-heading text-[#087DB5] font-bold text-base">NFA-2026-0001</span></div>
                    <div class="flex justify-between"><span class="text-[#071F35]/70">Service:</span><span id="conf-service" class="font-heading font-700 text-[#071F35]">-</span></div>
                    <div class="flex justify-between"><span class="text-[#071F35]/70">Date:</span><span id="conf-date" class="font-heading text-[#071F35]">-</span></div>
                    <div class="flex justify-between items-center pt-2 border-t border-[#BDEBFA]"><span class="text-[#071F35]/70">Status:</span><span class="px-2.5 py-0.5 text-[10px] uppercase font-bold border border-[#087DB5]/30 bg-[#087DB5]/10 text-[#087DB5] rounded-full">Pending Confirmation</span></div>
                </div>
                <div class="pt-4 flex justify-center gap-4">
                    <a href="/" class="btn-white-pill text-xs">
                        Return Home
                    </a>
                    <a href="tel:+19077333030" class="btn-navy-pill text-xs">
                        Call Shop
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        // Set tomorrow as default minimum date
        const dateInput = document.getElementById('app-date');
        const tomorrow = new Date();
        tomorrow.setDate(tomorrow.getDate() + 1);
        const yyyy = tomorrow.getFullYear();
        const mm = String(tomorrow.getMonth() + 1).padStart(2, '0');
        const dd = String(tomorrow.getDate()).padStart(2, '0');
        if (dateInput) {
            dateInput.min = \`\${yyyy}-\${mm}-\${dd}\`;
            dateInput.value = \`\${yyyy}-\${mm}-\${dd}\`;
        }

        // Check for URL parameter service
        const urlParams = new URLSearchParams(window.location.search);
        const preselectedService = urlParams.get('service');
        if (preselectedService) {
            const serviceInput = document.getElementById('selected-service-input');
            const reviewSrv = document.getElementById('review-srv');
            if (serviceInput) serviceInput.value = preselectedService;
            if (reviewSrv) reviewSrv.textContent = preselectedService;
            
            // Highlight matching card if present
            document.querySelectorAll('.service-select-card').forEach(c => {
                if (c.getAttribute('data-service-name').toLowerCase() === preselectedService.toLowerCase()) {
                    document.querySelectorAll('.service-select-card').forEach(allC => {
                        allC.className = 'service-select-card cursor-pointer border border-[#BDEBFA] bg-white hover:border-[#087DB5]/50 p-4.5 rounded-2xl transition-all duration-200';
                    });
                    c.className = 'service-select-card cursor-pointer border-2 border-[#087DB5] bg-[#EBF7FD] shadow-md shadow-[#087DB5]/10 p-4.5 rounded-2xl transition-all duration-200';
                }
            });
        }

        // Wizard navigation
        const panels = [1, 2, 3, 4, 5].map(i => document.getElementById('step-panel-' + i));
        const tabs = [1, 2, 3, 4].map(i => document.getElementById('step-tab-' + i));
        const labels = [1, 2, 3, 4].map(i => document.getElementById('step-label-' + i));

        function goToStep(step) {
            panels.forEach((p, idx) => {
                if (p) {
                    if (idx + 1 === step) p.classList.remove('hidden');
                    else p.classList.add('hidden');
                }
            });
            tabs.forEach((t, idx) => {
                if (t) {
                    if (idx + 1 <= step) {
                        t.className = 'h-1.5 bg-[#087DB5] rounded-full transition-all duration-300';
                    } else {
                        t.className = 'h-1.5 bg-[#D8F1FA] rounded-full transition-all duration-300';
                    }
                }
            });
            labels.forEach((l, idx) => {
                if (l) {
                    if (idx + 1 === step) {
                        l.className = 'text-[#087DB5] font-bold';
                    } else if (idx + 1 < step) {
                        l.className = 'text-[#071F35] font-bold';
                    } else {
                        l.className = 'text-[#071F35]/50';
                    }
                }
            });
            window.scrollTo({ top: 120, behavior: 'smooth' });
        }

        // Step 1 to 2
        document.getElementById('btn-to-step-2')?.addEventListener('click', () => {
            const name = document.getElementById('cust-name');
            const email = document.getElementById('cust-email');
            const phone = document.getElementById('cust-phone');
            if (!name.value.trim() || !email.value.trim() || !phone.value.trim()) {
                alert('Please fill out your Name, Email, and Phone Number.');
                return;
            }
            goToStep(2);
        });

        // Step 2 to 3
        document.getElementById('btn-to-step-3')?.addEventListener('click', () => {
            const make = document.getElementById('veh-make');
            const model = document.getElementById('veh-model');
            if (!make.value.trim() || !model.value.trim()) {
                alert('Please enter your vehicle Make and Model.');
                return;
            }
            goToStep(3);
        });

        // Step 3 to 4
        document.getElementById('btn-to-step-4')?.addEventListener('click', () => {
            const name = document.getElementById('cust-name').value;
            const year = document.getElementById('veh-year').value;
            const make = document.getElementById('veh-make').value;
            const model = document.getElementById('veh-model').value;
            const srv = document.getElementById('selected-service-input').value;

            document.getElementById('review-cust').textContent = name;
            document.getElementById('review-veh').textContent = [year, make, model].filter(Boolean).join(' ');
            document.getElementById('review-srv').textContent = srv;
            goToStep(4);
        });

        // Back buttons
        document.getElementById('btn-back-to-step-1')?.addEventListener('click', () => goToStep(1));
        document.getElementById('btn-back-to-step-2')?.addEventListener('click', () => goToStep(2));
        document.getElementById('btn-back-to-step-3')?.addEventListener('click', () => goToStep(3));

        // Service selection card clicks
        document.querySelectorAll('.service-select-card').forEach(card => {
            card.addEventListener('click', () => {
                document.querySelectorAll('.service-select-card').forEach(c => {
                    c.className = 'service-select-card cursor-pointer border border-[#BDEBFA] bg-white hover:border-[#087DB5]/50 p-4.5 rounded-2xl transition-all duration-200';
                });
                card.className = 'service-select-card cursor-pointer border-2 border-[#087DB5] bg-[#EBF7FD] shadow-md shadow-[#087DB5]/10 p-4.5 rounded-2xl transition-all duration-200';
                const sName = card.getAttribute('data-service-name');
                document.getElementById('selected-service-input').value = sName;
                document.getElementById('review-srv').textContent = sName;
            });
        });

        // Form Submission via AJAX/JSON
        const form = document.getElementById('booking-wizard-form');
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = document.getElementById('btn-submit-booking');
            const origText = btn.innerHTML;
            btn.innerHTML = 'Submitting Booking...';
            btn.disabled = true;

            const formData = new FormData(form);
            const dataObj = Object.fromEntries(formData.entries());

            try {
                const res = await fetch('/book', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(dataObj)
                });
                const resData = await res.json();
                if (res.ok && resData.success) {
                    document.getElementById('conf-ref').textContent = resData.booking.booking_number || resData.booking.booking_reference;
                    document.getElementById('conf-service').textContent = resData.booking.service_name;
                    document.getElementById('conf-date').textContent = resData.booking.preferred_date + ' at ' + resData.booking.preferred_time;
                    goToStep(5);
                } else {
                    alert(resData.message || 'There was an issue processing your booking.');
                    btn.innerHTML = origText;
                    btn.disabled = false;
                }
            } catch (err) {
                console.error(err);
                form.submit();
            }
        });

        if (urlParams.get('success') === '1') {
            const ref = urlParams.get('ref') || 'NFA-2026-CONFIRMED';
            document.getElementById('conf-ref').textContent = ref;
            document.getElementById('conf-service').textContent = 'Automotive Service';
            document.getElementById('conf-date').textContent = 'Scheduled';
            goToStep(5);
        }
    });
</script>
`;

  return renderLayout('Schedule Appointment', 'Book your service online at North Fork Auto.', content, 'book');
}

// -------------------------------------------------------------
// STAFF & ADMIN AUTHENTICATION & PORTAL HANDLERS
// -------------------------------------------------------------

function renderStaffLayout(title, content, activeNav = 'dashboard') {
  return `<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>${title} | Staff Portal - North Fork Auto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #071F35; color: #f8fafc; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Sora', sans-serif; font-weight: 800; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.6rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
            border-radius: 4px;
        }
        .status-pending { background: rgba(250, 204, 21, 0.15); color: #fde047; border: 1px solid rgba(250, 204, 21, 0.35); }
        .status-confirmed { background: rgba(8, 125, 181, 0.25); color: #BDEBFA; border: 1px solid rgba(189, 235, 250, 0.4); }
        .status-in_progress { background: rgba(168, 85, 247, 0.2); color: #e9d5ff; border: 1px solid rgba(168, 85, 247, 0.4); }
        .status-completed { background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4); }
        .status-cancelled { background: rgba(239, 68, 68, 0.15); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.35); }
    </style>
</head>
<body class="min-h-screen bg-[#071F35] text-white flex flex-col antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 border-r border-[#BDEBFA]/20 bg-[#0B2B47] p-6 flex flex-col justify-between shrink-0 shadow-xl">
            <div>
                <!-- Brand -->
                <div class="flex items-center gap-3 mb-10">
                    <img src="/images/logo.png" alt="North Fork Auto" class="h-10 w-auto object-contain rounded bg-[#041321]/80 border border-[#BDEBFA]/25 p-1">
                    <div>
                        <div class="font-heading font-extrabold text-sm tracking-wide text-white">NORTH FORK AUTO</div>
                        <div class="font-mono text-[10px] text-[#BDEBFA] uppercase tracking-wider font-semibold">Staff Portal</div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="space-y-1.5 font-mono text-xs">
                    <a href="/staff/dashboard" class="flex items-center gap-3 px-3.5 py-2.5 rounded transition-colors ${activeNav === 'dashboard' ? 'bg-[#087DB5] text-white font-bold border-l-4 border-[#BDEBFA]' : 'text-[#D8F1FA]/70 hover:text-white hover:bg-[#0E3557]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        Dashboard
                    </a>
                    <a href="/staff/bookings" class="flex items-center gap-3 px-3.5 py-2.5 rounded transition-colors ${activeNav === 'bookings' ? 'bg-[#087DB5] text-white font-bold border-l-4 border-[#BDEBFA]' : 'text-[#D8F1FA]/70 hover:text-white hover:bg-[#0E3557]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                        Bookings
                    </a>
                    <a href="/staff/services" class="flex items-center gap-3 px-3.5 py-2.5 rounded transition-colors ${activeNav === 'services' ? 'bg-[#087DB5] text-white font-bold border-l-4 border-[#BDEBFA]' : 'text-[#D8F1FA]/70 hover:text-white hover:bg-[#0E3557]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        Services
                    </a>
                    <a href="/staff/settings" class="flex items-center gap-3 px-3.5 py-2.5 rounded transition-colors ${activeNav === 'settings' ? 'bg-[#087DB5] text-white font-bold border-l-4 border-[#BDEBFA]' : 'text-[#D8F1FA]/70 hover:text-white hover:bg-[#0E3557]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        Settings &amp; Inbox
                    </a>
                </nav>
            </div>

            <div class="pt-6 border-t border-[#BDEBFA]/15 space-y-3">
                <a href="/" target="_blank" class="flex items-center gap-2 text-xs font-mono text-[#D8F1FA]/70 hover:text-[#BDEBFA] transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Live Public Site
                </a>
                <a href="/staff/logout" class="flex items-center gap-2 text-xs font-mono text-red-400 hover:text-red-300 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto p-8 lg:p-12 bg-[#071F35]">
            ${content}
        </main>
    </div>
</body>
</html>`;
}

function handleStaffLogin(res, errorMsg = '') {
  const html = `<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Staff Login | North Fork Auto</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background: #071F35; color: #f1f5f9; }
        h1, h2, h3 { font-family: 'Sora', sans-serif; font-weight: 800; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-[#071F35]">
    <div class="w-full max-w-md bg-[#0B2B47] border border-[#BDEBFA]/25 p-8 sm:p-10 rounded-xl shadow-2xl">
        <div class="flex items-center gap-3.5 mb-8">
            <img src="/images/logo.png" alt="North Fork Auto" class="h-11 w-auto object-contain rounded bg-[#041321] border border-[#BDEBFA]/25 p-1">
            <div>
                <div class="font-heading font-extrabold text-base text-white">NORTH FORK AUTO</div>
                <div class="font-mono text-[10px] text-[#BDEBFA] uppercase tracking-wider font-semibold">Authorized Staff Portal</div>
            </div>
        </div>

        ${errorMsg ? `
            <div class="mb-6 p-4 border border-red-500/40 bg-red-500/10 text-red-400 text-xs font-mono rounded">
                ${escapeHtml(errorMsg)}
            </div>
        ` : ''}

        <form action="/staff/login" method="POST" class="space-y-5">
            <div>
                <label class="block font-mono text-xs uppercase text-[#D8F1FA]/80 mb-1.5">Staff Email</label>
                <input type="email" name="email" required value="admin@northforkauto.com" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
            </div>
            <div>
                <label class="block font-mono text-xs uppercase text-[#D8F1FA]/80 mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-3 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
            </div>
            <div class="flex items-center justify-between text-xs font-mono text-[#D8F1FA]/70">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="accent-[#087DB5]"> Remember session
                </label>
                <a href="/" class="text-[#D8F1FA]/70 hover:text-[#BDEBFA] transition-colors">Public Site &rarr;</a>
            </div>
            <button type="submit" class="w-full bg-[#087DB5] text-white py-3.5 font-mono text-xs uppercase font-bold tracking-wider hover:bg-[#0A92D4] transition-colors rounded shadow-lg shadow-[#087DB5]/30">
                Sign In to Portal
            </button>
        </form>
    </div>
</body>
</html>`;
  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(html);
}

function handleStaffDashboard(res) {
  const currentDb = getDb();
  const bookings = currentDb.bookings || [];
  const services = currentDb.services || [];
  const messages = currentDb.contact_messages || [];

  const pendingCount = bookings.filter(b => b.status === 'pending').length;
  const confirmedCount = bookings.filter(b => b.status === 'confirmed').length;
  const inProgressCount = bookings.filter(b => b.status === 'in_progress').length;
  const completedCount = bookings.filter(b => b.status === 'completed').length;
  const cancelledCount = bookings.filter(b => b.status === 'cancelled').length;
  const activeServicesCount = services.filter(s => s.is_active).length;
  const inactiveServicesCount = services.filter(s => !s.is_active).length;

  const recentBookings = bookings.slice(0, 8);

  const content = `
<div>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#BDEBFA]/15 mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Operations Dashboard</h1>
            <p class="text-xs text-[#D8F1FA]/70 mt-1 font-mono">Real-time shop metrics, bookings &amp; workflow</p>
        </div>
        <div class="flex gap-3">
            <a href="/staff/bookings" class="px-4 py-2.5 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] transition-colors rounded shadow-md shadow-[#087DB5]/25">
                + Manage Bookings
            </a>
            <a href="/staff/services" class="px-4 py-2.5 border border-[#BDEBFA]/25 bg-[#0B2B47] text-white text-xs font-mono uppercase hover:border-[#BDEBFA] transition-colors rounded">
                Services Catalog
            </a>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-10">
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-5 rounded-lg shadow-md">
            <div class="text-[11px] font-mono text-[#D8F1FA]/70 uppercase tracking-wider">Total Bookings</div>
            <div class="text-3xl font-extrabold text-white mt-2">${bookings.length}</div>
            <div class="text-[10px] text-[#BDEBFA] font-mono mt-1">All-time volume</div>
        </div>
        <div class="bg-[#0B2B47] border border-amber-400/35 p-5 rounded-lg shadow-md">
            <div class="text-[11px] font-mono text-amber-300 uppercase tracking-wider">Pending Action</div>
            <div class="text-3xl font-extrabold text-amber-300 mt-2">${pendingCount}</div>
            <div class="text-[10px] text-amber-400/80 font-mono mt-1">Requires confirmation</div>
        </div>
        <div class="bg-[#0B2B47] border border-[#087DB5]/40 p-5 rounded-lg shadow-md">
            <div class="text-[11px] font-mono text-[#BDEBFA] uppercase tracking-wider">Confirmed &amp; Active</div>
            <div class="text-3xl font-extrabold text-[#BDEBFA] mt-2">${confirmedCount + inProgressCount}</div>
            <div class="text-[10px] text-[#D8F1FA]/70 font-mono mt-1">${inProgressCount} in progress</div>
        </div>
        <div class="bg-[#0B2B47] border border-emerald-400/35 p-5 rounded-lg shadow-md">
            <div class="text-[11px] font-mono text-emerald-300 uppercase tracking-wider">Completed Jobs</div>
            <div class="text-3xl font-extrabold text-emerald-300 mt-2">${completedCount}</div>
            <div class="text-[10px] text-emerald-400/80 font-mono mt-1">Ready for pickup</div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-6 rounded-lg flex items-center justify-between">
            <div>
                <div class="text-xs font-mono text-[#D8F1FA]/70 uppercase">Active Catalog Services</div>
                <div class="text-2xl font-extrabold text-white mt-1">${activeServicesCount}</div>
            </div>
            <div class="text-xs font-mono text-[#D8F1FA]/50">${inactiveServicesCount} hidden</div>
        </div>
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-6 rounded-lg flex items-center justify-between">
            <div>
                <div class="text-xs font-mono text-[#D8F1FA]/70 uppercase">Customer Inquiries</div>
                <div class="text-2xl font-extrabold text-white mt-1">${messages.length}</div>
            </div>
            <a href="/staff/settings" class="text-xs font-mono text-[#BDEBFA] hover:underline font-bold">View Inbox &rarr;</a>
        </div>
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-6 rounded-lg flex items-center justify-between">
            <div>
                <div class="text-xs font-mono text-[#D8F1FA]/70 uppercase">Cancelled Requests</div>
                <div class="text-2xl font-extrabold text-[#D8F1FA]/60 mt-1">${cancelledCount}</div>
            </div>
            <div class="text-xs font-mono text-[#D8F1FA]/50">Archived</div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="border border-[#BDEBFA]/20 bg-[#0B2B47] rounded-lg overflow-hidden shadow-lg">
        <div class="p-6 border-b border-[#BDEBFA]/15 flex items-center justify-between">
            <h2 class="text-lg font-bold text-white">Recent Customer Bookings</h2>
            <a href="/staff/bookings" class="text-xs font-mono text-[#BDEBFA] hover:underline font-bold">View Full Ledger &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#BDEBFA]/15 font-mono text-[#D8F1FA]/70 bg-[#041321]">
                    <tr>
                        <th class="p-4">Reference</th>
                        <th class="p-4">Customer</th>
                        <th class="p-4">Vehicle</th>
                        <th class="p-4">Service</th>
                        <th class="p-4">Date &amp; Slot</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Quick Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BDEBFA]/10">
                    ${recentBookings.length === 0 ? `
                        <tr><td colspan="7" class="p-8 text-center text-[#D8F1FA]/60 font-mono">No bookings found in database.</td></tr>
                    ` : recentBookings.map(b => `
                        <tr class="hover:bg-[#0E3557] transition-colors">
                            <td class="p-4 font-mono text-[#BDEBFA] font-bold">${escapeHtml(b.booking_number || b.booking_reference)}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">${escapeHtml(b.customer_name)}</div>
                                <div class="text-[11px] text-[#D8F1FA]/70 font-mono">${escapeHtml(b.customer_phone)}</div>
                            </td>
                            <td class="p-4 text-[#D8F1FA]/90">${escapeHtml(b.vehicle_info || (b.vehicle_year ? b.vehicle_year + ' ' + b.vehicle_make + ' ' + b.vehicle_model : 'Standard'))}</td>
                            <td class="p-4 text-white font-medium">${escapeHtml(b.service_name)}</td>
                            <td class="p-4 font-mono text-[#D8F1FA]/90">${escapeHtml(b.preferred_date)} <span class="text-[#D8F1FA]/50">${escapeHtml(b.preferred_time)}</span></td>
                            <td class="p-4">
                                <span class="status-badge status-${b.status || 'pending'}">${escapeHtml(b.status || 'pending')}</span>
                            </td>
                            <td class="p-4 text-right font-mono">
                                <a href="/staff/bookings?search=${encodeURIComponent(b.booking_number || b.booking_reference)}" class="text-[#BDEBFA] hover:underline font-bold">Edit / Manage</a>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    </div>
</div>
`;

  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(renderStaffLayout('Dashboard', content, 'dashboard'));
}

function handleStaffBookings(res, reqUrl) {
  const currentDb = getDb();
  let bookings = currentDb.bookings || [];
  const services = currentDb.services || [];

  const parsed = url.parse(reqUrl, true);
  const filterStatus = parsed.query.status || 'all';
  const searchQuery = (parsed.query.search || '').trim().toLowerCase();

  if (filterStatus !== 'all') {
    bookings = bookings.filter(b => (b.status || 'pending').toLowerCase() === filterStatus.toLowerCase());
  }

  if (searchQuery) {
    bookings = bookings.filter(b => {
      const target = `${b.booking_number} ${b.booking_reference} ${b.customer_name} ${b.customer_email} ${b.customer_phone} ${b.vehicle_info} ${b.service_name}`.toLowerCase();
      return target.includes(searchQuery);
    });
  }

  const content = `
<div>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#BDEBFA]/15 mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Bookings Management</h1>
            <p class="text-xs text-[#D8F1FA]/70 mt-1 font-mono">Create, review, update status, and manage shop schedule</p>
        </div>
        <button onclick="document.getElementById('modal-create-booking').classList.remove('hidden')" class="px-5 py-2.5 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] transition-colors flex items-center gap-2 rounded shadow-md shadow-[#087DB5]/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            + Create Manual Booking
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-4 mb-6 flex flex-col md:flex-row gap-4 justify-between items-center rounded-lg shadow-sm">
        <!-- Status Tabs -->
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            <a href="/staff/bookings?status=all${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 rounded border ${filterStatus === 'all' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">All (${(currentDb.bookings || []).length})</a>
            <a href="/staff/bookings?status=pending${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 rounded border ${filterStatus === 'pending' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">Pending</a>
            <a href="/staff/bookings?status=confirmed${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 rounded border ${filterStatus === 'confirmed' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">Confirmed</a>
            <a href="/staff/bookings?status=in_progress${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 rounded border ${filterStatus === 'in_progress' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">In Progress</a>
            <a href="/staff/bookings?status=completed${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 rounded border ${filterStatus === 'completed' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">Completed</a>
            <a href="/staff/bookings?status=cancelled${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 rounded border ${filterStatus === 'cancelled' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">Cancelled</a>
        </div>

        <!-- Search Input -->
        <form method="GET" action="/staff/bookings" class="flex gap-2 w-full md:w-auto">
            <input type="hidden" name="status" value="${escapeHtml(filterStatus)}">
            <input type="text" name="search" value="${escapeHtml(searchQuery)}" placeholder="Search customer, VIN, ref..." class="bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-1.5 text-xs text-white focus:outline-none focus:border-[#BDEBFA] rounded w-full md:w-64">
            <button type="submit" class="px-4 py-1.5 bg-[#087DB5] text-white text-xs font-mono hover:bg-[#0A92D4] rounded">Filter</button>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="border border-[#BDEBFA]/20 bg-[#0B2B47] rounded-lg overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#BDEBFA]/15 font-mono text-[#D8F1FA]/70 bg-[#041321]">
                    <tr>
                        <th class="p-4">Ref #</th>
                        <th class="p-4">Customer Details</th>
                        <th class="p-4">Vehicle</th>
                        <th class="p-4">Requested Service</th>
                        <th class="p-4">Date &amp; Time</th>
                        <th class="p-4">Status</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BDEBFA]/10">
                    ${bookings.length === 0 ? `
                        <tr><td colspan="7" class="p-12 text-center text-[#D8F1FA]/60 font-mono">No matching bookings found.</td></tr>
                    ` : bookings.map(b => `
                        <tr class="hover:bg-[#0E3557] transition-colors">
                            <td class="p-4 font-mono text-[#BDEBFA] font-bold">${escapeHtml(b.booking_number || b.booking_reference)}</td>
                            <td class="p-4">
                                <div class="font-bold text-white text-sm">${escapeHtml(b.customer_name)}</div>
                                <div class="text-[#D8F1FA]/70 font-mono">${escapeHtml(b.customer_phone)}</div>
                                <div class="text-[#D8F1FA]/50 text-[11px] font-mono">${escapeHtml(b.customer_email)}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-[#D8F1FA]/90 font-medium">${escapeHtml(b.vehicle_info || (b.vehicle_year ? b.vehicle_year + ' ' + b.vehicle_make + ' ' + b.vehicle_model : 'Vehicle Not Specified'))}</div>
                                ${b.vehicle_vin ? `<div class="text-[10px] text-[#D8F1FA]/50 font-mono">VIN: ${escapeHtml(b.vehicle_vin)}</div>` : ''}
                            </td>
                            <td class="p-4 text-white font-medium">${escapeHtml(b.service_name)}</td>
                            <td class="p-4 font-mono">
                                <div class="text-[#D8F1FA]/90">${escapeHtml(b.preferred_date)}</div>
                                <div class="text-[#D8F1FA]/50 text-[11px]">${escapeHtml(b.preferred_time)}</div>
                            </td>
                            <td class="p-4">
                                <span class="status-badge status-${b.status || 'pending'}">${escapeHtml(b.status || 'pending')}</span>
                            </td>
                            <td class="p-4 text-right space-x-2 font-mono">
                                <button onclick="openEditBooking(${JSON.stringify(b).replace(/"/g, '&quot;')})" class="px-2.5 py-1.5 border border-[#BDEBFA]/25 text-[#D8F1FA] hover:border-[#BDEBFA] hover:text-white text-[11px] rounded">
                                    Edit
                                </button>
                                <button onclick="confirmDeleteBooking(${b.id}, '${escapeHtml(b.booking_number || b.booking_reference)}')" class="px-2.5 py-1.5 border border-red-500/40 text-red-400 hover:bg-red-500/20 text-[11px] rounded">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Create Booking -->
    <div id="modal-create-booking" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/30 max-w-2xl w-full p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto rounded-xl shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#BDEBFA]/15 pb-4">
                <h3 class="text-xl font-bold text-white">Create New Appointment</h3>
                <button onclick="document.getElementById('modal-create-booking').classList.add('hidden')" class="text-[#D8F1FA]/60 hover:text-white text-xl">&times;</button>
            </div>
            <form action="/staff/bookings" method="POST" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Phone Number *</label>
                        <input type="tel" name="customer_phone" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Email Address *</label>
                        <input type="email" name="customer_email" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Requested Service *</label>
                        <select name="service_name" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                            ${services.map(s => `<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)} (${escapeHtml(s.price || 'Quote')})</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Vehicle Details *</label>
                        <input type="text" name="vehicle_info" required placeholder="e.g. 2021 Subaru Outback" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Preferred Date *</label>
                        <input type="date" name="preferred_date" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Preferred Time *</label>
                        <select name="preferred_time" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                            <option value="08:00 AM">08:00 AM</option>
                            <option value="09:00 AM">09:00 AM</option>
                            <option value="10:00 AM" selected>10:00 AM</option>
                            <option value="11:00 AM">11:00 AM</option>
                            <option value="01:00 PM">01:00 PM</option>
                            <option value="02:00 PM">02:00 PM</option>
                            <option value="03:00 PM">03:00 PM</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Initial Status</label>
                    <select name="status" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                        <option value="pending">Pending</option>
                        <option value="confirmed" selected>Confirmed</option>
                        <option value="in_progress">In Progress</option>
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Internal Staff Notes</label>
                    <textarea name="internal_notes" rows="2" placeholder="e.g. Parts assigned to bay 2" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#BDEBFA]/15">
                    <button type="button" onclick="document.getElementById('modal-create-booking').classList.add('hidden')" class="px-4 py-2 border border-[#BDEBFA]/25 text-xs font-mono text-[#D8F1FA]/70 hover:text-white rounded">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] rounded shadow-md">Save Booking</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Booking -->
    <div id="modal-edit-booking" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/30 max-w-2xl w-full p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto rounded-xl shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#BDEBFA]/15 pb-4">
                <h3 class="text-xl font-bold text-white">Edit Booking <span id="edit-modal-ref" class="text-[#BDEBFA] font-mono font-normal"></span></h3>
                <button onclick="document.getElementById('modal-edit-booking').classList.add('hidden')" class="text-[#D8F1FA]/60 hover:text-white text-xl">&times;</button>
            </div>
            <form id="form-edit-booking" method="POST" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Customer Name *</label>
                        <input type="text" id="edit-cust-name" name="customer_name" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Phone Number *</label>
                        <input type="tel" id="edit-cust-phone" name="customer_phone" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Email Address *</label>
                        <input type="email" id="edit-cust-email" name="customer_email" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Requested Service *</label>
                        <select id="edit-service" name="service_name" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                            ${services.map(s => `<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Vehicle Details</label>
                        <input type="text" id="edit-vehicle" name="vehicle_info" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Preferred Date</label>
                        <input type="date" id="edit-date" name="preferred_date" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Status Transition *</label>
                        <select id="edit-status" name="status" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Internal Staff Notes</label>
                    <textarea id="edit-notes" name="internal_notes" rows="3" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#BDEBFA]/15">
                    <button type="button" onclick="document.getElementById('modal-edit-booking').classList.add('hidden')" class="px-4 py-2 border border-[#BDEBFA]/25 text-xs font-mono text-[#D8F1FA]/70 hover:text-white rounded">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] rounded shadow-md">Update Booking</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditBooking(b) {
        document.getElementById('edit-modal-ref').textContent = '(' + (b.booking_number || b.booking_reference) + ')';
        document.getElementById('form-edit-booking').action = '/staff/bookings/' + b.id + '/edit';
        document.getElementById('edit-cust-name').value = b.customer_name || '';
        document.getElementById('edit-cust-phone').value = b.customer_phone || '';
        document.getElementById('edit-cust-email').value = b.customer_email || '';
        document.getElementById('edit-service').value = b.service_name || '';
        document.getElementById('edit-vehicle').value = b.vehicle_info || '';
        document.getElementById('edit-date').value = b.preferred_date || '';
        document.getElementById('edit-status').value = b.status || 'pending';
        document.getElementById('edit-notes').value = b.internal_notes || b.notes || '';
        document.getElementById('modal-edit-booking').classList.remove('hidden');
    }

    function confirmDeleteBooking(id, ref) {
        if (confirm('Are you sure you want to permanently delete booking ' + ref + '? This action cannot be undone.')) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '/staff/bookings/' + id + '/delete';
            document.body.appendChild(f);
            f.submit();
        }
    }
</script>
`;

  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(renderStaffLayout('Bookings Management', content, 'bookings'));
}

function handleStaffServices(res, reqUrl) {
  const currentDb = getDb();
  let services = currentDb.services || [];

  const parsed = url.parse(reqUrl, true);
  const filterCat = parsed.query.category || 'all';
  const searchQuery = (parsed.query.search || '').trim().toLowerCase();

  const categories = Array.from(new Set(services.map(s => s.category || 'General Repair')));

  if (filterCat !== 'all') {
    services = services.filter(s => (s.category || '').toLowerCase() === filterCat.toLowerCase());
  }

  if (searchQuery) {
    services = services.filter(s => s.name.toLowerCase().includes(searchQuery) || (s.description && s.description.toLowerCase().includes(searchQuery)));
  }

  const content = `
<div>
    <!-- Top Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#BDEBFA]/15 mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Services Catalog</h1>
            <p class="text-xs text-[#D8F1FA]/70 mt-1 font-mono">Manage shop capabilities, pricing, and live website availability</p>
        </div>
        <button onclick="document.getElementById('modal-create-service').classList.remove('hidden')" class="px-5 py-2.5 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] transition-colors flex items-center gap-2 rounded shadow-md shadow-[#087DB5]/25">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            + Add New Service
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-4 mb-6 flex flex-col md:flex-row gap-4 justify-between items-center rounded-lg shadow-sm">
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            <a href="/staff/services?category=all" class="px-3 py-1.5 rounded border ${filterCat === 'all' ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">All Categories</a>
            ${categories.map(c => `
                <a href="/staff/services?category=${encodeURIComponent(c)}" class="px-3 py-1.5 rounded border ${filterCat.toLowerCase() === c.toLowerCase() ? 'border-[#087DB5] bg-[#087DB5] text-white font-bold' : 'border-[#BDEBFA]/20 text-[#D8F1FA]/70 hover:text-white'}">${escapeHtml(c)}</a>
            `).join('')}
        </div>
        <form method="GET" action="/staff/services" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="${escapeHtml(searchQuery)}" placeholder="Search service..." class="bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-1.5 text-xs text-white focus:outline-none focus:border-[#BDEBFA] rounded">
            <button type="submit" class="px-4 py-1.5 bg-[#087DB5] text-white text-xs font-mono hover:bg-[#0A92D4] rounded">Search</button>
        </form>
    </div>

    <!-- Services Table -->
    <div class="border border-[#BDEBFA]/20 bg-[#0B2B47] rounded-lg overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#BDEBFA]/15 font-mono text-[#D8F1FA]/70 bg-[#041321]">
                    <tr>
                        <th class="p-4">Service Name</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Est. Duration</th>
                        <th class="p-4">Status on Live Site</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#BDEBFA]/10">
                    ${services.map(s => `
                        <tr class="hover:bg-[#0E3557] transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-white text-sm">${escapeHtml(s.name)}</div>
                                <div class="text-[#D8F1FA]/70 text-[11px] max-w-md truncate mt-0.5">${escapeHtml(s.description)}</div>
                            </td>
                            <td class="p-4 font-mono text-[#D8F1FA]/90">${escapeHtml(s.category)}</td>
                            <td class="p-4 font-mono text-[#BDEBFA] font-bold text-sm">${escapeHtml(s.price || 'Custom Quote')}</td>
                            <td class="p-4 font-mono text-[#D8F1FA]/70">${s.duration_minutes || 60} mins</td>
                            <td class="p-4">
                                ${s.is_active ? `
                                    <span class="inline-flex items-center px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase bg-emerald-500/15 text-emerald-300 border border-emerald-500/30 rounded">Active &amp; Public</span>
                                ` : `
                                    <span class="inline-flex items-center px-2.5 py-0.5 font-mono text-[10px] font-bold uppercase bg-red-500/15 text-red-300 border border-red-500/30 rounded">Disabled / Hidden</span>
                                `}
                            </td>
                            <td class="p-4 text-right space-x-2 font-mono">
                                <form action="/staff/services/${s.id}/toggle" method="POST" class="inline">
                                    <button type="submit" class="px-2.5 py-1.5 border ${s.is_active ? 'border-amber-400/40 text-amber-300 hover:bg-amber-400/10' : 'border-emerald-400/40 text-emerald-300 hover:bg-emerald-400/10'} text-[11px] rounded">
                                        ${s.is_active ? 'Disable' : 'Enable'}
                                    </button>
                                </form>
                                <button onclick="openEditService(${JSON.stringify(s).replace(/"/g, '&quot;')})" class="px-2.5 py-1.5 border border-[#BDEBFA]/25 text-[#D8F1FA] hover:border-[#BDEBFA] hover:text-white text-[11px] rounded">
                                    Edit
                                </button>
                                <button onclick="confirmDeleteService(${s.id}, '${escapeHtml(s.name)}')" class="px-2.5 py-1.5 border border-red-500/40 text-red-400 hover:bg-red-500/20 text-[11px] rounded">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    `).join('')}
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: Create Service -->
    <div id="modal-create-service" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/30 max-w-xl w-full p-6 sm:p-8 space-y-6 rounded-xl shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#BDEBFA]/15 pb-4">
                <h3 class="text-xl font-bold text-white">Add New Service</h3>
                <button onclick="document.getElementById('modal-create-service').classList.add('hidden')" class="text-[#D8F1FA]/60 hover:text-white text-xl">&times;</button>
            </div>
            <form action="/staff/services" method="POST" class="space-y-4">
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Service Name *</label>
                    <input type="text" name="name" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Category *</label>
                        <input type="text" name="category" required placeholder="e.g. Engine, Brakes" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Price</label>
                        <input type="text" name="price" placeholder="e.g. $149.99 or Custom Quote" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Description *</label>
                    <textarea name="description" rows="3" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#BDEBFA]/15">
                    <button type="button" onclick="document.getElementById('modal-create-service').classList.add('hidden')" class="px-4 py-2 border border-[#BDEBFA]/25 text-xs font-mono text-[#D8F1FA]/70 hover:text-white rounded">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] rounded shadow-md">Create Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Service -->
    <div id="modal-edit-service" class="hidden fixed inset-0 z-50 bg-black/85 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="bg-[#0B2B47] border border-[#BDEBFA]/30 max-w-xl w-full p-6 sm:p-8 space-y-6 rounded-xl shadow-2xl">
            <div class="flex items-center justify-between border-b border-[#BDEBFA]/15 pb-4">
                <h3 class="text-xl font-bold text-white">Edit Service</h3>
                <button onclick="document.getElementById('modal-edit-service').classList.add('hidden')" class="text-[#D8F1FA]/60 hover:text-white text-xl">&times;</button>
            </div>
            <form id="form-edit-service" method="POST" class="space-y-4">
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Service Name *</label>
                    <input type="text" id="edit-srv-name" name="name" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Category *</label>
                        <input type="text" id="edit-srv-cat" name="category" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Price</label>
                        <input type="text" id="edit-srv-price" name="price" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-[#D8F1FA]/80 mb-1">Description *</label>
                    <textarea id="edit-srv-desc" name="description" rows="3" required class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-3 py-2 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#BDEBFA]/15">
                    <button type="button" onclick="document.getElementById('modal-edit-service').classList.add('hidden')" class="px-4 py-2 border border-[#BDEBFA]/25 text-xs font-mono text-[#D8F1FA]/70 hover:text-white rounded">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] rounded shadow-md">Update Service</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function openEditService(s) {
        document.getElementById('form-edit-service').action = '/staff/services/' + s.id + '/edit';
        document.getElementById('edit-srv-name').value = s.name || '';
        document.getElementById('edit-srv-cat').value = s.category || '';
        document.getElementById('edit-srv-price').value = s.price || '';
        document.getElementById('edit-srv-desc').value = s.description || '';
        document.getElementById('modal-edit-service').classList.remove('hidden');
    }

    function confirmDeleteService(id, name) {
        if (confirm('Are you sure you want to delete "' + name + '"?')) {
            const f = document.createElement('form');
            f.method = 'POST';
            f.action = '/staff/services/' + id + '/delete';
            document.body.appendChild(f);
            f.submit();
        }
    }
</script>
`;

  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(renderStaffLayout('Services Catalog', content, 'services'));
}

function handleStaffSettings(res) {
  const currentDb = getDb();
  const settings = currentDb.settings || {};
  const messages = currentDb.contact_messages || [];

  const content = `
<div class="space-y-12">
    <!-- Shop Information -->
    <div>
        <div class="border-b border-[#BDEBFA]/15 pb-4 mb-6">
            <h1 class="text-2xl font-extrabold text-white">General Shop Settings</h1>
            <p class="text-xs text-[#D8F1FA]/70 mt-1 font-mono">Update shop contact numbers, physical location, and operating hours</p>
        </div>
        <form action="/staff/settings" method="POST" class="bg-[#0B2B47] border border-[#BDEBFA]/20 p-6 sm:p-8 max-w-3xl space-y-6 rounded-lg shadow-md">
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-mono text-xs uppercase text-[#D8F1FA]/80 mb-2">Shop Name</label>
                    <input type="text" name="shop_name" value="${escapeHtml(settings.shop_name || 'North Fork Auto')}" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                </div>
                <div>
                    <label class="block font-mono text-xs uppercase text-[#D8F1FA]/80 mb-2">Primary Phone</label>
                    <input type="text" name="shop_phone" value="${escapeHtml(settings.shop_phone || '(907) 733-3030')}" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-mono text-xs uppercase text-[#D8F1FA]/80 mb-2">Contact Email</label>
                    <input type="email" name="shop_email" value="${escapeHtml(settings.shop_email || 'info@northforkauto.com')}" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                </div>
                <div>
                    <label class="block font-mono text-xs uppercase text-[#D8F1FA]/80 mb-2">Physical Address</label>
                    <input type="text" name="shop_address" value="${escapeHtml(settings.shop_address || 'Talkeetna Spur Road, Talkeetna, AK 99676')}" class="w-full bg-[#071F35] border border-[#BDEBFA]/25 px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#BDEBFA] rounded">
                </div>
            </div>
            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#087DB5] text-white text-xs font-mono uppercase font-bold hover:bg-[#0A92D4] rounded shadow-md">Save Settings</button>
            </div>
        </form>
    </div>

    <!-- Customer Inquiries Inbox -->
    <div>
        <div class="border-b border-[#BDEBFA]/15 pb-4 mb-6">
            <h2 class="text-xl font-bold text-white">Customer Inquiries Inbox (${messages.length})</h2>
            <p class="text-xs text-[#D8F1FA]/70 mt-1 font-mono">Inbound messages submitted through the public contact form</p>
        </div>
        <div class="border border-[#BDEBFA]/20 bg-[#0B2B47] rounded-lg overflow-hidden shadow-lg">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-[#BDEBFA]/15 font-mono text-[#D8F1FA]/70 bg-[#041321]">
                        <tr>
                            <th class="p-4">Sender</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4">Message</th>
                            <th class="p-4">Received Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#BDEBFA]/10">
                        ${messages.length === 0 ? `
                            <tr><td colspan="4" class="p-8 text-center text-[#D8F1FA]/60 font-mono">No customer inquiries received yet.</td></tr>
                        ` : messages.map(m => `
                            <tr class="hover:bg-[#0E3557] transition-colors">
                                <td class="p-4 font-bold text-white">
                                    <div>${escapeHtml(m.name || 'Anonymous')}</div>
                                    <div class="text-[11px] text-[#D8F1FA]/70 font-mono font-normal">${escapeHtml(m.email || '')} ${m.phone ? '· ' + escapeHtml(m.phone) : ''}</div>
                                </td>
                                <td class="p-4 font-medium text-[#BDEBFA]">${escapeHtml(m.subject || 'General Inquiry')}</td>
                                <td class="p-4 text-[#D8F1FA]/90 max-w-lg leading-relaxed">${escapeHtml(m.message || '')}</td>
                                <td class="p-4 font-mono text-[#D8F1FA]/60">${new Date(m.created_at).toLocaleDateString()}</td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
`;

  res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(renderStaffLayout('Settings & Inbox', content, 'settings'));
}

// -------------------------------------------------------------
// MAIN HTTP SERVER & ROUTER
// -------------------------------------------------------------

const server = http.createServer((req, res) => {
  const parsed = url.parse(req.url, true);
  const pathname = parsed.pathname;
  const method = req.method;
  const clientIp = req.socket.remoteAddress || '127.0.0.1';

  // Parse Cookie
  const cookies = (req.headers.cookie || '').split(';').reduce((acc, c) => {
    const [k, v] = c.trim().split('=');
    if (k && v) acc[k] = v;
    return acc;
  }, {});
  const sessionCookie = cookies['nfa_session'];
  const isAuthenticated = sessionCookie && activeSessions.has(sessionCookie);

  // 0. STATIC ASSETS ROUTE (images, css, js)
  if (method === 'GET' && (pathname.startsWith('/images/') || pathname.startsWith('/public/') || pathname.startsWith('/css/') || pathname.startsWith('/js/'))) {
    const relPath = pathname.startsWith('/public/') ? pathname.slice(8) : pathname.slice(1);
    const filePath = path.join(__dirname, 'public', relPath);
    if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
      const ext = path.extname(filePath).toLowerCase();
      const mimeTypes = {
        '.png': 'image/png',
        '.jpg': 'image/jpeg',
        '.jpeg': 'image/jpeg',
        '.svg': 'image/svg+xml',
        '.gif': 'image/gif',
        '.webp': 'image/webp',
        '.ico': 'image/x-icon',
        '.css': 'text/css',
        '.js': 'text/javascript'
      };
      res.writeHead(200, { 'Content-Type': mimeTypes[ext] || 'application/octet-stream' });
      return res.end(fs.readFileSync(filePath));
    }
  }

  // 1. PUBLIC GET & HEAD ROUTES
  if ((method === 'GET' || method === 'HEAD') && pathname === '/') {
    const isScrolled = parsed.query && parsed.query.scrolled === '1';
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    if (method === 'HEAD') return res.end();
    return res.end(handleHome(isScrolled));
  }

  if ((method === 'GET' || method === 'HEAD') && pathname === '/services') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    if (method === 'HEAD') return res.end();
    return res.end(handleServices());
  }

  if ((method === 'GET' || method === 'HEAD') && pathname === '/about') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    if (method === 'HEAD') return res.end();
    return res.end(handleAbout());
  }

  if ((method === 'GET' || method === 'HEAD') && pathname === '/reviews') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    if (method === 'HEAD') return res.end();
    return res.end(handleReviews());
  }

  if ((method === 'GET' || method === 'HEAD') && pathname === '/contact') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    if (method === 'HEAD') return res.end();
    return res.end(handleContact());
  }

  if ((method === 'GET' || method === 'HEAD') && pathname === '/book') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    if (method === 'HEAD') return res.end();
    return res.end(handleBook());
  }

  // 1.5 CHATBOT API ROUTES
  if (method === 'POST' && pathname === '/api/chat') {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', async () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}

      const userMessage = (params.message || '').trim().slice(0, 500);
      const sessionId = (params.session_id || '').trim() || ('sess_' + Math.random().toString(36).substring(2));

      if (!userMessage) {
        res.writeHead(400);
        return res.end(JSON.stringify({ success: false, error: 'Message cannot be empty.' }));
      }

      if (!checkChatRateLimit(sessionId, clientIp)) {
        res.writeHead(429);
        return res.end(JSON.stringify({
          success: false,
          error: 'rate_limit',
          reply: 'You are sending messages too quickly. Please wait a moment before sending another message, or call us at (907) 733-3030.',
          session_id: sessionId
        }));
      }

      const allConvs = loadConversations();
      if (!allConvs[sessionId]) {
        allConvs[sessionId] = {
          session_id: sessionId,
          created_at: new Date().toISOString(),
          updated_at: new Date().toISOString(),
          daily_count: 0,
          last_active: new Date().toISOString().slice(0, 10),
          context: {
            vehicle_year: null,
            vehicle_make: null,
            vehicle_model: null,
            vehicle_full: null,
            current_issue: null,
            service_needed: null,
          },
          messages: [],
          pendingAppointmentId: null,
          verifiedAppointmentId: null
        };
      }
      session = allConvs[sessionId];

      const today = new Date().toISOString().slice(0, 10);
      if (session.last_active !== today) {
        session.daily_count = 0;
        session.last_active = today;
      }

      // Check daily limit: AI_CHATBOT_DAILY_LIMIT=100
      const dailyLimit = parseInt(process.env.AI_CHATBOT_DAILY_LIMIT || '100', 10);
      if ((session.daily_count || 0) >= dailyLimit) {
        res.writeHead(429);
        return res.end(JSON.stringify({
          success: false,
          error: 'daily_limit_reached',
          reply: `You have reached the maximum daily message limit for this chat session. Please give our Talkeetna shop a call at ${SHOP_INFO.phone} or ${SHOP_INFO.emergencyPhone} for immediate assistance.`,
          session_id: sessionId
        }));
      }
      session.daily_count = (session.daily_count || 0) + 1;
      session.updated_at = new Date().toISOString();

      // Ingest client history if server session was empty
      if ((!session.messages || session.messages.length === 0) && Array.isArray(params.history) && params.history.length > 0) {
        for (const ch of params.history) {
          if (ch && ch.content && ['user', 'assistant'].includes(ch.role)) {
            session.messages.push({
              role: ch.role,
              content: String(ch.content),
              time: ch.time || new Date().toISOString()
            });
            if (ch.role === 'user') {
              extractVehicleAndIssueContext(String(ch.content), session.context);
            }
          }
        }
      }

      extractVehicleAndIssueContext(userMessage, session.context);

      session.messages.push({
        role: 'user',
        content: userMessage,
        time: new Date().toISOString()
      });

      const lockoutKey = `verify_lockout:${sessionId}:${clientIp}`;
      let botReply = null;
      let appointmentPayload = null;

      // 0. Deterministic High-Speed Intent Engine (<20ms execution)
      const currentDb = getDb();
      const smartResult = classifyAndResolveIntent(userMessage, session.messages, currentDb, session.context);
      if (smartResult && smartResult.reply) {
        botReply = smartResult.reply;
        if (smartResult.appointment) {
          appointmentPayload = smartResult.appointment;
          session.verifiedAppointmentId = smartResult.appointment.confirmation_id;
        }
      }

      // 1. Check if user provided an appointment confirmation ID (e.g. NFA-2026-8659)
      const extractedId = extractConfirmationId(userMessage);
      if (botReply === null && extractedId) {
        const appt = findAppointmentInDb(extractedId);
        if (!appt) {
          botReply = "I couldn't find an appointment with that confirmation number. Please double-check the ID and try again.";
        } else {
          let isVerified = (session.verifiedAppointmentId === extractedId);

          // Check if customer provided verification details in the same query
          if (!isVerified) {
            const detail = extractVerificationDetail(userMessage);
            if (detail) {
              const vr = verifyAppointmentCustomer(appt, detail.value, lockoutKey);
              if (vr.verified) {
                isVerified = true;
                session.verifiedAppointmentId = extractedId;
                session.pendingAppointmentId = null;
              }
            }
          }

          if (isVerified) {
            const summary = formatAppointmentSummary(appt, true);
            appointmentPayload = summary;
            botReply = `Your appointment is confirmed for ${summary.date} at ${summary.time}. You're booked for ${summary.service_name} (Status: ${summary.status}). Your confirmation number is ${summary.confirmation_id}.`;
          } else {
            session.pendingAppointmentId = extractedId;
            botReply = `I found your appointment (${extractedId}). For privacy, please verify the email address or phone number associated with your booking before I share its details.`;
          }
        }
      }

      // 2. If waiting for customer verification on a pending appointment
      else if (botReply === null && session.pendingAppointmentId) {
        const pendingId = session.pendingAppointmentId;
        const detail = extractVerificationDetail(userMessage);

        if (detail) {
          const appt = findAppointmentInDb(pendingId);
          if (appt) {
            const vr = verifyAppointmentCustomer(appt, detail.value, lockoutKey);
            if (vr.locked_out) {
              botReply = vr.message;
            } else if (vr.verified) {
              session.verifiedAppointmentId = pendingId;
              session.pendingAppointmentId = null;
              const summary = formatAppointmentSummary(appt, true);
              appointmentPayload = summary;
              botReply = `Your appointment is confirmed for ${summary.date} at ${summary.time}. You're booked for ${summary.service_name} (Status: ${summary.status}). Your confirmation number is ${summary.confirmation_id}.`;
            } else {
              botReply = "The verification details provided do not match our records for that appointment. For security, please check your information and try again, or call us directly at (907) 733-3030.";
            }
          } else {
            session.pendingAppointmentId = null;
            botReply = "I couldn't find an appointment with that confirmation number. Please double-check the ID and try again.";
          }
        }
      }

      // 3. Check general appointment query without an ID
      if (botReply === null) {
        const isAskingAppointment = /\b(check|find|look up|view|status of|when is|what time is|my)\b.*\b(appointment|booking|scheduled|schedule|meeting)\b/i.test(userMessage)
          || /\b(when is my appointment|check my appointment|my appointment|check confirmation)\b/i.test(userMessage);

        if (isAskingAppointment) {
          if (session.verifiedAppointmentId) {
            const appt = findAppointmentInDb(session.verifiedAppointmentId);
            if (appt) {
              const summary = formatAppointmentSummary(appt, true);
              appointmentPayload = summary;
              botReply = `Your appointment is confirmed for ${summary.date} at ${summary.time} for ${summary.service_name} (Confirmation: ${summary.confirmation_id}).`;
            }
          }

          if (botReply === null) {
            botReply = "I can check your appointment! What is your confirmation number (for example, NFA-2026-xxxx)?";
          }
        }
      }

      // 4. General automotive query -> Cloudflare Workers AI
      if (botReply === null) {
        const aiMessages = [...session.messages];
        if (session.verifiedAppointmentId) {
          const appt = findAppointmentInDb(session.verifiedAppointmentId);
          if (appt) {
            const summary = formatAppointmentSummary(appt, true);
            aiMessages.unshift({
              role: 'system',
              content: `[VERIFIED GROUND TRUTH: Appointment ${summary.confirmation_id} confirmed for ${summary.date} at ${summary.time}. Service: ${summary.service_name}. Vehicle: ${summary.vehicle}. Status: ${summary.status}.]`
            });
          }
        }

        const aiResult = await callCloudflareWorkersAI(aiMessages, session.context);
        botReply = aiResult.reply || "I'm having trouble connecting right now. Please call our team at (907) 733-3030.";
      }

      session.messages.push({
        role: 'assistant',
        content: botReply,
        time: new Date().toISOString()
      });

      if (session.messages.length > 30) {
        session.messages = session.messages.slice(-30);
      }
      allConvs[sessionId] = session;
      saveConversations(allConvs);

      res.writeHead(200);
      const resPayload = {
        success: true,
        reply: botReply,
        session_id: sessionId,
        timestamp: new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })
      };
      if (appointmentPayload) {
        resPayload.appointment = appointmentPayload;
      }
      return res.end(JSON.stringify(resPayload));
    });
    return;
  }

  // 1.6 REAL APPOINTMENT LOOKUP & VERIFICATION REST API
  if (method === 'POST' && (pathname === '/api/appointments/lookup' || pathname === '/appointments/lookup')) {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}

      const confId = normalizeConfirmationId(params.confirmation_id || '');
      const verificationDetail = (params.verification_detail || '').trim();
      const sessionId = (params.session_id || '').trim() || ('sess_' + clientIp);

      if (!confId) {
        res.writeHead(400);
        return res.end(JSON.stringify({ success: false, error: 'Confirmation ID is required.' }));
      }

      const appt = findAppointmentInDb(confId);
      if (!appt) {
        res.writeHead(404);
        return res.end(JSON.stringify({
          success: false,
          error: 'not_found',
          message: "I couldn't find an appointment with that confirmation number. Please double-check the ID and try again."
        }));
      }

      let isVerified = false;
      const lockoutKey = `verify_lockout:${sessionId}:${clientIp}`;

      if (verificationDetail) {
        const vr = verifyAppointmentCustomer(appt, verificationDetail, lockoutKey);
        if (vr.locked_out) {
          res.writeHead(429);
          return res.end(JSON.stringify({ success: false, error: 'locked_out', message: vr.message }));
        }
        if (!vr.verified) {
          res.writeHead(403);
          return res.end(JSON.stringify({
            success: false,
            error: 'verification_failed',
            attempts_left: vr.attempts_left,
            message: vr.message
          }));
        }
        isVerified = true;
      }

      const summary = formatAppointmentSummary(appt, isVerified);
      const reply = isVerified
        ? `Your appointment is confirmed for ${summary.date} at ${summary.time}. You're booked for ${summary.service_name} (Status: ${summary.status}). Your confirmation number is ${summary.confirmation_id}.`
        : `I found your appointment (${summary.confirmation_id}). For privacy, please verify the email address or phone number associated with your booking before I share its details.`;

      res.writeHead(200);
      return res.end(JSON.stringify({
        success: true,
        verified: isVerified,
        appointment: summary,
        reply
      }));
    });
    return;
  }

  if (method === 'POST' && (pathname === '/api/appointments/verify' || pathname === '/appointments/verify')) {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}

      const confId = normalizeConfirmationId(params.confirmation_id || '');
      const verificationDetail = (params.verification_detail || '').trim();
      const sessionId = (params.session_id || '').trim() || ('sess_' + clientIp);

      if (!confId || !verificationDetail) {
        res.writeHead(400);
        return res.end(JSON.stringify({ success: false, error: 'confirmation_id and verification_detail are required.' }));
      }

      const appt = findAppointmentInDb(confId);
      if (!appt) {
        res.writeHead(404);
        return res.end(JSON.stringify({ success: false, error: 'not_found', message: "Appointment not found." }));
      }

      const lockoutKey = `verify_lockout:${sessionId}:${clientIp}`;
      const vr = verifyAppointmentCustomer(appt, verificationDetail, lockoutKey);

      if (vr.locked_out) {
        res.writeHead(429);
        return res.end(JSON.stringify({ success: false, error: 'locked_out', message: vr.message }));
      }

      if (!vr.verified) {
        res.writeHead(403);
        return res.end(JSON.stringify({
          success: false,
          error: 'verification_failed',
          attempts_left: vr.attempts_left,
          message: vr.message
        }));
      }

      const summary = formatAppointmentSummary(appt, true);
      res.writeHead(200);
      return res.end(JSON.stringify({
        success: true,
        verified: true,
        appointment: summary,
        reply: `Your appointment is confirmed for ${summary.date} at ${summary.time}. You're booked for ${summary.service_name} (Status: ${summary.status}). Your confirmation number is ${summary.confirmation_id}.`
      }));
    });
    return;
  }

  if (method === 'GET' && (pathname === '/api/appointments/provider-status' || pathname === '/appointments/provider-status')) {
    res.writeHead(200, { 'Content-Type': 'application/json' });
    return res.end(JSON.stringify({
      success: true,
      status: {
        provider: 'local_database',
        connected: true,
        source_of_truth: 'storage/app/database.json',
        timezone: 'America/Anchorage (Alaska Time)',
        message: 'Primary appointment source of truth is the local North Fork Auto database (storage/app/database.json). No third-party calendar credentials configured.'
      }
    }));
  }

  if (method === 'POST' && (pathname === '/api/appointments/reschedule' || pathname === '/appointments/reschedule')) {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}

      const confId = normalizeConfirmationId(params.confirmation_id || '');
      const verificationDetail = (params.verification_detail || '').trim();
      const preferredDate = (params.preferred_date || '').trim();
      const preferredTime = (params.preferred_time || '').trim();
      const confirmed = params.confirmed === true;

      if (!confirmed) {
        res.writeHead(400);
        return res.end(JSON.stringify({ success: false, error: 'confirmation_required', message: 'Explicit customer confirmation is required before rescheduling.' }));
      }

      const appt = findAppointmentInDb(confId);
      if (!appt) {
        res.writeHead(404);
        return res.end(JSON.stringify({ success: false, error: 'not_found', message: 'Appointment not found.' }));
      }

      const lockoutKey = `verify_lockout:${params.session_id || clientIp}:${clientIp}`;
      const vr = verifyAppointmentCustomer(appt, verificationDetail, lockoutKey);
      if (!vr.verified) {
        res.writeHead(403);
        return res.end(JSON.stringify({ success: false, error: 'verification_failed', message: 'Verification failed.' }));
      }

      const currentDb = getDb();
      for (const b of currentDb.bookings) {
        if (normalizeConfirmationId(b.booking_number || b.booking_reference || String(b.id)) === confId) {
          b.preferred_date = preferredDate;
          b.preferred_time = preferredTime;
          b.status = 'rescheduled';
          b.internal_notes = ((b.internal_notes || '') + '\n' + new Date().toISOString() + ': Rescheduled via Virtual Assistant').trim();
          break;
        }
      }
      saveDb(currentDb);

      res.writeHead(200);
      return res.end(JSON.stringify({
        success: true,
        message: `Your appointment (${confId}) has been successfully rescheduled to ${preferredDate} at ${preferredTime}.`
      }));
    });
    return;
  }

  if (method === 'POST' && (pathname === '/api/appointments/cancel' || pathname === '/appointments/cancel')) {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}

      const confId = normalizeConfirmationId(params.confirmation_id || '');
      const verificationDetail = (params.verification_detail || '').trim();
      const confirmed = params.confirmed === true;
      const reason = (params.reason || 'Customer requested').trim();

      if (!confirmed) {
        res.writeHead(400);
        return res.end(JSON.stringify({ success: false, error: 'confirmation_required', message: 'Explicit customer confirmation is required before cancelling.' }));
      }

      const appt = findAppointmentInDb(confId);
      if (!appt) {
        res.writeHead(404);
        return res.end(JSON.stringify({ success: false, error: 'not_found', message: 'Appointment not found.' }));
      }

      const lockoutKey = `verify_lockout:${params.session_id || clientIp}:${clientIp}`;
      const vr = verifyAppointmentCustomer(appt, verificationDetail, lockoutKey);
      if (!vr.verified) {
        res.writeHead(403);
        return res.end(JSON.stringify({ success: false, error: 'verification_failed', message: 'Verification failed.' }));
      }

      const currentDb = getDb();
      for (const b of currentDb.bookings) {
        if (normalizeConfirmationId(b.booking_number || b.booking_reference || String(b.id)) === confId) {
          b.status = 'cancelled';
          b.internal_notes = ((b.internal_notes || '') + '\n' + new Date().toISOString() + ': Cancelled. Reason: ' + reason).trim();
          break;
        }
      }
      saveDb(currentDb);

      res.writeHead(200);
      return res.end(JSON.stringify({
        success: true,
        message: `Your appointment (${confId}) has been cancelled.`
      }));
    });
    return;
  }

  // 1.7 REAL APPOINTMENT BOOKING REST API
  if (method === 'POST' && (pathname === '/api/chat/appointment' || pathname === '/api/appointments/book' || pathname === '/api/book')) {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}

      const currentDb = getDb();
      const ref = 'NFA-2026-' + Math.floor(1000 + Math.random() * 9000);
      const newBooking = {
        id: Date.now(),
        booking_number: ref,
        booking_reference: ref,
        customer_name: params.name || 'Website Visitor',
        customer_email: params.email || '',
        customer_phone: params.phone || '',
        service_name: params.service || 'Diagnostic Inspection',
        preferred_date: params.preferred_date || new Date().toISOString().split('T')[0],
        preferred_time: params.preferred_time || '10:00 AM',
        vehicle_year: params.vehicle_year || '2020',
        vehicle_make: params.vehicle_make || 'Standard',
        vehicle_model: params.vehicle_model || 'Vehicle',
        vehicle_info: `${params.vehicle_year || ''} ${params.vehicle_make || ''} ${params.vehicle_model || ''}`.trim(),
        notes: params.notes || '',
        internal_notes: 'Created via AI Auto Assistant',
        source: 'ai_chatbot',
        status: 'pending',
        created_at: new Date().toISOString()
      };

      currentDb.bookings.unshift(newBooking);
      saveDb(currentDb);

      res.writeHead(200);
      return res.end(JSON.stringify({
        success: true,
        booking_reference: ref,
        message: `Your appointment request (${ref}) has been received! Our team at North Fork Auto will contact you to confirm final availability.`,
        booking: newBooking
      }));
    });
    return;
  }

  if (method === 'GET' && pathname === '/api/chat/history') {
    res.setHeader('Content-Type', 'application/json');
    const sessionId = parsed.query.session_id || '';
    const allConvs = loadConversations();
    const session = allConvs[sessionId];
    res.writeHead(200);
    return res.end(JSON.stringify({
      success: true,
      messages: session ? session.messages : [],
      context: session ? session.context : {}
    }));
  }

  if (method === 'POST' && pathname === '/api/chat/clear') {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}
      if (params.session_id) {
        const allConvs = loadConversations();
        if (allConvs[params.session_id]) {
          allConvs[params.session_id].messages = [];
          allConvs[params.session_id].context = {
            vehicle_year: null, vehicle_make: null, vehicle_model: null,
            vehicle_full: null, current_issue: null, service_needed: null
          };
          allConvs[params.session_id].updated_at = new Date().toISOString();
          saveConversations(allConvs);
        }
      }
      res.writeHead(200);
      return res.end(JSON.stringify({ success: true, message: 'Conversation history cleared.' }));
    });
    return;
  }

  // 2. PUBLIC POST: CONTACT
  if (method === 'POST' && pathname === '/contact') {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      const currentDb = getDb();
      let params = {};
      const isJson = req.headers['content-type'] && req.headers['content-type'].includes('application/json');
      if (isJson) {
        try { params = JSON.parse(body || '{}'); } catch(e){}
      } else {
        const u = new URLSearchParams(body);
        params = Object.fromEntries(u.entries());
      }
      currentDb.contact_messages.unshift({
        id: Date.now(),
        name: params.name || '',
        email: params.email || '',
        phone: params.phone || '',
        subject: params.subject || 'General Inquiry',
        message: params.message || '',
        created_at: new Date().toISOString()
      });
      saveDb(currentDb);

      if (isJson || (req.headers['accept'] && req.headers['accept'].includes('application/json'))) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        return res.end(JSON.stringify({ success: true, message: 'Your message has been sent successfully.' }));
      }
      res.writeHead(302, { 'Location': '/contact?success=1' });
      res.end();
    });
    return;
  }

  // 3. PUBLIC POST: BOOKING SUBMISSION
  if (method === 'POST' && pathname === '/book') {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      const currentDb = getDb();
      let params = {};
      const isJson = req.headers['content-type'] && req.headers['content-type'].includes('application/json');
      if (isJson) {
        try { params = JSON.parse(body || '{}'); } catch(e){}
      } else {
        const u = new URLSearchParams(body);
        params = Object.fromEntries(u.entries());
      }

      const ref = 'NFA-2026-' + Math.floor(1000 + Math.random() * 9000);
      const newBooking = {
        id: Date.now(),
        booking_number: ref,
        booking_reference: ref,
        customer_name: params.name || params.customer_name || 'Customer',
        customer_email: params.email || params.customer_email || '',
        customer_phone: params.phone || params.customer_phone || '',
        service_name: params.service || params.service_name || 'Diagnostic Inspection',
        preferred_date: params.preferred_date || params.date || new Date().toISOString().split('T')[0],
        preferred_time: params.preferred_time || params.time || '10:00 AM',
        vehicle_year: params.vehicle_year || '2020',
        vehicle_make: params.vehicle_make || 'Toyota',
        vehicle_model: params.vehicle_model || 'Standard',
        vehicle_info: params.vehicle_make ? `${params.vehicle_year || ''} ${params.vehicle_make} ${params.vehicle_model || ''}`.trim() : (params.vehicle_info || 'Standard Vehicle'),
        vehicle_mileage: params.vehicle_mileage || null,
        vehicle_vin: params.vehicle_vin || null,
        notes: params.notes || '',
        internal_notes: null,
        status: 'pending',
        created_at: new Date().toISOString()
      };

      currentDb.bookings.unshift(newBooking);
      saveDb(currentDb);

      if (isJson || (req.headers['accept'] && req.headers['accept'].includes('application/json'))) {
        res.writeHead(200, { 'Content-Type': 'application/json' });
        return res.end(JSON.stringify({ success: true, booking: newBooking }));
      }
      res.writeHead(302, { 'Location': '/book?success=1&ref=' + ref });
      res.end();
    });
    return;
  }

  // 4. AUTHENTICATION ROUTES: LOGIN & LOGOUT
  if (pathname === '/staff/login' || pathname === '/admin/login' || pathname === '/login') {
    if (method === 'GET') {
      if (isAuthenticated) {
        res.writeHead(302, { 'Location': '/staff/dashboard' });
        return res.end();
      }
      const err = parsed.query.error === 'rate_limit'
        ? 'Too many failed attempts. Please wait 60 seconds.'
        : (parsed.query.error === 'invalid_credentials' ? 'Invalid staff email or password.' : '');
      return handleStaffLogin(res, err);
    }

    if (method === 'POST') {
      let body = '';
      req.on('data', chunk => body += chunk);
      req.on('end', () => {
        if (!checkRateLimit(clientIp)) {
          res.writeHead(302, { 'Location': '/staff/login?error=rate_limit' });
          return res.end();
        }

        const params = new URLSearchParams(body);
        const email = (params.get('email') || '').trim();
        const password = (params.get('password') || '').trim();

        const validStaff = (
          (email === 'admin@northforkauto.com' && (password === 'admin123456' || password === 'admin')) ||
          (email === 'staff@northforkauto.com' && (password === 'staff123456' || password === 'staff'))
        );

        if (validStaff) {
          clearRateLimit(clientIp);
          const token = 'nfa_' + Math.random().toString(36).substring(2) + Date.now().toString(36);
          activeSessions.add(token);
          res.writeHead(302, {
            'Set-Cookie': `nfa_session=${token}; Path=/; HttpOnly; SameSite=Lax`,
            'Location': '/staff/dashboard'
          });
          return res.end();
        }

        recordFailedLogin(clientIp);
        res.writeHead(302, { 'Location': '/staff/login?error=invalid_credentials' });
        res.end();
      });
      return;
    }
  }

  if (pathname === '/staff/logout' || pathname === '/admin/logout' || pathname === '/logout') {
    if (sessionCookie) activeSessions.delete(sessionCookie);
    res.writeHead(302, {
      'Set-Cookie': 'nfa_session=; Path=/; Expires=Thu, 01 Jan 1970 00:00:00 GMT',
      'Location': '/staff/login'
    });
    return res.end();
  }

  // 5. PROTECTED STAFF / ADMIN ROUTES (BLOCK UNAUTHENTICATED USERS)
  const isProtected = pathname.startsWith('/staff') || pathname.startsWith('/admin');
  if (isProtected) {
    if (!isAuthenticated) {
      res.writeHead(302, { 'Location': '/staff/login' });
      return res.end();
    }

    // A. Dashboard
    if (pathname === '/staff' || pathname === '/staff/dashboard' || pathname === '/admin' || pathname === '/admin/dashboard') {
      return handleStaffDashboard(res);
    }

    // B. Bookings GET
    if (pathname === '/staff/bookings' || pathname === '/admin/bookings') {
      return handleStaffBookings(res, req.url);
    }

    // C. Bookings POST (Create manual)
    if ((pathname === '/staff/bookings' || pathname === '/admin/bookings') && method === 'POST') {
      let body = '';
      req.on('data', chunk => body += chunk);
      req.on('end', () => {
        const currentDb = getDb();
        const u = new URLSearchParams(body);
        const ref = 'NFA-2026-' + Math.floor(1000 + Math.random() * 9000);
        currentDb.bookings.unshift({
          id: Date.now(),
          booking_number: ref,
          booking_reference: ref,
          customer_name: u.get('customer_name') || 'Customer',
          customer_email: u.get('customer_email') || '',
          customer_phone: u.get('customer_phone') || '',
          service_name: u.get('service_name') || 'Diagnostic Inspection',
          preferred_date: u.get('preferred_date') || new Date().toISOString().split('T')[0],
          preferred_time: u.get('preferred_time') || '10:00 AM',
          vehicle_info: u.get('vehicle_info') || 'Vehicle',
          notes: '',
          internal_notes: u.get('internal_notes') || '',
          status: u.get('status') || 'confirmed',
          created_at: new Date().toISOString()
        });
        saveDb(currentDb);
        res.writeHead(302, { 'Location': '/staff/bookings?created=1' });
        res.end();
      });
      return;
    }

    // D. Bookings Edit POST
    const editBookingMatch = pathname.match(/^\/(staff|admin)\/bookings\/(\d+)\/edit$/);
    if (editBookingMatch && method === 'POST') {
      const bId = parseInt(editBookingMatch[2], 10);
      let body = '';
      req.on('data', chunk => body += chunk);
      req.on('end', () => {
        const currentDb = getDb();
        const b = currentDb.bookings.find(item => item.id === bId);
        if (b) {
          const u = new URLSearchParams(body);
          b.customer_name = u.get('customer_name') || b.customer_name;
          b.customer_phone = u.get('customer_phone') || b.customer_phone;
          b.customer_email = u.get('customer_email') || b.customer_email;
          b.service_name = u.get('service_name') || b.service_name;
          b.vehicle_info = u.get('vehicle_info') || b.vehicle_info;
          b.preferred_date = u.get('preferred_date') || b.preferred_date;
          b.status = u.get('status') || b.status;
          b.internal_notes = u.get('internal_notes') !== null ? u.get('internal_notes') : b.internal_notes;
          saveDb(currentDb);
        }
        res.writeHead(302, { 'Location': '/staff/bookings?updated=1' });
        res.end();
      });
      return;
    }

    // E. Bookings Delete POST
    const deleteBookingMatch = pathname.match(/^\/(staff|admin)\/bookings\/(\d+)\/delete$/);
    if (deleteBookingMatch && method === 'POST') {
      const bId = parseInt(deleteBookingMatch[2], 10);
      const currentDb = getDb();
      currentDb.bookings = currentDb.bookings.filter(b => b.id !== bId);
      saveDb(currentDb);
      res.writeHead(302, { 'Location': '/staff/bookings?deleted=1' });
      return res.end();
    }

    // F. Services GET
    if (pathname === '/staff/services' || pathname === '/admin/services') {
      return handleStaffServices(res, req.url);
    }

    // G. Services POST (Create)
    if ((pathname === '/staff/services' || pathname === '/admin/services') && method === 'POST') {
      let body = '';
      req.on('data', chunk => body += chunk);
      req.on('end', () => {
        const currentDb = getDb();
        const u = new URLSearchParams(body);
        const newSrv = {
          id: Date.now(),
          name: u.get('name') || 'New Service',
          category: u.get('category') || 'General',
          description: u.get('description') || '',
          price: u.get('price') || 'Custom Quote',
          duration_minutes: 60,
          is_active: true,
          is_featured: false,
          display_order: currentDb.services.length + 1
        };
        currentDb.services.push(newSrv);
        saveDb(currentDb);
        res.writeHead(302, { 'Location': '/staff/services?created=1' });
        res.end();
      });
      return;
    }

    // H. Services Toggle Active POST
    const toggleServiceMatch = pathname.match(/^\/(staff|admin)\/services\/(\d+)\/toggle$/);
    if (toggleServiceMatch && method === 'POST') {
      const sId = parseInt(toggleServiceMatch[2], 10);
      const currentDb = getDb();
      const s = currentDb.services.find(item => item.id === sId);
      if (s) {
        s.is_active = !s.is_active;
        saveDb(currentDb);
      }
      res.writeHead(302, { 'Location': '/staff/services?toggled=1' });
      return res.end();
    }

    // I. Services Edit POST
    const editServiceMatch = pathname.match(/^\/(staff|admin)\/services\/(\d+)\/edit$/);
    if (editServiceMatch && method === 'POST') {
      const sId = parseInt(editServiceMatch[2], 10);
      let body = '';
      req.on('data', chunk => body += chunk);
      req.on('end', () => {
        const currentDb = getDb();
        const s = currentDb.services.find(item => item.id === sId);
        if (s) {
          const u = new URLSearchParams(body);
          s.name = u.get('name') || s.name;
          s.category = u.get('category') || s.category;
          s.description = u.get('description') || s.description;
          s.price = u.get('price') || s.price;
          saveDb(currentDb);
        }
        res.writeHead(302, { 'Location': '/staff/services?updated=1' });
        res.end();
      });
      return;
    }

    // J. Services Delete POST
    const deleteServiceMatch = pathname.match(/^\/(staff|admin)\/services\/(\d+)\/delete$/);
    if (deleteServiceMatch && method === 'POST') {
      const sId = parseInt(deleteServiceMatch[2], 10);
      const currentDb = getDb();
      currentDb.services = currentDb.services.filter(s => s.id !== sId);
      saveDb(currentDb);
      res.writeHead(302, { 'Location': '/staff/services?deleted=1' });
      return res.end();
    }

    // K. Settings GET & POST
    if (pathname === '/staff/settings' || pathname === '/admin/settings') {
      if (method === 'GET') {
        return handleStaffSettings(res);
      }
      if (method === 'POST') {
        let body = '';
        req.on('data', chunk => body += chunk);
        req.on('end', () => {
          const currentDb = getDb();
          const u = new URLSearchParams(body);
          currentDb.settings = {
            ...currentDb.settings,
            shop_name: u.get('shop_name') || currentDb.settings.shop_name,
            shop_phone: u.get('shop_phone') || currentDb.settings.shop_phone,
            shop_email: u.get('shop_email') || currentDb.settings.shop_email,
            shop_address: u.get('shop_address') || currentDb.settings.shop_address
          };
          saveDb(currentDb);
          res.writeHead(302, { 'Location': '/staff/settings?saved=1' });
          res.end();
        });
        return;
      }
    }
  }

  // 404 Catch-All
  res.writeHead(404, { 'Content-Type': 'text/html; charset=utf-8' });
  res.end(renderLayout('Page Not Found - 404', 'Page not found', `
    <div class="section-pad py-32 text-center min-h-[60vh] flex flex-col justify-center items-center">
        <div class="frosted-card p-10 sm:p-14 max-w-lg mx-auto border border-[#BDEBFA] rounded-3xl shadow-xl bg-white/95">
            <h1 class="font-heading font-800 text-7xl text-[#087DB5] mb-2 tracking-tight">404</h1>
            <h2 class="font-heading font-800 text-2xl text-[#071F35] mb-3">Page Not Found</h2>
            <p class="text-[#071F35]/70 max-w-md mx-auto mb-8 text-sm">The page you requested does not exist or has been moved.</p>
            <a href="/" class="btn-navy-pill text-sm inline-block">Return Home</a>
        </div>
    </div>
  `));
});

server.listen(PORT, () => {
  console.log(`North Fork Auto Application Server running at http://localhost:${PORT}`);
});
