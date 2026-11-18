const fs = require('fs');
const b64 = fs.readFileSync('public/images/support-icon.png').toString('base64');
const html = `<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
  body { background: #0a0a0f; color: white; font-family: sans-serif; padding: 40px; }
  .nfa-icon {
    display: inline-block;
    width: 26px;
    height: 26px;
    background-color: currentColor;
    -webkit-mask: url('data:image/png;base64,${b64}') no-repeat center / contain;
    mask: url('data:image/png;base64,${b64}') no-repeat center / contain;
  }
  .launcher-btn {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    background: linear-gradient(145deg, #1d1d26, #121218);
    border: 1.5px solid #262634;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    box-shadow: 0 10px 30px rgba(0,0,0,0.5), 0 0 0 1px rgba(223,31,64,0.3);
    cursor: pointer;
    transition: all 0.25s ease;
  }
  .launcher-btn:hover {
    color: #DF1F40;
    border-color: #DF1F40;
    box-shadow: 0 10px 30px rgba(223,31,64,0.35), 0 0 0 2px rgba(223,31,64,0.25);
  }
  .header-avatar {
    width: 36px;
    height: 36px;
    border-radius: 8px;
    background: #1b1b24;
    border: 1px solid rgba(223,31,64,0.3);
    display: inline-flex;
    align-items: center;
    justify-content: center;
    color: #DF1F40;
  }
</style>
</head>
<body>
  <h2>Support Icon Test</h2>
  <div style="display:flex; gap: 20px; align-items:center;">
    <div class="launcher-btn">
      <span class="nfa-icon"></span>
    </div>
    <div class="header-avatar">
      <span class="nfa-icon" style="width: 20px; height: 20px;"></span>
    </div>
  </div>
</body>
</html>`;

fs.writeFileSync('public/test-icon.html', html);
console.log('Saved public/test-icon.html successfully');
