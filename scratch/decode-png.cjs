const fs = require('fs');
const zlib = require('zlib');

const buf = fs.readFileSync('public/images/support-icon.png');
// Find IDAT chunks
let pos = 8;
const idatChunks = [];
let width = 0, height = 0, bitDepth = 0, colorType = 0;

while (pos < buf.length) {
  const length = buf.readUInt32BE(pos);
  const type = buf.toString('ascii', pos + 4, pos + 8);
  if (type === 'IHDR') {
    width = buf.readUInt32BE(pos + 8);
    height = buf.readUInt32BE(pos + 12);
    bitDepth = buf.readUInt8(pos + 16);
    colorType = buf.readUInt8(pos + 17);
  } else if (type === 'IDAT') {
    idatChunks.push(buf.subarray(pos + 8, pos + 8 + length));
  }
  pos += 12 + length;
}

console.log({ width, height, bitDepth, colorType });
const compressed = Buffer.concat(idatChunks);
const raw = zlib.inflateSync(compressed);
console.log('Decompressed raw bytes:', raw.length);
