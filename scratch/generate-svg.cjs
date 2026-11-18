const fs = require('fs');
const zlib = require('zlib');

const buf = fs.readFileSync('public/images/support-icon.png');
let pos = 8;
const idatChunks = [];
let width = 0, height = 0;

while (pos < buf.length) {
  const length = buf.readUInt32BE(pos);
  const type = buf.toString('ascii', pos + 4, pos + 8);
  if (type === 'IHDR') {
    width = buf.readUInt32BE(pos + 8);
    height = buf.readUInt32BE(pos + 12);
  } else if (type === 'IDAT') {
    idatChunks.push(buf.subarray(pos + 8, pos + 8 + length));
  }
  pos += 12 + length;
}

const raw = zlib.inflateSync(Buffer.concat(idatChunks));
// Filter type per scanline
const stride = 1 + width * 4;
const grid = [];

for (let y = 0; y < height; y++) {
  const row = [];
  const filter = raw[y * stride];
  // Since PNG filters can be Sub, Up, Average, Paeth:
  // Let's decode actual pixel values
  grid.push(row);
}

// Let's write full unfilter:
const uncompressed = Buffer.alloc(width * height * 4);
function unfilterPNG(raw, width, height) {
  const bpp = 4;
  const stride = 1 + width * bpp;
  const out = Buffer.alloc(width * height * bpp);

  for (let y = 0; y < height; y++) {
    const filter = raw[y * stride];
    const prevRow = y > 0 ? (y - 1) * width * bpp : null;
    const curRow = y * width * bpp;

    for (let x = 0; x < width * bpp; x++) {
      const rawByte = raw[y * stride + 1 + x];
      const a = x >= bpp ? out[curRow + x - bpp] : 0;
      const b = prevRow !== null ? out[prevRow + x] : 0;
      const c = (prevRow !== null && x >= bpp) ? out[prevRow + x - bpp] : 0;

      let val = 0;
      if (filter === 0) val = rawByte;
      else if (filter === 1) val = (rawByte + a) & 0xff;
      else if (filter === 2) val = (rawByte + b) & 0xff;
      else if (filter === 3) val = (rawByte + Math.floor((a + b) / 2)) & 0xff;
      else if (filter === 4) {
        const p = a + b - c;
        const pa = Math.abs(p - a);
        const pb = Math.abs(p - b);
        const pc = Math.abs(p - c);
        let pr = (pa <= pb && pa <= pc) ? a : (pb <= pc ? b : c);
        val = (rawByte + pr) & 0xff;
      }
      out[curRow + x] = val;
    }
  }
  return out;
}

const pixels = unfilterPNG(raw, width, height);

// Build horizontal run rects for crisp lossless SVG
let rects = [];
for (let y = 0; y < height; y++) {
  let startX = -1;
  for (let x = 0; x < width; x++) {
    const idx = (y * width + x) * 4;
    const alpha = pixels[idx + 3];
    // Threshold alpha > 128
    const isSolid = alpha > 128;
    if (isSolid && startX === -1) {
      startX = x;
    } else if (!isSolid && startX !== -1) {
      rects.push({ x: startX, y, w: x - startX, h: 1 });
      startX = -1;
    }
  }
  if (startX !== -1) {
    rects.push({ x: startX, y, w: width - startX, h: 1 });
  }
}

// Merge vertically adjacent rects with identical x and w
const merged = [];
const used = new Set();
for (let i = 0; i < rects.length; i++) {
  if (used.has(i)) continue;
  let r = { ...rects[i] };
  for (let j = i + 1; j < rects.length; j++) {
    if (used.has(j)) continue;
    if (rects[j].y === r.y + r.h && rects[j].x === r.x && rects[j].w === r.w) {
      r.h += rects[j].h;
      used.add(j);
    }
  }
  merged.push(r);
}

console.log('Merged rects count:', merged.length);

// Generate SVG
const svg = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${width} ${height}" fill="currentColor" width="100%" height="100%" aria-hidden="true">
${merged.map(r => `  <rect x="${r.x}" y="${r.y}" width="${r.w}" height="${r.h}" />`).join('\n')}
</svg>`;

fs.writeFileSync('public/images/support-icon.svg', svg);
console.log('SVG written to public/images/support-icon.svg. Size:', svg.length);
