import { readFileSync } from 'node:fs'
import { fileURLToPath } from 'node:url'

// Forms4U splash shown by `nuxt dev` while the app is still compiling
// (replaces the default Nuxt logo screen). Reloads until the app is ready.
const iconPath = fileURLToPath(new URL('../../public/img/logo.png', import.meta.url))
let icon = ''
try {
  icon = `data:image/png;base64,${readFileSync(iconPath).toString('base64')}`
}
catch {
  icon = ''
}

export default () => `<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Carregando… - Forms4U</title>
<style>
  html, body { margin: 0; height: 100%; }
  body {
    display: flex; align-items: center; justify-content: center;
    font-family: system-ui, -apple-system, "Segoe UI", sans-serif;
    background: #f6f4f5;
    background-image: radial-gradient(ellipse 70% 50% at 50% 0%, rgba(100, 14, 44, 0.14), transparent 70%);
    color: #4a031d;
  }
  .box { text-align: center; }
  img { width: 88px; height: 88px; animation: float 2.4s ease-in-out infinite; }
  .name { margin-top: 18px; font-size: 22px; font-weight: 600; letter-spacing: -0.01em; }
  .bar { margin: 18px auto 0; width: 140px; height: 4px; border-radius: 99px; background: #f3ced4; overflow: hidden; }
  .bar::after { content: ""; display: block; width: 40%; height: 100%; border-radius: 99px; background: #640e2c; animation: slide 1.1s ease-in-out infinite; }
  .hint { margin-top: 14px; font-size: 13px; color: #8c2c48; opacity: .8; }
  @keyframes float { 0%, 100% { transform: translateY(0) rotate(-3deg); } 50% { transform: translateY(-8px) rotate(3deg); } }
  @keyframes slide { 0% { transform: translateX(-100%); } 100% { transform: translateX(250%); } }
  @media (prefers-reduced-motion: reduce) { img, .bar::after { animation: none; } }
</style>
</head>
<body>
  <div class="box">
    ${icon ? `<img src="${icon}" alt="">` : ''}
    <div class="name">Forms4U</div>
    <div class="bar"></div>
    <div class="hint">Preparando o sistema…</div>
  </div>
  <script>setTimeout(function () { location.reload() }, 2000)</script>
</body>
</html>`
