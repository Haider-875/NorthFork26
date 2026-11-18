const http = require('http');

function postJson(path, payload) {
  return new Promise((resolve, reject) => {
    const data = JSON.stringify(payload);
    const req = http.request({
      hostname: 'localhost',
      port: 8000,
      path: path,
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Content-Length': Buffer.byteLength(data)
      }
    }, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    });
    req.on('error', reject);
    req.write(data);
    req.end();
  });
}

function getJson(path) {
  return new Promise((resolve, reject) => {
    http.get('http://localhost:8000' + path, res => {
      let body = '';
      res.on('data', chunk => body += chunk);
      res.on('end', () => {
        try {
          resolve({ status: res.statusCode, data: JSON.parse(body) });
        } catch (e) {
          resolve({ status: res.statusCode, raw: body });
        }
      });
    }).on('error', reject);
  });
}

async function runTests() {
  console.log('========================================================');
  console.log('  NORTH FORK AUTO — APPOINTMENT LOOKUP TEST SUITE');
  console.log('========================================================\n');

  let passed = 0;
  let failed = 0;

  function assert(condition, message) {
    if (condition) {
      console.log(`  [PASS] ${message}`);
      passed++;
    } else {
      console.error(`  [FAIL] ${message}`);
      failed++;
    }
  }

  // 1. Provider Status Check
  console.log('TEST 1: Provider Status');
  const statusRes = await getJson('/api/appointments/provider-status');
  assert(statusRes.status === 200, 'Provider status returned HTTP 200');
  assert(statusRes.data.status.provider === 'local_database', 'Provider identified as local_database');
  assert(statusRes.data.status.timezone.includes('America/Anchorage'), 'Timezone verified as America/Anchorage');

  // 2. Lookup without verification (Privacy Gate)
  console.log('\nTEST 2: Privacy Gate on Lookup (NFA-2026-3302)');
  const privacyRes = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-2026-3302'
  });
  assert(privacyRes.status === 200, 'Found booking returns HTTP 200');
  assert(privacyRes.data.verified === false, 'Appointment marked verified=false');
  assert(privacyRes.data.reply.includes('verify the email address or phone number'), 'Prompt asks for customer verification for privacy');
  assert(!privacyRes.data.appointment.customer_email, 'Customer email is NOT disclosed in unverified summary');

  // 3. Lookup with Correct Verification
  console.log('\nTEST 3: Lookup with Correct Verification (Email/Phone)');
  const verifiedRes = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-2026-3302',
    verification_detail: 'dave@test.com'
  });
  assert(verifiedRes.status === 200, 'Verified lookup returns HTTP 200');
  assert(verifiedRes.data.verified === true, 'Appointment marked verified=true');
  assert(verifiedRes.data.appointment.service_name === 'Oil & Filter Service', 'Correct service name returned');
  assert(verifiedRes.data.reply.includes('Your appointment is confirmed'), 'Confirmation reply generated with verified date/time');

  // 3b. Phone number matching with formatting
  console.log('\nTEST 3b: Lookup with Phone Number Matching');
  const phoneRes = await postJson('/api/appointments/verify', {
    confirmation_id: 'NFA-2026-3302',
    verification_detail: '907-555-7766' // record is (907) 555-7766
  });
  assert(phoneRes.status === 200, 'Formatted phone number verified successfully');
  assert(phoneRes.data.verified === true, 'Phone number matched and verified');

  // 4. Invalid Confirmation ID
  console.log('\nTEST 4: Invalid Confirmation ID');
  const invalidRes = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-9999-XXXX'
  });
  assert(invalidRes.status === 404, 'Invalid ID returns HTTP 404');
  assert(invalidRes.data.error === 'not_found', 'Error is not_found');
  assert(invalidRes.data.message.includes("couldn't find an appointment"), 'Appropriate not found message returned');

  // 5. Incorrect Verification Details
  console.log('\nTEST 5: Incorrect Verification Details');
  const wrongDetailRes = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-2026-3302',
    verification_detail: 'wrong_email@gmail.com',
    session_id: 'sess_test_wrong'
  });
  assert(wrongDetailRes.status === 403, 'Incorrect verification returns HTTP 403 Forbidden');
  assert(wrongDetailRes.data.error === 'verification_failed', 'Error code is verification_failed');

  // 6. Conversational Chatbot Flow (Session memory + 2-step verification)
  console.log('\nTEST 6: Conversational Chatbot Multi-turn Flow');
  const chatSession = 'sess_test_turn_' + Date.now();

  // Turn 1: Customer asks to check ID
  const turn1 = await postJson('/api/chat', {
    session_id: chatSession,
    message: 'Can you please check my appointment NFA-2026-1001?'
  });
  assert(turn1.status === 200, 'Turn 1 returns HTTP 200');
  assert(turn1.data.reply.includes('verify the email address or phone number'), 'Bot requests phone/email verification');

  // Turn 2: Customer provides wrong email
  const turn2 = await postJson('/api/chat', {
    session_id: chatSession,
    message: 'My email is hacker@otherdomain.com'
  });
  assert(turn2.status === 200, 'Turn 2 returns HTTP 200');
  assert(turn2.data.reply.includes('do not match our records'), 'Bot informs verification details do not match');

  // Turn 3: Customer provides correct email (sarah@example.com for NFA-2026-1001)
  const turn3 = await postJson('/api/chat', {
    session_id: chatSession,
    message: 'My email is sarah@example.com'
  });
  assert(turn3.status === 200, 'Turn 3 returns HTTP 200');
  assert(turn3.data.reply.includes('Your appointment is confirmed'), 'Bot discloses verified details upon successful verification');
  assert(turn3.data.appointment && turn3.data.appointment.confirmation_id === 'NFA-2026-1001', 'Appointment object returned with NFA-2026-1001');

  // Turn 4: Follow-up question in the same session without re-verification
  const turn4 = await postJson('/api/chat', {
    session_id: chatSession,
    message: 'What service did I choose again?'
  });
  assert(turn4.status === 200, 'Turn 4 returns HTTP 200');
  assert(turn4.data.reply.length > 5, 'AI successfully responds with context in mind');

  // 7. Test Appointment Status Variations (Pending, Confirmed, Cancelled, Rescheduled)
  console.log('\nTEST 7: Appointment Status Handling');
  const statusTestAppt = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-2026-1001',
    verification_detail: 'sarah@example.com'
  });
  assert(statusTestAppt.data.appointment.status === 'Confirmed', 'Confirmed status handled properly');

  const pendingTestAppt = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-2026-1002',
    verification_detail: 'mvance@example.com'
  });
  assert(pendingTestAppt.data.appointment.status === 'Pending', 'Pending status handled properly');

  // 8. Rescheduling and Cancellation Flow
  console.log('\nTEST 8: Future-Ready Rescheduling and Cancellation');
  // Attempt cancel without confirmation
  const unconfirmedCancel = await postJson('/api/appointments/cancel', {
    confirmation_id: 'NFA-2026-8897',
    verification_detail: 'auditor@test.local',
    confirmed: false
  });
  assert(unconfirmedCancel.status === 400, 'Cancellation without explicit confirmation is rejected (HTTP 400)');

  // Confirmed reschedule on mock record NFA-2026-8897
  const rescheduleRes = await postJson('/api/appointments/reschedule', {
    confirmation_id: 'NFA-2026-8897',
    verification_detail: 'auditor@test.local',
    preferred_date: '2026-11-28',
    preferred_time: '11:00 AM',
    confirmed: true
  });
  assert(rescheduleRes.status === 200, 'Rescheduling with verification and confirmation succeeds');

  // Verify new date in database
  const verifyRescheduled = await postJson('/api/appointments/lookup', {
    confirmation_id: 'NFA-2026-8897',
    verification_detail: 'auditor@test.local'
  });
  assert(verifyRescheduled.data.appointment.raw_date === '2026-11-28', 'Database record updated with new date');
  assert(verifyRescheduled.data.appointment.status === 'Rescheduled', 'Record status updated to Rescheduled');

  // 9. Rate Limiting / Lockout Test
  console.log('\nTEST 9: Brute-Force Lockout Protection');
  const bruteSession = 'brute_session_' + Date.now();
  for (let i = 0; i < 5; i++) {
    await postJson('/api/appointments/verify', {
      confirmation_id: 'NFA-2026-1001',
      verification_detail: 'random_wrong_' + i + '@domain.com',
      session_id: bruteSession
    });
  }
  const lockoutAttempt = await postJson('/api/appointments/verify', {
    confirmation_id: 'NFA-2026-1001',
    verification_detail: 'sarah@example.com', // even correct detail is locked
    session_id: bruteSession
  });
  assert(lockoutAttempt.status === 429, 'Brute-force lockout triggered (HTTP 429)');
  assert(lockoutAttempt.data.error === 'locked_out', 'Error indicates locked_out');

  console.log('\n========================================================');
  console.log(`  TEST RESULTS: ${passed} PASSED, ${failed} FAILED`);
  console.log('========================================================\n');
}

runTests().catch(console.error);
