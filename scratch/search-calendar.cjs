const fs = require('fs');
const path = require('path');

function search(dir, terms) {
  const files = fs.readdirSync(dir);
  for (const f of files) {
    if (f === '.git' || f === 'node_modules' || f === 'scratch') continue;
    const full = path.join(dir, f);
    const stat = fs.statSync(full);
    if (stat.isDirectory()) {
      search(full, terms);
    } else if (stat.isFile() && (f.endsWith('.php') || f.endsWith('.cjs') || f.endsWith('.json') || f.endsWith('.blade.php') || f.endsWith('.env'))) {
      const content = fs.readFileSync(full, 'utf8');
      for (const t of terms) {
        if (content.toLowerCase().includes(t.toLowerCase())) {
          console.log(`Found '${t}' in ${full}`);
        }
      }
    }
  }
}

search('.', ['calendar', 'google', 'calendly', 'acuity', 'square', 'oauth']);
