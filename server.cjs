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

// Chatbot in-memory session and rate-limiting store
const chatSessions = new Map(); // sessionId => { messages: [], lastReq: timestamp, reqCount: number }

function checkChatRateLimit(sessionId, clientIp) {
  const now = Date.now();
  const key = sessionId || clientIp;
  let record = chatSessions.get(key);
  if (!record) {
    record = { messages: [], lastReq: now, reqCount: 1 };
    chatSessions.set(key, record);
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
const NFA_SYSTEM_PROMPT = `You are the North Fork Auto Assistant, an experienced, friendly automotive service advisor at North Fork Auto in Talkeetna, Alaska (25460 Talkeetna Spur Rd).
Website: https://www.northforkauto.com/ | Phone: (907) 733-3030 | After-Hours: +1 (907) 232-3859
Hours: Monday–Friday, 8:00 AM – 5:00 PM | Saturday & Sunday: Closed | Walk-ins welcome during business hours.

SERVICES: Diagnostic Inspection (OBD-II, check engine), Brake Repair & Replacement, Oil & Filter Service (synthetic & conventional, Subaru/Ford approved), Engine Repair & Overhaul, Transmission Service, Steering & Suspension, Electrical & Battery, Cooling & AC Repair.
HIGHLIGHTS: ASE Certified Technicians, 12-Month / 12,000-Mile Warranty, Locally owned, 10+ years experience.

STRICT RESPONSE LENGTH POLICY (CRITICAL):
- Your answers MUST be 1–3 short sentences (approximately 25–60 words maximum).
- For simple questions, answer in ONE sentence whenever possible.
- Never write robotic greetings, apologies, or lengthy introductions ("I'm sorry to hear that", "I can definitely help with the next steps").
- Never repeat or rephrase the customer's question.
- Do NOT use markdown headers (###), bullet points, or numbered lists unless explicitly asked.
- Ask NO MORE than TWO relevant questions in a single response.
- Do not repeat phone numbers in every answer—only provide them when directly asked or for urgent safety emergencies.
- CONVERSATION MEMORY: Remember vehicle details already provided (year, make, model, problem). NEVER ask for information the customer has already supplied.

AUTOMOTIVE SAFETY:
- Never present an AI guess as a confirmed diagnosis.
- For dangerous issues (flashing check engine light, brake failure, severe overheating, unsafe steering, serious fluid leaks): concisely advise the customer to stop safely, avoid driving, and call (907) 733-3030.

EXAMPLES OF EXACT TONE AND LENGTH:
Customer: My truck's check engine light just came on. Can you help?
Assistant: Absolutely! Is the check engine light flashing or staying solid? Also, what year, make and model is your truck?

Customer: It's flashing.
Assistant: If it's flashing, safely pull over and avoid driving it. Please call us at (907) 733-3030 for assistance.

Customer: It's solid and driving normally.
Assistant: Thanks! We can help arrange a diagnostic inspection. Have you noticed any unusual noises or loss of power?

Customer: How much is an oil change?
Assistant: Pricing depends on your vehicle and oil type. What's your truck's year, make and model?

Customer: Are you open Saturday?
Assistant: We're closed Saturdays, but we're open Monday–Friday, 8 AM–5 PM.

Customer: Where are you located?
Assistant: We're in Talkeetna, Alaska. You can call us at (907) 733-3030 if you need directions.

Customer: My car is making a squealing noise when I brake.
Assistant: Squealing usually points to worn brake pads reaching their wear indicator. What year, make and model do you drive?`;

async function callCloudflareWorkersAI(historyMessages) {
  const accountId = process.env.CLOUDFLARE_ACCOUNT_ID || '';
  let token = (process.env.CLOUDFLARE_API_TOKEN || '').trim();
  token = token.replace(/^(Authorization:\s*Bearer\s*|Bearer\s*)/i, '').replace(/^["']|["']$/g, '');
  const model = process.env.CLOUDFLARE_AI_MODEL || '@cf/google/gemma-4-26b-a4b-it';

  const endpoint = `https://api.cloudflare.com/client/v4/accounts/${accountId}/ai/run/${model}`;

  const payloadMessages = [
    { role: 'system', content: NFA_SYSTEM_PROMPT }
  ];

  const recent = historyMessages.slice(-8);
  for (const m of recent) {
    payloadMessages.push({
      role: m.role === 'user' ? 'user' : 'assistant',
      content: String(m.content).slice(0, 1000)
    });
  }

  const controller = new AbortController();
  const timeoutId = setTimeout(() => controller.abort(), 45000);

  try {
    const res = await fetch(endpoint, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ messages: payloadMessages }),
      signal: controller.signal
    });
    clearTimeout(timeoutId);

    if (res.ok) {
      const data = await res.json();
      const content = data?.result?.choices?.[0]?.message?.content || data?.result?.response;
      if (content) {
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
    console.error('Cloudflare AI error response:', res.status, errText);

    return {
      success: false,
      reply: "I apologize, but I am momentarily having trouble connecting. Please feel free to call our shop directly at (907) 733-3030 or visit us on Talkeetna Spur Road."
    };
  } catch (err) {
    clearTimeout(timeoutId);
    console.error('Cloudflare AI Exception:', err);
    return {
      success: false,
      reply: "I apologize, but our AI service is momentarily offline. For urgent questions or appointment scheduling, please call our service desk at (907) 733-3030."
    };
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

  // Seed services from live_Service.json
  if (fs.existsSync(LIVE_SERVICES_FILE)) {
    try {
      const liveServices = JSON.parse(fs.readFileSync(LIVE_SERVICES_FILE, 'utf8').replace(/^\uFEFF/, ''));
      db.services = liveServices.map((s, idx) => {
        let price = 'Custom Quote';
        if (s.name.includes('Oil')) price = '$89.99';
        else if (s.name.includes('Brake')) price = '$189.99';
        else if (s.name.includes('Diagnostic')) price = '$120.00';
        else if (s.name.includes('Transmission')) price = '$189.99';
        else if (s.name.includes('Battery') || s.name.includes('Electrical')) price = '$135.00';
        else if (s.name.includes('Fluid') || s.name.includes('Cool')) price = '$119.99';
        else if (s.name.includes('Filter')) price = '$49.99';
        else if (s.name.includes('Alignment') || s.name.includes('Suspension')) price = '$149.99';
        else if (s.name.includes('Inspection')) price = '$99.99';

        return {
          id: idx + 1,
          live_id: s.id,
          name: s.name,
          category: s.category || 'General Repair',
          description: s.description || 'Professional automotive repair and service in Talkeetna.',
          symptoms: s.symptoms || [],
          price: price,
          duration_minutes: 60,
          is_active: true,
          is_featured: !!s.featured,
          display_order: idx + 1
        };
      });
    } catch (e) {
      console.error('Error loading live_Service.json', e);
    }
  }

  if (!db.services || db.services.length === 0) {
    db.services = [
      { id: 1, name: 'Diagnostic Inspection', category: 'Diagnostics', description: 'Comprehensive computerized scan and physical multi-point inspection.', price: '$120.00', duration_minutes: 60, is_active: true, is_featured: true, display_order: 1 },
      { id: 2, name: 'Synthetic Oil Change', category: 'Maintenance', description: 'Full synthetic oil, OEM filter replacement, and multi-point inspection.', price: '$89.99', duration_minutes: 45, is_active: true, is_featured: true, display_order: 2 },
      { id: 3, name: 'Brake Pad & Rotor Service', category: 'Brakes', description: 'Front or rear pads, rotors, caliper check, and road test.', price: '$189.99', duration_minutes: 90, is_active: true, is_featured: true, display_order: 3 },
      { id: 4, name: 'Transmission Service', category: 'Drivetrain', description: 'Fluid exchange, filter inspection, and electronic diagnostics.', price: '$189.99', duration_minutes: 90, is_active: true, is_featured: true, display_order: 4 },
      { id: 5, name: 'Steering & Suspension', category: 'Suspension', description: 'Struts, tie-rods, control arms, bushings, and laser alignment.', price: 'Custom Quote', duration_minutes: 120, is_active: true, is_featured: true, display_order: 5 },
      { id: 6, name: 'Electrical & Battery', category: 'Electrical', description: 'Alternator, starter, battery diagnostic, and wire repair.', price: '$135.00', duration_minutes: 60, is_active: true, is_featured: true, display_order: 6 },
      { id: 7, name: 'Cooling & Heating / AC', category: 'Climate', description: 'Coolant flush, radiator check, thermostat, and AC service.', price: '$160.00', duration_minutes: 90, is_active: true, is_featured: true, display_order: 7 },
      { id: 8, name: 'Engine Repair & Timing Belt', category: 'Engine', description: 'Full engine diagnostics, timing belts, head gaskets, and rebuilds.', price: 'Custom Quote', duration_minutes: 240, is_active: true, is_featured: true, display_order: 8 }
    ];
  }

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

// Global Public Layout
function renderLayout(title, description, content, activeNav = '') {
  return `<!DOCTYPE html>
<html lang="en" class="dark scroll-smooth">
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Sora:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        background: 'hsl(240 6% 6%)',
                        foreground: 'hsl(220 20% 98%)',
                        card: {
                            DEFAULT: 'hsl(240 5% 9%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        popover: {
                            DEFAULT: 'hsl(240 6% 8%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        primary: {
                            DEFAULT: 'hsl(349 77% 50%)',
                            foreground: 'hsl(0 0% 100%)'
                        },
                        secondary: {
                            DEFAULT: 'hsl(240 5% 13%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        muted: {
                            DEFAULT: 'hsl(240 5% 13%)',
                            foreground: 'hsl(217 13% 65%)'
                        },
                        accent: {
                            DEFAULT: 'hsl(240 5% 16%)',
                            foreground: 'hsl(220 20% 98%)'
                        },
                        border: 'hsl(215 19% 22%)',
                        input: 'hsl(215 19% 22%)',
                        ring: 'hsl(349 77% 50%)'
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
        }
        body {
            font-family: var(--font-body);
            background-color: hsl(240 6% 6%);
            color: hsl(220 20% 98%);
            overflow-x: hidden;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: var(--font-heading);
            font-weight: 800;
        }
        .font-mono-data {
            font-family: var(--font-mono);
            font-size: 0.75rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: hsl(349 77% 50%);
        }
        .btn-engine {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            font-family: var(--font-heading);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            border-radius: 0px;
        }
        .btn-engine:hover {
            transform: translateY(-1px);
        }
        .btn-engine:active {
            transform: translateY(0);
        }
        .section-pad {
            padding-left: 1.5rem;
            padding-right: 1.5rem;
            max-width: 80rem;
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
                padding-left: 2.5rem;
                padding-right: 2.5rem;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">
    <!-- Navbar -->
    <header class="fixed top-0 left-0 right-0 z-50 transition-all duration-300 bg-background/90 backdrop-blur-md border-b border-border/80">
        <div class="section-pad">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <a href="/" class="flex items-center gap-3 group">
                    <img src="/images/logo.png" alt="North Fork Automotive - Talkeetna, AK" class="h-11 sm:h-12 w-auto max-w-[160px] sm:max-w-[200px] object-contain rounded-md border border-border/40 group-hover:border-primary transition-all duration-200 shadow-sm">
                    <div class="hidden sm:flex flex-col">
                        <span class="font-heading font-800 text-lg tracking-tight text-foreground leading-none">
                            North Fork Auto
                        </span>
                        <span class="font-mono-data text-[10px] mt-1 text-muted-foreground">
                            Talkeetna, AK
                        </span>
                    </div>
                </a>

                <!-- Desktop Navigation -->
                <nav class="hidden md:flex items-center gap-8">
                    <a href="/" class="text-sm font-medium tracking-wide hover:text-primary transition-colors ${activeNav === 'home' ? 'text-primary font-semibold' : 'text-foreground/90'}">Home</a>
                    <a href="/services" class="text-sm font-medium tracking-wide hover:text-primary transition-colors ${activeNav === 'services' ? 'text-primary font-semibold' : 'text-foreground/90'}">Services</a>
                    <a href="/about" class="text-sm font-medium tracking-wide hover:text-primary transition-colors ${activeNav === 'about' ? 'text-primary font-semibold' : 'text-foreground/90'}">About</a>
                    <a href="/contact" class="text-sm font-medium tracking-wide hover:text-primary transition-colors ${activeNav === 'contact' ? 'text-primary font-semibold' : 'text-foreground/90'}">Visit Us</a>
                </nav>

                <!-- Action Button -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="tel:+19077333030" class="flex items-center gap-2 text-xs font-mono-data text-muted-foreground hover:text-foreground">
                        <svg class="w-3.5 h-3.5 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        (907) 733-3030
                    </a>
                    <a href="/book" class="btn-engine bg-primary text-primary-foreground px-5 py-2.5 text-xs shadow-lg shadow-primary/20">
                        Book Appointment
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <div class="flex md:hidden items-center gap-3">
                    <a href="/book" class="btn-engine bg-primary text-primary-foreground px-3 py-1.5 text-xs">
                        Book
                    </a>
                    <button id="mobile-toggle" aria-label="Toggle Navigation Menu" class="p-2 text-foreground hover:text-primary">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16m-7 6h7"/></svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Menu Dropdown -->
        <div id="mobile-menu" class="hidden md:hidden border-b border-border bg-card/95 backdrop-blur-md px-6 py-6 space-y-4">
            <a href="/" class="block text-sm font-medium tracking-wide hover:text-primary py-1">Home</a>
            <a href="/services" class="block text-sm font-medium tracking-wide hover:text-primary py-1">Services</a>
            <a href="/about" class="block text-sm font-medium tracking-wide hover:text-primary py-1">About</a>
            <a href="/contact" class="block text-sm font-medium tracking-wide hover:text-primary py-1">Visit Us</a>
            <a href="/staff/login" class="block text-sm font-medium tracking-wide text-muted-foreground hover:text-primary py-1">Staff Login</a>
            <div class="pt-4 border-t border-border/60">
                <a href="/book" class="block btn-engine bg-primary text-primary-foreground py-3 text-center text-xs">Book Appointment</a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow pt-20">
        ${content}
    </main>

    <!-- Footer -->
    <footer class="border-t border-border bg-card/40 mt-auto">
        <div class="section-pad py-16">
            <div class="grid gap-12 lg:grid-cols-4">
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-5">
                        <img src="/images/logo.png" alt="North Fork Automotive - Talkeetna, AK" class="h-14 sm:h-16 w-auto max-w-[200px] object-contain rounded-md border border-border/40 shadow-sm">
                    </div>
                    <p class="text-sm text-muted-foreground leading-relaxed mb-5">
                        Trusted auto repair and maintenance in Talkeetna, Alaska. Honest service, quality workmanship, and dependable care for your vehicle.
                    </p>
                    <div class="flex gap-3">
                        <a href="https://facebook.com" target="_blank" rel="noreferrer" aria-label="Facebook" class="flex items-center justify-center w-9 h-9 border border-border hover:border-primary hover:text-primary transition-colors text-foreground">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"/></svg>
                        </a>
                        <a href="https://instagram.com" target="_blank" rel="noreferrer" aria-label="Instagram" class="flex items-center justify-center w-9 h-9 border border-border hover:border-primary hover:text-primary transition-colors text-foreground">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="20" x="2" y="2" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"/><line x1="17.5" x2="17.51" y1="6.5" y2="6.5"/></svg>
                        </a>
                    </div>
                </div>

                <div>
                    <h3 class="font-mono-data mb-4">Contact</h3>
                    <ul class="space-y-3 text-sm">
                        <li>
                            <a href="https://maps.google.com/?q=Talkeetna+Spur+Road,+Talkeetna,+AK+99676" target="_blank" rel="noreferrer" class="flex gap-3 hover:text-primary transition-colors text-muted-foreground hover:text-foreground">
                                <svg class="w-4 h-4 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                                <span>Talkeetna Spur Road, Talkeetna, AK 99676</span>
                            </a>
                        </li>
                        <li>
                            <a href="tel:+19077333030" class="flex gap-3 hover:text-primary transition-colors text-muted-foreground hover:text-foreground">
                                <svg class="w-4 h-4 text-primary shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                                <span>(907) 733-3030</span>
                            </a>
                        </li>
                        <li>
                            <a href="mailto:info@northforkauto.com" class="flex gap-3 hover:text-primary transition-colors text-muted-foreground hover:text-foreground">
                                <svg class="w-4 h-4 text-primary shrink-0" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                                <span>info@northforkauto.com</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-mono-data mb-4">Hours</h3>
                    <ul class="space-y-1">
                        <li class="flex justify-between gap-4 text-sm py-1.5 border-b border-border/40">
                            <span class="text-muted-foreground">Monday – Friday</span>
                            <span class="font-mono-data text-foreground/80">8:00 AM – 5:00 PM</span>
                        </li>
                        <li class="flex justify-between gap-4 text-sm py-1.5 border-b border-border/40">
                            <span class="text-muted-foreground">Saturday</span>
                            <span class="font-mono-data text-foreground/80">By Appointment</span>
                        </li>
                        <li class="flex justify-between gap-4 text-sm py-1.5 border-b border-border/40">
                            <span class="text-muted-foreground">Sunday</span>
                            <span class="font-mono-data text-foreground/80">Closed</span>
                        </li>
                    </ul>
                </div>

                <div>
                    <h3 class="font-mono-data mb-4">Quick Links</h3>
                    <ul class="space-y-3 text-sm">
                        <li><a href="/" class="hover:text-primary transition-colors text-muted-foreground hover:text-foreground">Home</a></li>
                        <li><a href="/services" class="hover:text-primary transition-colors text-muted-foreground hover:text-foreground">Services</a></li>
                        <li><a href="/about" class="hover:text-primary transition-colors text-muted-foreground hover:text-foreground">About</a></li>
                        <li><a href="/contact" class="hover:text-primary transition-colors text-muted-foreground hover:text-foreground">Visit Us</a></li>
                        <li><a href="/book" class="hover:text-primary transition-colors text-muted-foreground hover:text-foreground">Book Appointment</a></li>
                        <li><a href="/staff/login" class="text-muted-foreground/60 hover:text-primary transition-colors">Staff Login</a></li>
                    </ul>
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-border flex flex-col sm:flex-row justify-between gap-3 text-xs text-muted-foreground">
                <p>© ${new Date().getFullYear()} North Fork Auto. All rights reserved.</p>
                <p class="font-mono-data">Talkeetna, Alaska · Est. locally owned</p>
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
    </script>
    
    <!-- North Fork Auto AI Auto Service Assistant Chatbot -->
    ${getChatbotHtml()}
</body>
</html>`;
}

// -------------------------------------------------------------
// PUBLIC PAGE HANDLERS
// -------------------------------------------------------------

function handleHome() {
  const currentDb = getDb();
  const services = currentDb.services.filter(s => s.is_active);
  const featured = services.filter(s => s.is_featured).slice(0, 8);

  const heroHtml = `
<section class="relative min-h-[100svh] flex flex-col justify-between">
    <img src="https://media.base44.com/images/public/6a97793b1eaf1697782a6c01/9d7382181_generated_60d9ab56.jpg" alt="Luxury vehicle on a hydraulic lift inside the North Fork Auto workshop" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-background via-background/85 to-background/40"></div>
    <div class="absolute inset-0 bg-gradient-to-t from-background via-transparent to-background/60"></div>
    
    <div class="relative section-pad pt-36 pb-16 flex-1 flex flex-col justify-center">
        <div class="max-w-3xl">
            <div class="font-mono-data flex items-center gap-3 mb-6">
                <span class="h-px w-10 bg-primary"></span>
                <span>Talkeetna, Alaska · Auto Repair Experts</span>
            </div>
            
            <h1 class="font-heading font-800 text-4xl sm:text-6xl lg:text-7xl leading-[1.02] tracking-tight text-balance text-foreground">
                Trusted Auto Repair.<br>
                <span class="text-primary">Quality Service.</span><br>
                Every Time.
            </h1>
            
            <p class="mt-6 text-base sm:text-xl text-muted-foreground leading-relaxed max-w-2xl">
                From routine maintenance to complex diagnostics, North Fork Auto provides professional, honest automotive care for drivers across Talkeetna and the Mat-Su Valley.
            </p>
            
            <!-- Open/Closed Status Pill -->
            <div class="mt-8 border border-border bg-card/70 backdrop-blur px-6 py-4 max-w-2xl">
                <div class="flex flex-wrap items-center justify-between gap-3 text-sm">
                    <div class="flex items-center gap-2.5">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                        </span>
                        <span class="font-heading font-700 text-foreground">Currently Open</span>
                    </div>
                    <span class="font-mono-data text-foreground/80">Mon – Fri 8:00 AM – 5:00 PM</span>
                </div>
            </div>
            
            <!-- CTA Buttons -->
            <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-4">
                <a href="/book" class="btn-engine bg-primary text-primary-foreground px-8 py-4 text-sm">
                    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                    Book an Appointment
                </a>
                <a href="tel:+19077333030" class="btn-engine border border-foreground/30 bg-background/40 backdrop-blur hover:border-primary px-8 py-4 text-sm text-foreground">
                    <svg class="w-4 h-4 text-primary" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                    Call Now · (907) 733-3030
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Features Bar -->
<div class="border-b border-border bg-card/40">
    <div class="section-pad py-8">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="flex items-center gap-3">
                <span class="font-mono-data text-base">01</span>
                <div>
                    <h3 class="font-heading font-700 text-sm text-foreground">ASE Certified</h3>
                    <p class="text-xs text-muted-foreground">Skilled Master Techs</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-mono-data text-base">02</span>
                <div>
                    <h3 class="font-heading font-700 text-sm text-foreground">OEM Diagnostic</h3>
                    <p class="text-xs text-muted-foreground">Dealer-Level Equipment</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-mono-data text-base">03</span>
                <div>
                    <h3 class="font-heading font-700 text-sm text-foreground">Warranty Backed</h3>
                    <p class="text-xs text-muted-foreground">Parts & Labor Protected</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="font-mono-data text-base">04</span>
                <div>
                    <h3 class="font-heading font-700 text-sm text-foreground">Local Alaskan</h3>
                    <p class="text-xs text-muted-foreground">Community Focused</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Featured Services Section -->
<section class="section-pad py-24">
    <div class="flex flex-col md:flex-row md:items-end justify-between mb-16 gap-6">
        <div>
            <div class="font-mono-data flex items-center gap-2 mb-3">
                <span class="h-px w-6 bg-primary"></span>
                <span>Our Capabilities</span>
            </div>
            <h2 class="font-heading font-800 text-3xl sm:text-4xl lg:text-5xl tracking-tight text-foreground">
                Expert Auto Care For Alaskan Roads
            </h2>
        </div>
        <a href="/services" class="btn-engine border border-border px-6 py-3 text-xs text-foreground hover:border-primary shrink-0">
            View All Services (${services.length})
            <svg class="w-3.5 h-3.5 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </a>
    </div>

    <!-- Service Grid with 1px border separation -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-px bg-border border border-border">
        ${featured.map(s => `
            <div class="group relative bg-card p-8 flex flex-col justify-between hover:bg-card/80 transition-all duration-300">
                <div>
                    <div class="font-mono-data text-[10px] text-muted-foreground mb-4">${escapeHtml(s.category)}</div>
                    <h3 class="font-heading font-700 text-lg text-foreground group-hover:text-primary transition-colors">
                        ${escapeHtml(s.name)}
                    </h3>
                    <p class="mt-3 text-xs text-muted-foreground leading-relaxed">
                        ${escapeHtml(s.description)}
                    </p>
                </div>
                <div class="mt-8 pt-4 border-t border-border/50 flex items-center justify-between">
                    <span class="font-mono-data text-xs text-primary font-bold">${escapeHtml(s.price || 'Custom Quote')}</span>
                    <a href="/book?service=${encodeURIComponent(s.name)}" class="font-heading font-700 text-xs text-primary uppercase tracking-wider flex items-center gap-1 group-hover:underline">
                        Book
                        <svg class="w-3 h-3" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </a>
                </div>
            </div>
        `).join('')}
    </div>
</section>

<!-- CTA Section -->
<section class="border-t border-b border-border bg-card">
    <div class="section-pad py-20">
        <div class="max-w-4xl mx-auto text-center space-y-6">
            <div class="font-mono-data flex items-center justify-center gap-2">
                <span class="h-px w-8 bg-primary"></span>
                <span>Get Back On The Road</span>
                <span class="h-px w-8 bg-primary"></span>
            </div>
            <h2 class="font-heading font-800 text-3xl sm:text-5xl text-foreground">
                Ready For Reliable Automotive Service?
            </h2>
            <p class="text-muted-foreground text-sm sm:text-base max-w-xl mx-auto">
                Schedule your appointment online or call us directly. We provide transparent estimates and expert care.
            </p>
            <div class="pt-4 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="/book" class="btn-engine bg-primary text-primary-foreground px-8 py-4 text-sm">
                    Book Service Appointment
                </a>
                <a href="tel:+19077333030" class="btn-engine border border-border px-8 py-4 text-sm text-foreground hover:border-primary">
                    Call (907) 733-3030
                </a>
            </div>
        </div>
    </div>
</section>
`;

  return renderLayout('North Fork Auto | Trusted Auto Repair in Talkeetna, AK', 'Professional automotive repair and maintenance in Talkeetna, Alaska.', heroHtml, 'home');
}

function handleServices() {
  const currentDb = getDb();
  const services = currentDb.services.filter(s => s.is_active);

  const content = `
<div class="section-pad pt-32 pb-24">
    <div class="max-w-4xl mx-auto mb-16 text-center">
        <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>Complete Repair &amp; Maintenance</span>
            <span class="h-px w-8 bg-primary"></span>
        </div>
        <h1 class="font-heading font-800 text-4xl sm:text-5xl lg:text-6xl tracking-tight text-foreground">
            Our Automotive Services
        </h1>
        <p class="mt-5 text-muted-foreground text-base sm:text-lg max-w-2xl mx-auto">
            From routine fluid changes to complete engine diagnostics, our certified technicians keep your vehicle running smoothly in harsh Alaskan conditions.
        </p>
    </div>

    <!-- 1px grid of all services -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        ${services.map(s => `
            <div class="border border-border bg-card p-6 flex flex-col justify-between hover:border-primary/60 transition-all duration-200">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-mono-data text-[10px] text-muted-foreground">${escapeHtml(s.category)}</span>
                        <span class="font-mono-data text-xs text-primary font-bold">${escapeHtml(s.price || 'Custom Quote')}</span>
                    </div>
                    <h3 class="font-heading font-700 text-lg text-foreground mb-2">
                        ${escapeHtml(s.name)}
                    </h3>
                    <p class="text-xs text-muted-foreground leading-relaxed mb-4">
                        ${escapeHtml(s.description)}
                    </p>
                    ${s.symptoms && s.symptoms.length > 0 ? `
                        <div class="border-t border-border/40 pt-3">
                            <span class="text-[10px] font-mono-data text-muted-foreground uppercase">Common Signs:</span>
                            <ul class="mt-1.5 space-y-1">
                                ${s.symptoms.slice(0, 3).map(sym => `<li class="text-[11px] text-foreground/80 flex items-center gap-1.5"><span class="w-1 h-1 bg-primary rounded-full shrink-0"></span> ${escapeHtml(sym)}</li>`).join('')}
                            </ul>
                        </div>
                    ` : ''}
                </div>
                <div class="mt-6 pt-4 border-t border-border flex items-center justify-between">
                    <span class="text-[11px] text-muted-foreground font-mono-data">Duration: ~${s.duration_minutes || 60}m</span>
                    <a href="/book?service=${encodeURIComponent(s.name)}" class="btn-engine bg-primary text-primary-foreground px-4 py-2 text-xs">
                        Book Now
                    </a>
                </div>
            </div>
        `).join('')}
    </div>
</div>
`;

  return renderLayout('Our Services', 'Complete automotive repair and maintenance services in Talkeetna, Alaska.', content, 'services');
}

function handleAbout() {
  const content = `
<div class="section-pad pt-32 pb-24">
    <div class="max-w-4xl mx-auto mb-16 text-center">
        <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>About North Fork Auto</span>
            <span class="h-px w-8 bg-primary"></span>
        </div>
        <h1 class="font-heading font-800 text-4xl sm:text-5xl lg:text-6xl tracking-tight text-foreground">
            Locally Owned. Alaskan Tested.
        </h1>
        <p class="mt-5 text-muted-foreground text-base sm:text-lg max-w-2xl mx-auto">
            North Fork Auto is Talkeetna's premier independent auto care facility. We understand what it takes to keep vehicles running safely through Alaskan winters and rugged summer terrain.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-12 items-center mb-24">
        <div class="space-y-6">
            <h2 class="font-heading font-800 text-2xl sm:text-3xl text-foreground">Our Story &amp; Values</h2>
            <p class="text-sm text-muted-foreground leading-relaxed">
                Founded with a mission to deliver straightforward, trustworthy auto repairs to the Talkeetna community, North Fork Auto brings modern diagnostic technology and old-school honesty to every job.
            </p>
            <p class="text-sm text-muted-foreground leading-relaxed">
                We believe in total transparency. When you bring your vehicle in, we thoroughly inspect it, explain what needs immediate attention versus what can wait, and provide clear upfront estimates before any work begins.
            </p>
            <div class="grid grid-cols-2 gap-4 pt-4">
                <div class="border border-border p-4 bg-card">
                    <div class="font-heading font-800 text-2xl text-primary">100%</div>
                    <div class="text-xs text-muted-foreground mt-1">Locally Owned &amp; Operated</div>
                </div>
                <div class="border border-border p-4 bg-card">
                    <div class="font-heading font-800 text-2xl text-primary">ASE</div>
                    <div class="text-xs text-muted-foreground mt-1">Certified Technicians</div>
                </div>
            </div>
        </div>
        <div class="border border-border bg-card overflow-hidden">
            <img src="https://media.base44.com/images/public/6a97793b1eaf1697782a6c01/9d7382181_generated_60d9ab56.jpg" alt="North Fork Auto Workshop" class="w-full h-80 object-cover">
        </div>
    </div>
</div>
`;

  return renderLayout('About Us', 'Learn about North Fork Auto and our commitment to Talkeetna drivers.', content, 'about');
}

function handleContact() {
  const content = `
<div class="section-pad pt-32 pb-24">
    <div class="max-w-4xl mx-auto mb-16 text-center">
        <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>Visit Us In Talkeetna</span>
            <span class="h-px w-8 bg-primary"></span>
        </div>
        <h1 class="font-heading font-800 text-4xl sm:text-5xl lg:text-6xl tracking-tight text-foreground">
            Contact &amp; Location
        </h1>
        <p class="mt-5 text-muted-foreground text-base sm:text-lg max-w-2xl mx-auto">
            Conveniently located off the Talkeetna Spur Road. Drop by during shop hours or send us a message below.
        </p>
    </div>

    <div class="grid md:grid-cols-2 gap-12">
        <!-- Contact Information -->
        <div class="space-y-8">
            <div class="border border-border bg-card p-6 space-y-6">
                <h3 class="font-heading font-700 text-xl text-foreground">Shop Details</h3>
                <div class="space-y-4 text-sm">
                    <div class="flex gap-4 items-start">
                        <svg class="w-5 h-5 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 4.993-5.539 10.193-7.399 11.799a1 1 0 0 1-1.202 0C9.539 20.193 4 14.993 4 10a8 8 0 0 1 16 0"/><circle cx="12" cy="10" r="3"/></svg>
                        <div>
                            <strong class="text-foreground block">Address</strong>
                            <span class="text-muted-foreground">Talkeetna Spur Road, Talkeetna, AK 99676</span>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <svg class="w-5 h-5 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <div>
                            <strong class="text-foreground block">Direct Phone</strong>
                            <a href="tel:+19077333030" class="text-primary hover:underline">(907) 733-3030</a>
                        </div>
                    </div>
                    <div class="flex gap-4 items-start">
                        <svg class="w-5 h-5 text-primary shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/></svg>
                        <div>
                            <strong class="text-foreground block">Email Inquiries</strong>
                            <a href="mailto:info@northforkauto.com" class="text-primary hover:underline">info@northforkauto.com</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contact Form -->
        <div class="border border-border bg-card p-6 sm:p-8">
            <h3 class="font-heading font-700 text-xl text-foreground mb-4">Send a Message</h3>
            <form id="contact-form" action="/contact" method="POST" class="space-y-4">
                <div>
                    <label class="block font-heading font-600 text-xs uppercase text-foreground mb-1">Your Name *</label>
                    <input type="text" name="name" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block font-heading font-600 text-xs uppercase text-foreground mb-1">Email Address *</label>
                    <input type="email" name="email" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block font-heading font-600 text-xs uppercase text-foreground mb-1">Phone Number</label>
                    <input type="tel" name="phone" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block font-heading font-600 text-xs uppercase text-foreground mb-1">Subject</label>
                    <input type="text" name="subject" class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary">
                </div>
                <div>
                    <label class="block font-heading font-600 text-xs uppercase text-foreground mb-1">Message *</label>
                    <textarea name="message" rows="4" required class="w-full bg-background border border-border px-4 py-2.5 text-sm text-foreground focus:outline-none focus:border-primary"></textarea>
                </div>
                <button type="submit" class="w-full btn-engine bg-primary text-primary-foreground py-3 text-xs">
                    Send Inquiry
                </button>
            </form>
        </div>
    </div>
</div>
`;

  return renderLayout('Visit Us / Contact', 'Contact North Fork Auto in Talkeetna, Alaska.', content, 'contact');
}

// -------------------------------------------------------------
// FIXED & DYNAMIC BOOKING WIZARD HANDLER (ZERO BLADE LEAK)
// -------------------------------------------------------------

function handleBook() {
  const currentDb = getDb();
  const services = currentDb.services.filter(s => s.is_active);

  const serviceCardsHtml = services.map((s, idx) => `
    <div class="service-select-card cursor-pointer border p-4 transition-all duration-200 ${idx === 0 ? 'border-primary bg-primary/10' : 'border-border bg-card hover:border-primary/50'}" data-service-name="${escapeHtml(s.name)}">
        <div class="flex items-start justify-between gap-2">
            <h4 class="font-heading font-700 text-sm text-foreground">${escapeHtml(s.name)}</h4>
            <span class="font-mono-data text-xs text-primary shrink-0">${escapeHtml(s.price || 'Custom Quote')}</span>
        </div>
        <p class="mt-1 text-xs text-muted-foreground">${escapeHtml(s.description)}</p>
    </div>
  `).join('');

  const defaultServiceName = services.length > 0 ? services[0].name : 'Diagnostic Inspection';

  const content = `
<div class="section-pad pt-32 pb-24 min-h-screen">
    <!-- Header -->
    <div class="max-w-4xl mx-auto mb-12 text-center">
        <div class="font-mono-data flex items-center justify-center gap-2 mb-4">
            <span class="h-px w-8 bg-primary"></span>
            <span>Book an Appointment</span>
            <span class="h-px w-8 bg-primary"></span>
        </div>
        <h1 class="font-heading font-800 text-4xl sm:text-5xl lg:text-6xl tracking-tight text-foreground">
            Schedule Your Service
        </h1>
        <p class="mt-5 text-muted-foreground text-base sm:text-lg max-w-2xl mx-auto">
            Book in minutes. Choose your service, pick an available time, and we'll take care of the rest. No account needed.
        </p>
    </div>

    <!-- Booking Wizard Container -->
    <div class="max-w-4xl mx-auto border border-border bg-card p-6 sm:p-10">
        <!-- Step Progress Bar -->
        <div class="mb-10">
            <div class="grid grid-cols-4 gap-2">
                <div id="step-tab-1" class="h-1.5 bg-primary rounded-sm transition-all duration-300"></div>
                <div id="step-tab-2" class="h-1.5 bg-border rounded-sm transition-all duration-300"></div>
                <div id="step-tab-3" class="h-1.5 bg-border rounded-sm transition-all duration-300"></div>
                <div id="step-tab-4" class="h-1.5 bg-border rounded-sm transition-all duration-300"></div>
            </div>
            <div class="mt-3 flex justify-between text-[11px] font-mono-data text-muted-foreground">
                <span id="step-label-1" class="text-primary font-bold">1. Contact</span>
                <span id="step-label-2">2. Vehicle</span>
                <span id="step-label-3">3. Service</span>
                <span id="step-label-4">4. Date &amp; Time</span>
            </div>
        </div>

        <div id="booking-feedback" class="hidden mb-6 p-4 border text-sm"></div>

        <form id="booking-wizard-form" action="/book" method="POST">
            <!-- STEP 1: Contact Information -->
            <div id="step-panel-1" class="space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Customer Information</h3>
                    <p class="text-xs text-muted-foreground mt-1">Please provide your contact details so we can confirm your booking.</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="cust-name" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Full Name <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="cust-name" name="name" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. Sarah Connor">
                    </div>
                    <div>
                        <label for="cust-email" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Email Address <span class="text-primary">*</span>
                        </label>
                        <input type="email" id="cust-email" name="email" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="sarah@example.com">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="cust-phone" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Phone Number <span class="text-primary">*</span>
                        </label>
                        <input type="tel" id="cust-phone" name="phone" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="(907) 555-0199">
                    </div>
                    <div>
                        <label for="cust-notes" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Special Requests / Symptoms
                        </label>
                        <input type="text" id="cust-notes" name="notes" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. Squeaking noise when braking...">
                    </div>
                </div>

                <div class="pt-6 flex justify-end">
                    <button type="button" id="btn-to-step-2" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Continue to Vehicle
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 2: Vehicle Details -->
            <div id="step-panel-2" class="hidden space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Vehicle Details</h3>
                    <p class="text-xs text-muted-foreground mt-1">Tell us about the vehicle you're bringing in.</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-6">
                    <div>
                        <label for="veh-year" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Year <span class="text-primary">*</span>
                        </label>
                        <input type="number" id="veh-year" name="vehicle_year" min="1950" max="2027" required value="2020" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label for="veh-make" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Make <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="veh-make" name="vehicle_make" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. Toyota, Subaru, Ford">
                    </div>
                    <div>
                        <label for="veh-model" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Model <span class="text-primary">*</span>
                        </label>
                        <input type="text" id="veh-model" name="vehicle_model" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. 4Runner, Outback, F-150">
                    </div>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="veh-mileage" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Approximate Mileage
                        </label>
                        <input type="text" id="veh-mileage" name="vehicle_mileage" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="e.g. 85,000">
                    </div>
                    <div>
                        <label for="veh-vin" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            VIN (Optional)
                        </label>
                        <input type="text" id="veh-vin" name="vehicle_vin" class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary placeholder-muted-foreground/60" placeholder="17-character VIN">
                    </div>
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" id="btn-back-to-step-1" class="btn-engine border border-border px-6 py-3 text-sm text-foreground hover:border-primary">
                        Back
                    </button>
                    <button type="button" id="btn-to-step-3" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Continue to Service
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 3: Select Service (Cleanly rendered with dynamic DB cards) -->
            <div id="step-panel-3" class="hidden space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Select Requested Service</h3>
                    <p class="text-xs text-muted-foreground mt-1">Pick the primary service you need performed.</p>
                </div>

                <input type="hidden" id="selected-service-input" name="service" value="${escapeHtml(defaultServiceName)}">

                <div class="grid sm:grid-cols-2 gap-4 max-h-[460px] overflow-y-auto pr-1">
                    ${serviceCardsHtml}
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" id="btn-back-to-step-2" class="btn-engine border border-border px-6 py-3 text-sm text-foreground hover:border-primary">
                        Back
                    </button>
                    <button type="button" id="btn-to-step-4" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Continue to Date &amp; Time
                        <svg class="w-4 h-4 ml-1" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                    </button>
                </div>
            </div>

            <!-- STEP 4: Date & Time Picker & Submit -->
            <div id="step-panel-4" class="hidden space-y-6">
                <div class="border-b border-border pb-4 mb-6">
                    <h3 class="font-heading font-700 text-xl text-foreground">Select Appointment Date &amp; Time</h3>
                    <p class="text-xs text-muted-foreground mt-1">Select your preferred drop-off slot (Mon - Fri, 8:00 AM – 5:00 PM).</p>
                </div>

                <div class="grid sm:grid-cols-2 gap-6">
                    <div>
                        <label for="app-date" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Preferred Date <span class="text-primary">*</span>
                        </label>
                        <input type="date" id="app-date" name="preferred_date" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary">
                    </div>
                    <div>
                        <label for="app-time" class="block font-heading font-600 text-xs uppercase tracking-wide text-foreground mb-2">
                            Preferred Time Slot <span class="text-primary">*</span>
                        </label>
                        <select id="app-time" name="preferred_time" required class="w-full bg-background border border-border px-4 py-3 text-sm text-foreground focus:outline-none focus:border-primary">
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
                <div class="border border-border bg-background p-5 text-sm space-y-2">
                    <div class="font-mono-data text-xs text-primary mb-2">Appointment Review</div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Customer:</span><span id="review-cust" class="font-heading font-600 text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Vehicle:</span><span id="review-veh" class="font-heading font-600 text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Service:</span><span id="review-srv" class="font-heading font-600 text-foreground">${escapeHtml(defaultServiceName)}</span></div>
                </div>

                <div class="pt-6 flex justify-between">
                    <button type="button" id="btn-back-to-step-3" class="btn-engine border border-border px-6 py-3 text-sm text-foreground hover:border-primary">
                        Back
                    </button>
                    <button type="submit" id="btn-submit-booking" class="btn-engine bg-primary text-primary-foreground px-8 py-3 text-sm">
                        Confirm &amp; Book Appointment
                    </button>
                </div>
            </div>

            <!-- STEP 5: Success Confirmation Screen -->
            <div id="step-panel-5" class="hidden text-center py-8 space-y-6">
                <div class="w-16 h-16 rounded-full border-2 border-emerald-500 bg-emerald-500/10 text-emerald-400 flex items-center justify-center mx-auto">
                    <svg class="w-8 h-8" xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
                <h3 class="font-heading font-800 text-3xl text-foreground">Appointment Requested!</h3>
                <p class="text-muted-foreground max-w-lg mx-auto text-sm leading-relaxed">
                    Thank you! We have received your booking request. Our service advisor will review your slot and contact you to confirm drop-off details.
                </p>
                <div class="border border-border bg-background p-6 max-w-md mx-auto text-left space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-muted-foreground">Booking Reference:</span><span id="conf-ref" class="font-mono-data text-primary font-bold">NFA-2026-0001</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Service:</span><span id="conf-service" class="font-heading font-600 text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Date:</span><span id="conf-date" class="font-mono-data text-foreground">-</span></div>
                    <div class="flex justify-between"><span class="text-muted-foreground">Status:</span><span class="px-2 py-0.5 text-[10px] uppercase font-bold border border-primary/40 bg-primary/15 text-primary">Pending Confirmation</span></div>
                </div>
                <div class="pt-4 flex justify-center gap-4">
                    <a href="/" class="btn-engine border border-border px-6 py-3 text-xs text-foreground hover:border-primary">
                        Return Home
                    </a>
                    <a href="tel:+19077333030" class="btn-engine bg-primary text-primary-foreground px-6 py-3 text-xs">
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
                        allC.classList.remove('border-primary', 'bg-primary/10');
                        allC.classList.add('border-border', 'bg-card');
                    });
                    c.classList.remove('border-border', 'bg-card');
                    c.classList.add('border-primary', 'bg-primary/10');
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
                        t.className = 'h-1.5 bg-primary rounded-sm transition-all duration-300';
                    } else {
                        t.className = 'h-1.5 bg-border rounded-sm transition-all duration-300';
                    }
                }
            });
            labels.forEach((l, idx) => {
                if (l) {
                    if (idx + 1 === step) {
                        l.className = 'text-primary font-bold';
                    } else if (idx + 1 < step) {
                        l.className = 'text-foreground font-semibold';
                    } else {
                        l.className = 'text-muted-foreground';
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
                    c.classList.remove('border-primary', 'bg-primary/10');
                    c.classList.add('border-border', 'bg-card');
                });
                card.classList.remove('border-border', 'bg-card');
                card.classList.add('border-primary', 'bg-primary/10');
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
        body { font-family: 'Inter', sans-serif; background-color: #0c0c0e; color: #f1f5f9; }
        h1, h2, h3, h4, h5, h6 { font-family: 'Sora', sans-serif; font-weight: 800; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 0.2rem 0.55rem;
            font-family: 'JetBrains Mono', monospace;
            font-size: 0.65rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.05em;
        }
        .status-pending { background: rgba(234, 179, 8, 0.15); color: #facc15; border: 1px solid rgba(234, 179, 8, 0.3); }
        .status-confirmed { background: rgba(59, 130, 246, 0.15); color: #60a5fa; border: 1px solid rgba(59, 130, 246, 0.3); }
        .status-in_progress { background: rgba(168, 85, 247, 0.15); color: #c084fc; border: 1px solid rgba(168, 85, 247, 0.3); }
        .status-completed { background: rgba(34, 197, 94, 0.15); color: #4ade80; border: 1px solid rgba(34, 197, 94, 0.3); }
        .status-cancelled { background: rgba(239, 68, 68, 0.15); color: #f87171; border: 1px solid rgba(239, 68, 68, 0.3); }
    </style>
</head>
<body class="min-h-screen bg-[#0c0c0e] text-[#f1f5f9] flex flex-col antialiased">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 border-r border-[#26262a] bg-[#141416] p-6 flex flex-col justify-between shrink-0">
            <div>
                <!-- Brand -->
                <div class="flex items-center gap-3 mb-10">
                    <div class="w-9 h-9 bg-[#DF1F40] text-white flex items-center justify-center font-extrabold text-base">N</div>
                    <div>
                        <div class="font-extrabold text-sm tracking-wide">NORTH FORK AUTO</div>
                        <div class="font-mono text-[10px] text-gray-400 uppercase">Staff Portal</div>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="space-y-1.5 font-mono text-xs">
                    <a href="/staff/dashboard" class="flex items-center gap-3 px-3.5 py-2.5 transition-colors ${activeNav === 'dashboard' ? 'bg-[#DF1F40] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-[#1f1f23]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        Dashboard
                    </a>
                    <a href="/staff/bookings" class="flex items-center gap-3 px-3.5 py-2.5 transition-colors ${activeNav === 'bookings' ? 'bg-[#DF1F40] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-[#1f1f23]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M8 2v4"/><path d="M16 2v4"/><rect width="18" height="18" x="3" y="4" rx="2"/><path d="M3 10h18"/></svg>
                        Bookings
                    </a>
                    <a href="/staff/services" class="flex items-center gap-3 px-3.5 py-2.5 transition-colors ${activeNav === 'services' ? 'bg-[#DF1F40] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-[#1f1f23]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"/></svg>
                        Services
                    </a>
                    <a href="/staff/settings" class="flex items-center gap-3 px-3.5 py-2.5 transition-colors ${activeNav === 'settings' ? 'bg-[#DF1F40] text-white font-bold' : 'text-gray-400 hover:text-white hover:bg-[#1f1f23]'}">
                        <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>
                        Settings &amp; Inbox
                    </a>
                </nav>
            </div>

            <div class="pt-6 border-t border-[#26262a] space-y-3">
                <a href="/" target="_blank" class="flex items-center gap-2 text-xs font-mono text-gray-400 hover:text-white">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    Live Public Site
                </a>
                <a href="/staff/logout" class="flex items-center gap-2 text-xs font-mono text-red-400 hover:text-red-300">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Sign Out
                </a>
            </div>
        </aside>

        <!-- Main Body -->
        <main class="flex-1 overflow-y-auto p-8 lg:p-12">
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
        body { font-family: 'Inter', sans-serif; background: #0a0a0c; color: #f1f5f9; }
        h1, h2, h3 { font-family: 'Sora', sans-serif; font-weight: 800; }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-6 bg-[#0a0a0c]">
    <div class="w-full max-w-md bg-[#141416] border border-[#26262a] p-8 shadow-2xl">
        <div class="flex items-center gap-3 mb-8">
            <div class="w-10 h-10 bg-[#DF1F40] text-white flex items-center justify-center font-extrabold text-base">N</div>
            <div>
                <div class="font-extrabold text-base text-white">NORTH FORK AUTO</div>
                <div class="font-mono text-[10px] text-gray-400 uppercase tracking-wider">Authorized Staff Portal</div>
            </div>
        </div>

        ${errorMsg ? `
            <div class="mb-6 p-4 border border-red-500/40 bg-red-500/10 text-red-400 text-xs font-mono">
                ${escapeHtml(errorMsg)}
            </div>
        ` : ''}

        <form action="/staff/login" method="POST" class="space-y-5">
            <div>
                <label class="block font-mono text-xs uppercase text-gray-400 mb-1.5">Staff Email</label>
                <input type="email" name="email" required value="admin@northforkauto.com" class="w-full bg-[#0a0a0c] border border-[#26262a] px-4 py-3 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
            </div>
            <div>
                <label class="block font-mono text-xs uppercase text-gray-400 mb-1.5">Password</label>
                <input type="password" name="password" required placeholder="••••••••" class="w-full bg-[#0a0a0c] border border-[#26262a] px-4 py-3 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
            </div>
            <div class="flex items-center justify-between text-xs font-mono text-gray-400">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="remember" class="accent-[#DF1F40]"> Remember session
                </label>
                <a href="/" class="text-gray-400 hover:text-white">Public Site &rarr;</a>
            </div>
            <button type="submit" class="w-full bg-[#DF1F40] text-white py-3.5 font-mono text-xs uppercase font-bold tracking-wider hover:bg-[#c41533] transition-colors">
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#26262a] mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Operations Dashboard</h1>
            <p class="text-xs text-gray-400 mt-1 font-mono">Real-time shop metrics, bookings &amp; workflow</p>
        </div>
        <div class="flex gap-3">
            <a href="/staff/bookings" class="px-4 py-2.5 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533] transition-colors">
                + Manage Bookings
            </a>
            <a href="/staff/services" class="px-4 py-2.5 border border-[#26262a] bg-[#141416] text-white text-xs font-mono uppercase hover:border-[#DF1F40] transition-colors">
                Services Catalog
            </a>
        </div>
    </div>

    <!-- Metric Cards Grid -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 sm:gap-6 mb-10">
        <div class="bg-[#141416] border border-[#26262a] p-5">
            <div class="text-[11px] font-mono text-gray-400 uppercase tracking-wider">Total Bookings</div>
            <div class="text-3xl font-extrabold text-white mt-2">${bookings.length}</div>
            <div class="text-[10px] text-gray-500 font-mono mt-1">All-time volume</div>
        </div>
        <div class="bg-[#141416] border border-amber-500/30 p-5">
            <div class="text-[11px] font-mono text-amber-400 uppercase tracking-wider">Pending Action</div>
            <div class="text-3xl font-extrabold text-amber-400 mt-2">${pendingCount}</div>
            <div class="text-[10px] text-amber-500/80 font-mono mt-1">Requires confirmation</div>
        </div>
        <div class="bg-[#141416] border border-blue-500/30 p-5">
            <div class="text-[11px] font-mono text-blue-400 uppercase tracking-wider">Confirmed &amp; Active</div>
            <div class="text-3xl font-extrabold text-blue-400 mt-2">${confirmedCount + inProgressCount}</div>
            <div class="text-[10px] text-blue-500/80 font-mono mt-1">${inProgressCount} in progress</div>
        </div>
        <div class="bg-[#141416] border border-emerald-500/30 p-5">
            <div class="text-[11px] font-mono text-emerald-400 uppercase tracking-wider">Completed Jobs</div>
            <div class="text-3xl font-extrabold text-emerald-400 mt-2">${completedCount}</div>
            <div class="text-[10px] text-emerald-500/80 font-mono mt-1">Ready for pickup</div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
        <div class="bg-[#141416] border border-[#26262a] p-6 flex items-center justify-between">
            <div>
                <div class="text-xs font-mono text-gray-400 uppercase">Active Catalog Services</div>
                <div class="text-2xl font-extrabold text-white mt-1">${activeServicesCount}</div>
            </div>
            <div class="text-xs font-mono text-gray-500">${inactiveServicesCount} hidden</div>
        </div>
        <div class="bg-[#141416] border border-[#26262a] p-6 flex items-center justify-between">
            <div>
                <div class="text-xs font-mono text-gray-400 uppercase">Customer Inquiries</div>
                <div class="text-2xl font-extrabold text-white mt-1">${messages.length}</div>
            </div>
            <a href="/staff/settings" class="text-xs font-mono text-[#DF1F40] hover:underline">View Inbox &rarr;</a>
        </div>
        <div class="bg-[#141416] border border-[#26262a] p-6 flex items-center justify-between">
            <div>
                <div class="text-xs font-mono text-gray-400 uppercase">Cancelled Requests</div>
                <div class="text-2xl font-extrabold text-gray-400 mt-1">${cancelledCount}</div>
            </div>
            <div class="text-xs font-mono text-gray-500">Archived</div>
        </div>
    </div>

    <!-- Recent Bookings Table -->
    <div class="border border-[#26262a] bg-[#141416]">
        <div class="p-6 border-b border-[#26262a] flex items-center justify-between">
            <h2 class="text-lg font-bold text-white">Recent Customer Bookings</h2>
            <a href="/staff/bookings" class="text-xs font-mono text-[#DF1F40] hover:underline">View Full Ledger &rarr;</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#26262a] font-mono text-gray-400 bg-[#0e0e10]">
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
                <tbody class="divide-y divide-[#26262a]">
                    ${recentBookings.length === 0 ? `
                        <tr><td colspan="7" class="p-8 text-center text-gray-500 font-mono">No bookings found in database.</td></tr>
                    ` : recentBookings.map(b => `
                        <tr class="hover:bg-[#1a1a1d] transition-colors">
                            <td class="p-4 font-mono text-[#DF1F40] font-bold">${escapeHtml(b.booking_number || b.booking_reference)}</td>
                            <td class="p-4">
                                <div class="font-bold text-white">${escapeHtml(b.customer_name)}</div>
                                <div class="text-[11px] text-gray-400 font-mono">${escapeHtml(b.customer_phone)}</div>
                            </td>
                            <td class="p-4 text-gray-300">${escapeHtml(b.vehicle_info || (b.vehicle_year ? b.vehicle_year + ' ' + b.vehicle_make + ' ' + b.vehicle_model : 'Standard'))}</td>
                            <td class="p-4 text-white font-medium">${escapeHtml(b.service_name)}</td>
                            <td class="p-4 font-mono text-gray-300">${escapeHtml(b.preferred_date)} <span class="text-gray-500">${escapeHtml(b.preferred_time)}</span></td>
                            <td class="p-4">
                                <span class="status-badge status-${b.status || 'pending'}">${escapeHtml(b.status || 'pending')}</span>
                            </td>
                            <td class="p-4 text-right font-mono">
                                <a href="/staff/bookings?search=${encodeURIComponent(b.booking_number || b.booking_reference)}" class="text-[#DF1F40] hover:underline font-bold">Edit / Manage</a>
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#26262a] mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Bookings Management</h1>
            <p class="text-xs text-gray-400 mt-1 font-mono">Create, review, update status, and manage shop schedule</p>
        </div>
        <button onclick="document.getElementById('modal-create-booking').classList.remove('hidden')" class="px-5 py-2.5 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533] transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            + Create Manual Booking
        </button>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-[#141416] border border-[#26262a] p-4 mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
        <!-- Status Tabs -->
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            <a href="/staff/bookings?status=all${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 border ${filterStatus === 'all' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">All (${(currentDb.bookings || []).length})</a>
            <a href="/staff/bookings?status=pending${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 border ${filterStatus === 'pending' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">Pending</a>
            <a href="/staff/bookings?status=confirmed${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 border ${filterStatus === 'confirmed' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">Confirmed</a>
            <a href="/staff/bookings?status=in_progress${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 border ${filterStatus === 'in_progress' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">In Progress</a>
            <a href="/staff/bookings?status=completed${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 border ${filterStatus === 'completed' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">Completed</a>
            <a href="/staff/bookings?status=cancelled${searchQuery ? '&search=' + encodeURIComponent(searchQuery) : ''}" class="px-3 py-1.5 border ${filterStatus === 'cancelled' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">Cancelled</a>
        </div>

        <!-- Search Input -->
        <form method="GET" action="/staff/bookings" class="flex gap-2 w-full md:w-auto">
            <input type="hidden" name="status" value="${escapeHtml(filterStatus)}">
            <input type="text" name="search" value="${escapeHtml(searchQuery)}" placeholder="Search customer, VIN, ref..." class="bg-[#0c0c0e] border border-[#26262a] px-4 py-1.5 text-xs text-white focus:outline-none focus:border-[#DF1F40] w-full md:w-64">
            <button type="submit" class="px-4 py-1.5 bg-[#26262a] text-white text-xs font-mono hover:bg-[#323238]">Filter</button>
        </form>
    </div>

    <!-- Bookings Table -->
    <div class="border border-[#26262a] bg-[#141416]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#26262a] font-mono text-gray-400 bg-[#0e0e10]">
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
                <tbody class="divide-y divide-[#26262a]">
                    ${bookings.length === 0 ? `
                        <tr><td colspan="7" class="p-12 text-center text-gray-500 font-mono">No matching bookings found.</td></tr>
                    ` : bookings.map(b => `
                        <tr class="hover:bg-[#1a1a1d] transition-colors">
                            <td class="p-4 font-mono text-[#DF1F40] font-bold">${escapeHtml(b.booking_number || b.booking_reference)}</td>
                            <td class="p-4">
                                <div class="font-bold text-white text-sm">${escapeHtml(b.customer_name)}</div>
                                <div class="text-gray-400 font-mono">${escapeHtml(b.customer_phone)}</div>
                                <div class="text-gray-500 text-[11px] font-mono">${escapeHtml(b.customer_email)}</div>
                            </td>
                            <td class="p-4">
                                <div class="text-gray-200 font-medium">${escapeHtml(b.vehicle_info || (b.vehicle_year ? b.vehicle_year + ' ' + b.vehicle_make + ' ' + b.vehicle_model : 'Vehicle Not Specified'))}</div>
                                ${b.vehicle_vin ? `<div class="text-[10px] text-gray-500 font-mono">VIN: ${escapeHtml(b.vehicle_vin)}</div>` : ''}
                            </td>
                            <td class="p-4 text-white font-medium">${escapeHtml(b.service_name)}</td>
                            <td class="p-4 font-mono">
                                <div class="text-gray-300">${escapeHtml(b.preferred_date)}</div>
                                <div class="text-gray-500 text-[11px]">${escapeHtml(b.preferred_time)}</div>
                            </td>
                            <td class="p-4">
                                <span class="status-badge status-${b.status || 'pending'}">${escapeHtml(b.status || 'pending')}</span>
                            </td>
                            <td class="p-4 text-right space-x-2 font-mono">
                                <button onclick="openEditBooking(${JSON.stringify(b).replace(/"/g, '&quot;')})" class="px-2.5 py-1.5 border border-[#26262a] text-gray-300 hover:border-[#DF1F40] hover:text-white text-[11px]">
                                    Edit
                                </button>
                                <button onclick="confirmDeleteBooking(${b.id}, '${escapeHtml(b.booking_number || b.booking_reference)}')" class="px-2.5 py-1.5 border border-red-900/50 text-red-400 hover:bg-red-500/20 text-[11px]">
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
    <div id="modal-create-booking" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <div class="bg-[#141416] border border-[#26262a] max-w-2xl w-full p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-[#26262a] pb-4">
                <h3 class="text-xl font-bold text-white">Create New Appointment</h3>
                <button onclick="document.getElementById('modal-create-booking').classList.add('hidden')" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <form action="/staff/bookings" method="POST" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Customer Name *</label>
                        <input type="text" name="customer_name" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Phone Number *</label>
                        <input type="tel" name="customer_phone" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Email Address *</label>
                        <input type="email" name="customer_email" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Requested Service *</label>
                        <select name="service_name" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                            ${services.map(s => `<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)} (${escapeHtml(s.price || 'Quote')})</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Vehicle Details *</label>
                        <input type="text" name="vehicle_info" required placeholder="e.g. 2021 Subaru Outback" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Preferred Date *</label>
                        <input type="date" name="preferred_date" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Preferred Time *</label>
                        <select name="preferred_time" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
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
                    <label class="block font-mono text-xs text-gray-400 mb-1">Initial Status</label>
                    <select name="status" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                        <option value="pending">Pending</option>
                        <option value="confirmed" selected>Confirmed</option>
                        <option value="in_progress">In Progress</option>
                    </select>
                </div>
                <div>
                    <label class="block font-mono text-xs text-gray-400 mb-1">Internal Staff Notes</label>
                    <textarea name="internal_notes" rows="2" placeholder="e.g. Parts assigned to bay 2" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#26262a]">
                    <button type="button" onclick="document.getElementById('modal-create-booking').classList.add('hidden')" class="px-4 py-2 border border-[#26262a] text-xs font-mono text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533]">Save Booking</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Booking -->
    <div id="modal-edit-booking" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <div class="bg-[#141416] border border-[#26262a] max-w-2xl w-full p-6 sm:p-8 space-y-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between border-b border-[#26262a] pb-4">
                <h3 class="text-xl font-bold text-white">Edit Booking <span id="edit-modal-ref" class="text-[#DF1F40] font-mono font-normal"></span></h3>
                <button onclick="document.getElementById('modal-edit-booking').classList.add('hidden')" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <form id="form-edit-booking" method="POST" class="space-y-4">
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Customer Name *</label>
                        <input type="text" id="edit-cust-name" name="customer_name" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Phone Number *</label>
                        <input type="tel" id="edit-cust-phone" name="customer_phone" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Email Address *</label>
                        <input type="email" id="edit-cust-email" name="customer_email" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Requested Service *</label>
                        <select id="edit-service" name="service_name" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                            ${services.map(s => `<option value="${escapeHtml(s.name)}">${escapeHtml(s.name)}</option>`).join('')}
                        </select>
                    </div>
                </div>
                <div class="grid sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Vehicle Details</label>
                        <input type="text" id="edit-vehicle" name="vehicle_info" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Preferred Date</label>
                        <input type="date" id="edit-date" name="preferred_date" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Status Transition *</label>
                        <select id="edit-status" name="status" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                            <option value="pending">Pending</option>
                            <option value="confirmed">Confirmed</option>
                            <option value="in_progress">In Progress</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-gray-400 mb-1">Internal Staff Notes</label>
                    <textarea id="edit-notes" name="internal_notes" rows="3" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#26262a]">
                    <button type="button" onclick="document.getElementById('modal-edit-booking').classList.add('hidden')" class="px-4 py-2 border border-[#26262a] text-xs font-mono text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533]">Update Booking</button>
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
    <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-6 border-b border-[#26262a] mb-8 gap-4">
        <div>
            <h1 class="text-2xl sm:text-3xl font-extrabold text-white">Services Catalog</h1>
            <p class="text-xs text-gray-400 mt-1 font-mono">Manage shop capabilities, pricing, and live website availability</p>
        </div>
        <button onclick="document.getElementById('modal-create-service').classList.remove('hidden')" class="px-5 py-2.5 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533] transition-colors flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
            + Add New Service
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-[#141416] border border-[#26262a] p-4 mb-6 flex flex-col md:flex-row gap-4 justify-between items-center">
        <div class="flex flex-wrap gap-2 text-xs font-mono">
            <a href="/staff/services?category=all" class="px-3 py-1.5 border ${filterCat === 'all' ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">All Categories</a>
            ${categories.map(c => `
                <a href="/staff/services?category=${encodeURIComponent(c)}" class="px-3 py-1.5 border ${filterCat.toLowerCase() === c.toLowerCase() ? 'border-[#DF1F40] bg-[#DF1F40] text-white font-bold' : 'border-[#26262a] text-gray-400 hover:text-white'}">${escapeHtml(c)}</a>
            `).join('')}
        </div>
        <form method="GET" action="/staff/services" class="flex gap-2 w-full md:w-auto">
            <input type="text" name="search" value="${escapeHtml(searchQuery)}" placeholder="Search service..." class="bg-[#0c0c0e] border border-[#26262a] px-4 py-1.5 text-xs text-white focus:outline-none focus:border-[#DF1F40]">
            <button type="submit" class="px-4 py-1.5 bg-[#26262a] text-white text-xs font-mono">Search</button>
        </form>
    </div>

    <!-- Services Table -->
    <div class="border border-[#26262a] bg-[#141416]">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="border-b border-[#26262a] font-mono text-gray-400 bg-[#0e0e10]">
                    <tr>
                        <th class="p-4">Service Name</th>
                        <th class="p-4">Category</th>
                        <th class="p-4">Price</th>
                        <th class="p-4">Est. Duration</th>
                        <th class="p-4">Status on Live Site</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#26262a]">
                    ${services.map(s => `
                        <tr class="hover:bg-[#1a1a1d] transition-colors">
                            <td class="p-4">
                                <div class="font-bold text-white text-sm">${escapeHtml(s.name)}</div>
                                <div class="text-gray-400 text-[11px] max-w-md truncate mt-0.5">${escapeHtml(s.description)}</div>
                            </td>
                            <td class="p-4 font-mono text-gray-300">${escapeHtml(s.category)}</td>
                            <td class="p-4 font-mono text-primary font-bold text-sm">${escapeHtml(s.price || 'Custom Quote')}</td>
                            <td class="p-4 font-mono text-gray-400">${s.duration_minutes || 60} mins</td>
                            <td class="p-4">
                                ${s.is_active ? `
                                    <span class="inline-flex items-center px-2 py-0.5 font-mono text-[10px] font-bold uppercase bg-emerald-500/15 text-emerald-400 border border-emerald-500/30">Active &amp; Public</span>
                                ` : `
                                    <span class="inline-flex items-center px-2 py-0.5 font-mono text-[10px] font-bold uppercase bg-red-500/15 text-red-400 border border-red-500/30">Disabled / Hidden</span>
                                `}
                            </td>
                            <td class="p-4 text-right space-x-2 font-mono">
                                <form action="/staff/services/${s.id}/toggle" method="POST" class="inline">
                                    <button type="submit" class="px-2.5 py-1.5 border ${s.is_active ? 'border-amber-500/40 text-amber-400 hover:bg-amber-500/10' : 'border-emerald-500/40 text-emerald-400 hover:bg-emerald-500/10'} text-[11px]">
                                        ${s.is_active ? 'Disable' : 'Enable'}
                                    </button>
                                </form>
                                <button onclick="openEditService(${JSON.stringify(s).replace(/"/g, '&quot;')})" class="px-2.5 py-1.5 border border-[#26262a] text-gray-300 hover:border-[#DF1F40] hover:text-white text-[11px]">
                                    Edit
                                </button>
                                <button onclick="confirmDeleteService(${s.id}, '${escapeHtml(s.name)}')" class="px-2.5 py-1.5 border border-red-900/50 text-red-400 hover:bg-red-500/20 text-[11px]">
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
    <div id="modal-create-service" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <div class="bg-[#141416] border border-[#26262a] max-w-xl w-full p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-[#26262a] pb-4">
                <h3 class="text-xl font-bold text-white">Add New Service</h3>
                <button onclick="document.getElementById('modal-create-service').classList.add('hidden')" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <form action="/staff/services" method="POST" class="space-y-4">
                <div>
                    <label class="block font-mono text-xs text-gray-400 mb-1">Service Name *</label>
                    <input type="text" name="name" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Category *</label>
                        <input type="text" name="category" required placeholder="e.g. Engine, Brakes" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Price</label>
                        <input type="text" name="price" placeholder="e.g. $149.99 or Custom Quote" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-gray-400 mb-1">Description *</label>
                    <textarea name="description" rows="3" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#26262a]">
                    <button type="button" onclick="document.getElementById('modal-create-service').classList.add('hidden')" class="px-4 py-2 border border-[#26262a] text-xs font-mono text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533]">Create Service</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: Edit Service -->
    <div id="modal-edit-service" class="hidden fixed inset-0 z-50 bg-black/80 flex items-center justify-center p-4">
        <div class="bg-[#141416] border border-[#26262a] max-w-xl w-full p-6 sm:p-8 space-y-6">
            <div class="flex items-center justify-between border-b border-[#26262a] pb-4">
                <h3 class="text-xl font-bold text-white">Edit Service</h3>
                <button onclick="document.getElementById('modal-edit-service').classList.add('hidden')" class="text-gray-400 hover:text-white">&times;</button>
            </div>
            <form id="form-edit-service" method="POST" class="space-y-4">
                <div>
                    <label class="block font-mono text-xs text-gray-400 mb-1">Service Name *</label>
                    <input type="text" id="edit-srv-name" name="name" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                </div>
                <div class="grid sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Category *</label>
                        <input type="text" id="edit-srv-cat" name="category" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                    <div>
                        <label class="block font-mono text-xs text-gray-400 mb-1">Price</label>
                        <input type="text" id="edit-srv-price" name="price" class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                    </div>
                </div>
                <div>
                    <label class="block font-mono text-xs text-gray-400 mb-1">Description *</label>
                    <textarea id="edit-srv-desc" name="description" rows="3" required class="w-full bg-[#0c0c0e] border border-[#26262a] px-3 py-2 text-sm text-white focus:outline-none focus:border-[#DF1F40]"></textarea>
                </div>
                <div class="pt-4 flex justify-end gap-3 border-t border-[#26262a]">
                    <button type="button" onclick="document.getElementById('modal-edit-service').classList.add('hidden')" class="px-4 py-2 border border-[#26262a] text-xs font-mono text-gray-400 hover:text-white">Cancel</button>
                    <button type="submit" class="px-6 py-2 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533]">Update Service</button>
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
        <div class="border-b border-[#26262a] pb-4 mb-6">
            <h1 class="text-2xl font-extrabold text-white">General Shop Settings</h1>
            <p class="text-xs text-gray-400 mt-1 font-mono">Update shop contact numbers, physical location, and operating hours</p>
        </div>
        <form action="/staff/settings" method="POST" class="bg-[#141416] border border-[#26262a] p-6 sm:p-8 max-w-3xl space-y-6">
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-mono text-xs uppercase text-gray-400 mb-2">Shop Name</label>
                    <input type="text" name="shop_name" value="${escapeHtml(settings.shop_name || 'North Fork Auto')}" class="w-full bg-[#0c0c0e] border border-[#26262a] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                </div>
                <div>
                    <label class="block font-mono text-xs uppercase text-gray-400 mb-2">Primary Phone</label>
                    <input type="text" name="shop_phone" value="${escapeHtml(settings.shop_phone || '(907) 733-3030')}" class="w-full bg-[#0c0c0e] border border-[#26262a] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                </div>
            </div>
            <div class="grid sm:grid-cols-2 gap-6">
                <div>
                    <label class="block font-mono text-xs uppercase text-gray-400 mb-2">Contact Email</label>
                    <input type="email" name="shop_email" value="${escapeHtml(settings.shop_email || 'info@northforkauto.com')}" class="w-full bg-[#0c0c0e] border border-[#26262a] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                </div>
                <div>
                    <label class="block font-mono text-xs uppercase text-gray-400 mb-2">Physical Address</label>
                    <input type="text" name="shop_address" value="${escapeHtml(settings.shop_address || 'Talkeetna Spur Road, Talkeetna, AK 99676')}" class="w-full bg-[#0c0c0e] border border-[#26262a] px-4 py-2.5 text-sm text-white focus:outline-none focus:border-[#DF1F40]">
                </div>
            </div>
            <div class="pt-4 flex justify-end">
                <button type="submit" class="px-6 py-2.5 bg-[#DF1F40] text-white text-xs font-mono uppercase font-bold hover:bg-[#c41533]">Save Settings</button>
            </div>
        </form>
    </div>

    <!-- Customer Inquiries Inbox -->
    <div>
        <div class="border-b border-[#26262a] pb-4 mb-6">
            <h2 class="text-xl font-bold text-white">Customer Inquiries Inbox (${messages.length})</h2>
            <p class="text-xs text-gray-400 mt-1 font-mono">Inbound messages submitted through the public contact form</p>
        </div>
        <div class="border border-[#26262a] bg-[#141416]">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="border-b border-[#26262a] font-mono text-gray-400 bg-[#0e0e10]">
                        <tr>
                            <th class="p-4">Sender</th>
                            <th class="p-4">Subject</th>
                            <th class="p-4">Message</th>
                            <th class="p-4">Received Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#26262a]">
                        ${messages.length === 0 ? `
                            <tr><td colspan="4" class="p-8 text-center text-gray-500 font-mono">No customer inquiries received yet.</td></tr>
                        ` : messages.map(m => `
                            <tr class="hover:bg-[#1a1a1d]">
                                <td class="p-4 font-bold text-white">
                                    <div>${escapeHtml(m.name || 'Anonymous')}</div>
                                    <div class="text-[11px] text-gray-400 font-mono font-normal">${escapeHtml(m.email || '')} ${m.phone ? '· ' + escapeHtml(m.phone) : ''}</div>
                                </td>
                                <td class="p-4 font-medium text-gray-200">${escapeHtml(m.subject || 'General Inquiry')}</td>
                                <td class="p-4 text-gray-300 max-w-lg leading-relaxed">${escapeHtml(m.message || '')}</td>
                                <td class="p-4 font-mono text-gray-400">${new Date(m.created_at).toLocaleDateString()}</td>
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

  // 1. PUBLIC GET ROUTES
  if (method === 'GET' && pathname === '/') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(handleHome());
  }

  if (method === 'GET' && pathname === '/services') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(handleServices());
  }

  if (method === 'GET' && pathname === '/about') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(handleAbout());
  }

  if (method === 'GET' && pathname === '/contact') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
    return res.end(handleContact());
  }

  if (method === 'GET' && pathname === '/book') {
    res.writeHead(200, { 'Content-Type': 'text/html; charset=utf-8' });
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

      let session = chatSessions.get(sessionId);
      if (!session) {
        session = {
          messages: [],
          lastReq: Date.now(),
          reqCount: 1,
          pendingAppointmentId: null,
          verifiedAppointmentId: null
        };
        chatSessions.set(sessionId, session);
      }

      session.messages.push({
        role: 'user',
        content: userMessage,
        time: new Date().toISOString()
      });

      const lockoutKey = `verify_lockout:${sessionId}:${clientIp}`;
      let botReply = null;
      let appointmentPayload = null;

      // 1. Check if user provided an appointment confirmation ID (e.g. NFA-2026-8659)
      const extractedId = extractConfirmationId(userMessage);
      if (extractedId) {
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
      else if (session.pendingAppointmentId) {
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

        const aiResult = await callCloudflareWorkersAI(aiMessages);
        botReply = aiResult.reply || "I'm having trouble connecting right now. Please call our team at (907) 733-3030.";
      }

      session.messages.push({
        role: 'assistant',
        content: botReply,
        time: new Date().toISOString()
      });

      if (session.messages.length > 20) {
        session.messages = session.messages.slice(-20);
      }

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

  if (method === 'POST' && pathname === '/api/chat/appointment') {
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

  if (method === 'POST' && pathname === '/api/chat/clear') {
    let body = '';
    req.on('data', chunk => body += chunk);
    req.on('end', () => {
      res.setHeader('Content-Type', 'application/json');
      let params = {};
      try { params = JSON.parse(body || '{}'); } catch(e){}
      if (params.session_id && chatSessions.has(params.session_id)) {
        chatSessions.delete(params.session_id);
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
    <div class="section-pad py-32 text-center">
        <h1 class="font-heading font-800 text-6xl text-[#DF1F40] mb-4">404</h1>
        <h2 class="font-heading font-700 text-2xl text-white mb-4">Page Not Found</h2>
        <p class="text-gray-400 max-w-md mx-auto mb-8 text-sm">The page you requested does not exist or has been moved.</p>
        <a href="/" class="btn-engine bg-primary text-primary-foreground px-6 py-3 text-xs">Return Home</a>
    </div>
  `));
});

server.listen(PORT, () => {
  console.log(`North Fork Auto Application Server running at http://localhost:${PORT}`);
});
