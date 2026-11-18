const fs = require('fs');

// Read PNG and parse raw RGBA bitmap using pngjs if available or simple check
// Let's see if pngjs or any image library exists in node_modules
try {
  const pngjs = require('pngjs');
  console.log('pngjs available');
} catch (e) {
  console.log('pngjs not installed');
}
