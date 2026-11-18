const fs = require('fs');
const path = require('path');

async function main() {
  console.log('Fetching rendered pages from local server...');
  
  const pages = {};
  const routes = [
    { key: 'home', path: '/' },
    { key: 'services', path: '/services' },
    { key: 'about', path: '/about' },
    { key: 'contact', path: '/contact' },
    { key: 'book', path: '/book' },
  ];

  for (const r of routes) {
    const res = await fetch(`http://localhost:8000${r.path}`);
    if (!res.ok) {
      throw new Error(`Failed to fetch ${r.path}: ${res.status}`);
    }
    pages[r.key] = await res.text();
    console.log(`Fetched ${r.path} (${pages[r.key].length} bytes)`);
  }

  // Load chatbot component
  const chatbotBlade = fs.readFileSync(path.join(__dirname, '..', 'resources', 'views', 'components', 'chatbot.blade.php'), 'utf8');
  console.log(`Loaded chatbot.blade.php (${chatbotBlade.length} bytes)`);

  // Verify that pages have chatbot
  for (const k of Object.keys(pages)) {
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

  console.log('Done generating cached pages!');
}

main().catch(console.error);
