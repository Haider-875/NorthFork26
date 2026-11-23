const fs = require('fs');
const path = require('path');

async function main() {
  console.log('Fetching rendered pages from local server...');
  
  const pages = {};
  const publicRoutes = [
    { key: 'home', path: '/' },
    { key: 'services', path: '/services' },
    { key: 'about', path: '/about' },
    { key: 'reviews', path: '/reviews' },
    { key: 'contact', path: '/contact' },
    { key: 'book', path: '/book' },
  ];

  const adminRoutes = [
    { key: 'admin_dashboard', path: '/staff/dashboard', auth: true },
    { key: 'admin_bookings', path: '/staff/bookings', auth: true },
    { key: 'admin_services', path: '/staff/services', auth: true },
    { key: 'admin_settings', path: '/staff/settings', auth: true },
    { key: 'admin_login', path: '/staff/login' },
  ];

  for (const r of publicRoutes) {
    const res = await fetch(`http://localhost:8000${r.path}`);
    if (!res.ok) {
      throw new Error(`Failed to fetch ${r.path}: ${res.status}`);
    }
    pages[r.key] = await res.text();
    console.log(`Fetched ${r.path} (${pages[r.key].length} bytes)`);
  }

  const token = 'dev_admin_session_token_2026';
  for (const r of adminRoutes) {
    const headers = {};
    if (r.auth) {
      headers['Cookie'] = `nfa_session=${token}`;
    }
    const res = await fetch(`http://localhost:8000${r.path}`, { headers });
    if (!res.ok) {
      throw new Error(`Failed to fetch ${r.path}: ${res.status}`);
    }
    pages[r.key] = await res.text();
    console.log(`Fetched ${r.path} (${pages[r.key].length} bytes)`);
  }

  // Load chatbot component for public pages
  const chatbotBlade = fs.readFileSync(path.join(__dirname, '..', 'resources', 'views', 'components', 'chatbot.blade.php'), 'utf8');
  console.log(`Loaded chatbot.blade.php (${chatbotBlade.length} bytes)`);

  for (const r of publicRoutes) {
    const k = r.key;
    if (!pages[k].includes('nfa-chatbot-root')) {
      console.log(`Injecting chatbot into ${k}...`);
      pages[k] = pages[k].replace('</body>', `${chatbotBlade}\n</body>`);
    }
  }

  // Write pages to dedicated files in public/pages_cache/
  const cacheDir = path.join(__dirname, '..', 'public', 'pages_cache');
  if (!fs.existsSync(cacheDir)) {
    fs.mkdirSync(cacheDir, { recursive: true });
  }

  for (const k of Object.keys(pages)) {
    fs.writeFileSync(path.join(cacheDir, `${k}.html`), pages[k], 'utf8');
    console.log(`Wrote public/pages_cache/${k}.html`);
  }

  console.log('Done generating cached pages including admin views!');
}

main().catch(console.error);
