@echo off
chcp 65001 > nul
cls

echo 🧹 ОЧИСТКА ПРОЕКТА ОТ ЛИШНЕГО КОДА
echo ==================================

echo 🗑️  Удаление лишних файлов...

:: Удаление PWA файлов
if exist "install-pwa.bat" del /q "install-pwa.bat"
if exist "quick-start-pwa.bat" del /q "quick-start-pwa.bat"
if exist "pwa-install-guide.html" del /q "pwa-install-guide.html"
if exist "qr-pwa.html" del /q "qr-pwa.html"
if exist "MOBILE_PWA_INSTALL.md" del /q "MOBILE_PWA_INSTALL.md"
if exist "mobile-install.bat" del /q "mobile-install.bat"
if exist "mobile-start.bat" del /q "mobile-start.bat"
if exist "MOBILE_ACCESS.md" del /q "MOBILE_ACCESS.md"
if exist "qr-code.html" del /q "qr-code.html"

:: Удаление APK файлов
if exist "create-apk.bat" del /q "create-apk.bat"
if exist "cleanup-and-build-apk.bat" del /q "cleanup-and-build-apk.bat"
if exist "build-apk.bat" del /q "build-apk.bat"
if exist "quick-apk.bat" del /q "quick-apk.bat"
if exist "APK_ИНСТРУКЦИЯ.md" del /q "APK_ИНСТРУКЦИЯ.md"
if exist "APK_BUILD.md" del /q "APK_BUILD.md"
if exist "create-downloadable-apk.bat" del /q "create-downloadable-apk.bat"
if exist "capacitor.config.json" del /q "capacitor.config.json"

:: Удаление диагностических файлов
if exist "diagnose.bat" del /q "diagnose.bat"
if exist "fix-and-build.bat" del /q "fix-and-build.bat"
if exist "test-web.bat" del /q "test-web.bat"

:: Удаление деплой файлов для других платформ
if exist "deploy-to-vercel.bat" del /q "deploy-to-vercel.bat"
if exist "setup-distribution.bat" del /q "setup-distribution.bat"
if exist "download.html" del /q "download.html"

:: Удаление бэкенд файлов (не нужны для статического сайта)
if exist "docker-compose.yml" del /q "docker-compose.yml"
if exist "start.bat" del /q "start.bat"
if exist "start.sh" del /q "start.sh"
if exist "start-simple.bat" del /q "start-simple.bat"
if exist "backend" rmdir /s /q "backend"

:: Удаление Next.js конфигурации (используем только статический HTML)
if exist "next.config.js" del /q "next.config.js"
if exist "next-env.d.ts" del /q "next-env.d.ts"
if exist "tsconfig.json" del /q "tsconfig.json"
if exist "tailwind.config.js" del /q "tailwind.config.js"
if exist "postcss.config.js" del /q "postcss.config.js"
if exist ".next" rmdir /s /q ".next"
if exist "out" rmdir /s /q "out"
if exist "node_modules" rmdir /s /q "node_modules"
if exist "package.json" del /q "package.json"
if exist "package-lock.json" del /q "package-lock.json"

:: Удаление React компонентов (заменяем на простой HTML)
if exist "app" rmdir /s /q "app"

:: Удаление vscode настроек
if exist ".vscode" rmdir /s /q ".vscode"

:: Удаление скриптов
if exist "scripts" rmdir /s /q "scripts"

:: Удаление лишних файлов из public
if exist "public\generate-icons.html" del /q "public\generate-icons.html"
if exist "public\auto-icons.html" del /q "public\auto-icons.html"
if exist "public\create-simple-icons.html" del /q "public\create-simple-icons.html"

echo ✅ Лишние файлы удалены!

:: Создание чистой структуры
echo 📁 Создание чистой структуры проекта...
if not exist "src" mkdir "src"

echo 📄 Создание основного HTML файла...
(
echo ^<!DOCTYPE html^>
echo ^<html lang="ru"^>
echo ^<head^>
echo     ^<meta charset="UTF-8"^>
echo     ^<meta name="viewport" content="width=device-width, initial-scale=1.0"^>
echo     ^<meta name="description" content="Создавайте исторические портреты в стиле Речи Посполитой и Великого Княжества Литовского"^>
echo     ^<title^>👑 Исторический портрет^</title^>
echo     ^<link rel="manifest" href="manifest.json"^>
echo     ^<meta name="theme-color" content="#d4af37"^>
echo     ^<meta name="apple-mobile-web-app-capable" content="yes"^>
echo     ^<meta name="apple-mobile-web-app-status-bar-style" content="default"^>
echo     ^<meta name="apple-mobile-web-app-title" content="ИсторПортрет"^>
echo     ^<style^>
echo         * { margin: 0; padding: 0; box-sizing: border-box; }
echo         body { 
echo             background: linear-gradient^(135deg, #1e293b, #374151^);
echo             color: white;
echo             font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
echo             min-height: 100vh;
echo             padding: 20px;
echo             overflow-x: hidden;
echo         }
echo         .container {
echo             max-width: 450px;
echo             margin: 0 auto;
echo             background: rgba^(255,255,255,0.1^);
echo             border-radius: 25px;
echo             padding: 30px;
echo             backdrop-filter: blur^(20px^);
echo             border: 1px solid rgba^(255,255,255,0.2^);
echo             box-shadow: 0 20px 40px rgba^(0,0,0,0.3^);
echo         }
echo         .header {
echo             text-align: center;
echo             margin-bottom: 30px;
echo         }
echo         .app-icon {
echo             width: 80px;
echo             height: 80px;
echo             background: linear-gradient^(135deg, #d4af37, #fbbf24^);
echo             border-radius: 20px;
echo             display: flex;
echo             align-items: center;
echo             justify-content: center;
echo             font-size: 40px;
echo             margin: 0 auto 15px;
echo             box-shadow: 0 10px 30px rgba^(212,175,55,0.3^);
echo         }
echo         .upload-area {
echo             border: 3px dashed #d4af37;
echo             border-radius: 20px;
echo             padding: 40px 20px;
echo             text-align: center;
echo             margin: 25px 0;
echo             cursor: pointer;
echo             transition: all 0.3s ease;
echo             position: relative;
echo         }
echo         .upload-area:hover {
echo             background: rgba^(212,175,55,0.1^);
echo             transform: translateY^(-2px^);
echo         }
echo         .upload-area.dragover {
echo             background: rgba^(212,175,55,0.2^);
echo             border-color: #fbbf24;
echo             transform: scale^(1.02^);
echo         }
echo         input[type="file"] {
echo             display: none;
echo         }
echo         .preview {
echo             max-width: 100%%;
echo             max-height: 300px;
echo             border-radius: 15px;
echo             margin: 20px 0;
echo             box-shadow: 0 10px 30px rgba^(0,0,0,0.3^);
echo         }
echo         .roles {
echo             display: grid;
echo             grid-template-columns: repeat^(2, 1fr^);
echo             gap: 15px;
echo             margin: 25px 0;
echo         }
echo         .role-card {
echo             background: rgba^(255,255,255,0.1^);
echo             border-radius: 15px;
echo             padding: 20px 15px;
echo             text-align: center;
echo             cursor: pointer;
echo             transition: all 0.3s ease;
echo             border: 2px solid transparent;
echo             position: relative;
echo         }
echo         .role-card:hover {
echo             background: rgba^(212,175,55,0.2^);
echo             border-color: #d4af37;
echo             transform: translateY^(-3px^);
echo         }
echo         .role-card.selected {
echo             background: rgba^(212,175,55,0.3^);
echo             border-color: #fbbf24;
echo             box-shadow: 0 10px 25px rgba^(212,175,55,0.4^);
echo         }
echo         .role-card .emoji {
echo             font-size: 32px;
echo             margin-bottom: 8px;
echo             display: block;
echo         }
echo         .role-card .name {
echo             font-size: 14px;
echo             font-weight: 600;
echo             line-height: 1.3;
echo         }
echo         .btn {
echo             background: linear-gradient^(135deg, #d4af37, #cd7f32^);
echo             color: #1e293b;
echo             border: none;
echo             padding: 18px 30px;
echo             border-radius: 15px;
echo             font-weight: bold;
echo             cursor: pointer;
echo             width: 100%%;
echo             margin: 15px 0;
echo             font-size: 16px;
echo             transition: all 0.3s ease;
echo         }
echo         .btn:hover {
echo             transform: translateY^(-2px^);
echo             box-shadow: 0 10px 25px rgba^(212,175,55,0.4^);
echo         }
echo         .btn:disabled {
echo             opacity: 0.5;
echo             cursor: not-allowed;
echo             transform: none;
echo         }
echo         .secondary-btn {
echo             background: rgba^(255,255,255,0.1^);
echo             color: white;
echo             border: 2px solid rgba^(255,255,255,0.3^);
echo         }
echo         .secondary-btn:hover {
echo             background: rgba^(255,255,255,0.2^);
echo         }
echo         .progress-bar {
echo             background: rgba^(255,255,255,0.2^);
echo             border-radius: 15px;
echo             height: 25px;
echo             margin: 25px 0;
echo             overflow: hidden;
echo             position: relative;
echo         }
echo         .progress-fill {
echo             background: linear-gradient^(90deg, #d4af37, #fbbf24^);
echo             height: 100%%;
echo             transition: width 0.5s ease;
echo             border-radius: 15px;
echo             position: relative;
echo         }
echo         .progress-fill::after {
echo             content: '';
echo             position: absolute;
echo             top: 0;
echo             left: 0;
echo             right: 0;
echo             bottom: 0;
echo             background: linear-gradient^(90deg, transparent, rgba^(255,255,255,0.3^), transparent^);
echo             animation: shimmer 2s infinite;
echo         }
echo         @keyframes shimmer {
echo             0%% { transform: translateX^(-100%%^); }
echo             100%% { transform: translateX^(100%%^); }
echo         }
echo         .result {
echo             text-align: center;
echo             margin: 25px 0;
echo         }
echo         .result canvas {
echo             max-width: 100%%;
echo             border-radius: 20px;
echo             box-shadow: 0 15px 40px rgba^(0,0,0,0.4^);
echo         }
echo         .hidden { display: none; }
echo         .step { 
echo             margin: 20px 0;
echo             animation: fadeInUp 0.6s ease;
echo         }
echo         @keyframes fadeInUp {
echo             from {
echo                 opacity: 0;
echo                 transform: translateY^(30px^);
echo             }
echo             to {
echo                 opacity: 1;
echo                 transform: translateY^(0^);
echo             }
echo         }
echo         .status-text {
echo             text-align: center;
echo             margin: 15px 0;
echo             font-size: 16px;
echo             font-weight: 600;
echo             color: #fbbf24;
echo         }
echo         .feature-badge {
echo             background: rgba^(34,197,94,0.1^);
echo             border: 1px solid rgba^(34,197,94,0.3^);
echo             border-radius: 20px;
echo             padding: 8px 16px;
echo             font-size: 12px;
echo             color: #10b981;
echo             display: inline-block;
echo             margin: 5px;
echo         }
echo         .install-prompt {
echo             background: rgba^(59,130,246,0.1^);
echo             border: 2px solid rgba^(59,130,246,0.3^);
echo             border-radius: 15px;
echo             padding: 20px;
echo             margin: 20px 0;
echo             text-align: center;
echo         }
echo         .social-share {
echo             display: flex;
echo             gap: 10px;
echo             margin: 20px 0;
echo         }
echo         .social-share button {
echo             flex: 1;
echo             padding: 12px;
echo             border-radius: 10px;
echo             border: none;
echo             cursor: pointer;
echo             font-weight: 600;
echo             transition: all 0.3s ease;
echo         }
echo         .share-download {
echo             background: #10b981;
echo             color: white;
echo         }
echo         .share-link {
echo             background: #3b82f6;
echo             color: white;
echo         }
echo         .share-whatsapp {
echo             background: #25d366;
echo             color: white;
echo         }
echo     ^</style^>
echo ^</head^>
echo ^<body^>
echo     ^<div class="container"^>
echo         ^<div class="header"^>
echo             ^<div class="app-icon"^>👑^</div^>
echo             ^<h1^>Исторический портрет^</h1^>
echo             ^<p^>Создайте портрет в стиле Речи Посполитой^</p^>
echo             ^<div style="margin-top: 15px;"^>
echo                 ^<span class="feature-badge"^>📸 Камера^</span^>
echo                 ^<span class="feature-badge"^>🎭 10 ролей^</span^>
echo                 ^<span class="feature-badge"^>💾 Сохранение^</span^>
echo             ^</div^>
echo         ^</div^>
echo.
echo         ^<!-- Шаг 1: Загрузка фото --^>
echo         ^<div id="step-upload" class="step"^>
echo             ^<h2 style="margin-bottom: 20px; text-align: center;"^>📸 Загрузите ваше фото^</h2^>
echo             ^<div class="upload-area" onclick="document.getElementById('fileInput'^).click^(^)"^>
echo                 ^<div id="upload-text"^>
echo                     ^<p style="font-size: 48px; margin-bottom: 15px;"^>📷^</p^>
echo                     ^<p style="font-size: 16px; margin-bottom: 8px;"^>Нажмите для выбора фото^</p^>
echo                     ^<p style="font-size: 14px; opacity: 0.8;"^>или перетащите файл сюда^</p^>
echo                 ^</div^>
echo                 ^<img id="preview" class="preview hidden" /^>
echo             ^</div^>
echo             ^<input type="file" id="fileInput" accept="image/*" capture="environment"^>
echo             ^<button id="nextToRoles" class="btn hidden"^>Выбрать роль 🎭^</button^>
echo         ^</div^>
echo.
echo         ^<!-- Шаг 2: Выбор роли --^>
echo         ^<div id="step-roles" class="step hidden"^>
echo             ^<h2 style="margin-bottom: 20px; text-align: center;"^>🎭 Выберите историческую роль^</h2^>
echo             ^<div class="roles"^>
echo                 ^<div class="role-card" data-role="hussar"^>
echo                     ^<span class="emoji"^>⚔️^</span^>
echo                     ^<div class="name"^>Крылатый гусар^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="boyar"^>
echo                     ^<span class="emoji"^>🏰^</span^>
echo                     ^<div class="name"^>Литовский боярин^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="prince"^>
echo                     ^<span class="emoji"^>👑^</span^>
echo                     ^<div class="name"^>Князь^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="merchant"^>
echo                     ^<span class="emoji"^>💰^</span^>
echo                     ^<div class="name"^>Купец^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="monk"^>
echo                     ^<span class="emoji"^>⛪^</span^>
echo                     ^<div class="name"^>Монах^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="hunter"^>
echo                     ^<span class="emoji"^>🏹^</span^>
echo                     ^<div class="name"^>Охотник^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="craftswoman"^>
echo                     ^<span class="emoji"^>🧵^</span^>
echo                     ^<div class="name"^>Ремесленница^</div^>
echo                 ^</div^>
echo                 ^<div class="role-card" data-role="blacksmith"^>
echo                     ^<span class="emoji"^>🔨^</span^>
echo                     ^<div class="name"^>Кузнец^</div^>
echo                 ^</div^>
echo             ^</div^>
echo             ^<button id="generateBtn" class="btn" disabled^>Создать портрет 🎨^</button^>
echo             ^<button class="secondary-btn" onclick="goToUpload^(^)"^>⬅️ Назад^</button^>
echo         ^</div^>
echo.
echo         ^<!-- Шаг 3: Генерация --^>
echo         ^<div id="step-generation" class="step hidden"^>
echo             ^<h2 style="text-align: center; margin-bottom: 25px;"^>🎨 Создание портрета...^</h2^>
echo             ^<div class="progress-bar"^>
echo                 ^<div id="progress" class="progress-fill" style="width: 0%%;"^>^</div^>
echo             ^</div^>
echo             ^<p id="status" class="status-text"^>Инициализация...^</p^>
echo         ^</div^>
echo.
echo         ^<!-- Шаг 4: Результат --^>
echo         ^<div id="step-result" class="step hidden"^>
echo             ^<h2 style="text-align: center; margin-bottom: 25px;"^>🎉 Ваш портрет готов!^</h2^>
echo             ^<div class="result"^>
echo                 ^<canvas id="resultCanvas"^>^</canvas^>
echo             ^</div^>
echo             ^<div class="social-share"^>
echo                 ^<button class="share-download" onclick="downloadResult^(^)"^>💾 Сохранить^</button^>
echo                 ^<button class="share-link" onclick="shareResult^(^)"^>📤 Поделиться^</button^>
echo             ^</div^>
echo             ^<button class="btn" onclick="createNew^(^)"^>🔄 Создать новый^</button^>
echo             ^<button class="secondary-btn" onclick="tryAnother^(^)"^>🎲 Другой вариант^</button^>
echo         ^</div^>
echo.
echo         ^<!-- PWA Install Prompt --^>
echo         ^<div id="install-prompt" class="install-prompt hidden"^>
echo             ^<h3 style="margin-bottom: 15px;"^>📱 Установить приложение^</h3^>
echo             ^<p style="margin-bottom: 20px; opacity: 0.9;"^>Добавьте на главный экран для быстрого доступа^</p^>
echo             ^<button class="btn" onclick="installApp^(^)"^>⬇️ Установить сейчас^</button^>
echo         ^</div^>
echo     ^</div^>
echo.
echo     ^<script^>
echo         let uploadedImage = null;
echo         let selectedRole = null;
echo         let deferredPrompt;
echo.
echo         const roles = {
echo             hussar: { name: 'Крылатый гусар', color: '#8B4513' },
echo             boyar: { name: 'Литовский боярин', color: '#654321' },
echo             prince: { name: 'Князь', color: '#4A0E4E' },
echo             merchant: { name: 'Купец', color: '#2F4F4F' },
echo             monk: { name: 'Монах', color: '#2C1810' },
echo             hunter: { name: 'Охотник', color: '#556B2F' },
echo             craftswoman: { name: 'Ремесленница', color: '#8B008B' },
echo             blacksmith: { name: 'Кузнец', color: '#A0522D' }
echo         };
echo.
echo         // PWA Installation
echo         window.addEventListener^('beforeinstallprompt', ^(e^) =^> {
echo             e.preventDefault^(^);
echo             deferredPrompt = e;
echo             document.getElementById^('install-prompt'^).classList.remove^('hidden'^);
echo         }^);
echo.
echo         function installApp^(^) {
echo             if ^(deferredPrompt^) {
echo                 deferredPrompt.prompt^(^);
echo                 deferredPrompt.userChoice.then^(^(choiceResult^) =^> {
echo                     if ^(choiceResult.outcome === 'accepted'^) {
echo                         document.getElementById^('install-prompt'^).classList.add^('hidden'^);
echo                     }
echo                     deferredPrompt = null;
echo                 }^);
echo             }
echo         }
echo.
echo         // File upload
echo         document.getElementById^('fileInput'^).addEventListener^('change', function^(e^) {
echo             const file = e.target.files[0];
echo             if ^(file^) {
echo                 const reader = new FileReader^(^);
echo                 reader.onload = function^(e^) {
echo                     uploadedImage = e.target.result;
echo                     const preview = document.getElementById^('preview'^);
echo                     preview.src = uploadedImage;
echo                     preview.classList.remove^('hidden'^);
echo                     document.getElementById^('upload-text'^).style.display = 'none';
echo                     document.getElementById^('nextToRoles'^).classList.remove^('hidden'^);
echo                 };
echo                 reader.readAsDataURL^(file^);
echo             }
echo         }^);
echo.
echo         // Navigation
echo         document.getElementById^('nextToRoles'^).addEventListener^('click', function^(^) {
echo             document.getElementById^('step-upload'^).classList.add^('hidden'^);
echo             document.getElementById^('step-roles'^).classList.remove^('hidden'^);
echo         }^);
echo.
echo         function goToUpload^(^) {
echo             document.getElementById^('step-roles'^).classList.add^('hidden'^);
echo             document.getElementById^('step-upload'^).classList.remove^('hidden'^);
echo         }
echo.
echo         // Role selection
echo         document.querySelectorAll^('.role-card'^).forEach^(card =^> {
echo             card.addEventListener^('click', function^(^) {
echo                 document.querySelectorAll^('.role-card'^).forEach^(c =^> c.classList.remove^('selected'^)^);
echo                 this.classList.add^('selected'^);
echo                 selectedRole = this.dataset.role;
echo                 document.getElementById^('generateBtn'^).disabled = false;
echo             }^);
echo         }^);
echo.
echo         // Generate portrait
echo         document.getElementById^('generateBtn'^).addEventListener^('click', function^(^) {
echo             document.getElementById^('step-roles'^).classList.add^('hidden'^);
echo             document.getElementById^('step-generation'^).classList.remove^('hidden'^);
echo             generatePortrait^(^);
echo         }^);
echo.
echo         async function generatePortrait^(^) {
echo             const statusEl = document.getElementById^('status'^);
echo             const progressEl = document.getElementById^('progress'^);
echo             
echo             const stages = [
echo                 { progress: 15, text: 'Анализ лица...' },
echo                 { progress: 35, text: 'Подбор исторической одежды...' },
echo                 { progress: 55, text: 'Генерация портрета...' },
echo                 { progress: 75, text: 'Применение эффектов...' },
echo                 { progress: 95, text: 'Финальная обработка...' },
echo                 { progress: 100, text: 'Готово!' }
echo             ];
echo.
echo             for ^(const stage of stages^) {
echo                 statusEl.textContent = stage.text;
echo                 progressEl.style.width = stage.progress + '%%';
echo                 await new Promise^(resolve =^> setTimeout^(resolve, 1200 + Math.random^(^) * 800^)^);
echo             }
echo.
echo             createResult^(^);
echo         }
echo.
echo         function createResult^(^) {
echo             const canvas = document.getElementById^('resultCanvas'^);
echo             const ctx = canvas.getContext^('2d'^);
echo             canvas.width = 400;
echo             canvas.height = 600;
echo.
echo             const role = roles[selectedRole];
echo             
echo             // Background
echo             const gradient = ctx.createLinearGradient^(0, 0, 0, canvas.height^);
echo             gradient.addColorStop^(0, role.color^);
echo             gradient.addColorStop^(1, '#2C1810'^);
echo             ctx.fillStyle = gradient;
echo             ctx.fillRect^(0, 0, canvas.width, canvas.height^);
echo.
echo             // Decorative frame
echo             ctx.strokeStyle = '#d4af37';
echo             ctx.lineWidth = 12;
echo             ctx.strokeRect^(20, 20, canvas.width - 40, canvas.height - 40^);
echo.
echo             // Inner frame
echo             ctx.strokeStyle = '#fbbf24';
echo             ctx.lineWidth = 4;
echo             ctx.strokeRect^(35, 35, canvas.width - 70, canvas.height - 70^);
echo.
echo             // Title
echo             ctx.fillStyle = '#d4af37';
echo             ctx.font = 'bold 32px serif';
echo             ctx.textAlign = 'center';
echo             ctx.fillText^(role.name, canvas.width/2, canvas.height/2 - 50^);
echo.
echo             // Subtitle
echo             ctx.font = '18px serif';
echo             ctx.fillStyle = '#f4e4bc';
echo             ctx.fillText^('XVI-XVII век', canvas.width/2, canvas.height/2 - 15^);
echo             ctx.fillText^('Речь Посполитая', canvas.width/2, canvas.height/2 + 10^);
echo.
echo             // Crown
echo             ctx.fillStyle = '#ffd700';
echo             ctx.font = '80px serif';
echo             ctx.fillText^('👑', canvas.width/2, canvas.height/2 - 120^);
echo.
echo             // Demo watermark
echo             ctx.font = '16px sans-serif';
echo             ctx.fillStyle = 'rgba^(255,255,255,0.6^)';
echo             ctx.fillText^('Демо версия', canvas.width/2, canvas.height - 60^);
echo.
echo             document.getElementById^('step-generation'^).classList.add^('hidden'^);
echo             document.getElementById^('step-result'^).classList.remove^('hidden'^);
echo         }
echo.
echo         // Result actions
echo         function downloadResult^(^) {
echo             const canvas = document.getElementById^('resultCanvas'^);
echo             const link = document.createElement^('a'^);
echo             link.download = `historical-portrait-${roles[selectedRole].name.replace^(/\s+/g, '-'^).toLowerCase^(^)}.png`;
echo             link.href = canvas.toDataURL^(^);
echo             link.click^(^);
echo         }
echo.
echo         function shareResult^(^) {
echo             const canvas = document.getElementById^('resultCanvas'^);
echo             canvas.toBlob^(function^(blob^) {
echo                 if ^(navigator.share && navigator.canShare && navigator.canShare^({files: [blob]}^)^) {
echo                     const file = new File^([blob], 'portrait.png', { type: 'image/png' }^);
echo                     navigator.share^({
echo                         title: 'Мой исторический портрет',
echo                         text: `Я создал портрет в роли "${roles[selectedRole].name}"!`,
echo                         files: [file]
echo                     }^);
echo                 } else {
echo                     // Fallback: copy to clipboard
echo                     canvas.toBlob^(async function^(blob^) {
echo                         try {
echo                             await navigator.clipboard.write^([
echo                                 new ClipboardItem^({ 'image/png': blob }^)
echo                             ]^);
echo                             alert^('Изображение скопировано в буфер обмена!'^);
echo                         } catch ^(err^) {
echo                             downloadResult^(^); // Fallback to download
echo                         }
echo                     }^);
echo                 }
echo             }^);
echo         }
echo.
echo         function createNew^(^) {
echo             location.reload^(^);
echo         }
echo.
echo         function tryAnother^(^) {
echo             if ^(selectedRole^) {
echo                 document.getElementById^('step-result'^).classList.add^('hidden'^);
echo                 document.getElementById^('step-generation'^).classList.remove^('hidden'^);
echo                 generatePortrait^(^);
echo             }
echo         }
echo.
echo         // Drag and drop
echo         const uploadArea = document.querySelector^('.upload-area'^);
echo         uploadArea.addEventListener^('dragover', function^(e^) {
echo             e.preventDefault^(^);
echo             this.classList.add^('dragover'^);
echo         }^);
echo.
echo         uploadArea.addEventListener^('dragleave', function^(e^) {
echo             this.classList.remove^('dragover'^);
echo         }^);
echo.
echo         uploadArea.addEventListener^('drop', function^(e^) {
echo             e.preventDefault^(^);
echo             this.classList.remove^('dragover'^);
echo             const files = e.dataTransfer.files;
echo             if ^(files.length ^> 0^) {
echo                 document.getElementById^('fileInput'^).files = files;
echo                 document.getElementById^('fileInput'^).dispatchEvent^(new Event^('change'^)^);
echo             }
echo         }^);
echo.
echo         // Check if already installed
echo         if ^(window.matchMedia^('^(display-mode: standalone^)'^).matches^) {
echo             console.log^('Приложение уже установлено как PWA'^);
echo         }
echo     ^</script^>
echo ^</body^>
echo ^</html^>
) > "src\index.html"

echo 📋 Создание упрощенного манифеста...
(
echo {
echo   "name": "Исторический портрет",
echo   "short_name": "ИсторПортрет",
echo   "description": "Создавайте исторические портреты в стиле Речи Посполитой",
echo   "start_url": "./index.html",
echo   "display": "standalone",
echo   "background_color": "#1e293b",
echo   "theme_color": "#d4af37",
echo   "orientation": "portrait",
echo   "scope": "./",
echo   "icons": [
echo     {
echo       "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDUxMiA1MTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiIGZpbGw9IiNkNGFmMzciLz48dGV4dCB4PSIyNTYiIHk9IjMyMCIgZm9udC1zaXplPSIyNDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMxZTI5M2IiPvCfkZE8L3RleHQ+PC9zdmc+",
echo       "sizes": "512x512",
echo       "type": "image/svg+xml"
echo     }
echo   ]
echo }
) > "src\manifest.json"

echo 📄 Создание страницы установки...
(
echo ^<!DOCTYPE html^>
echo ^<html lang="ru"^>
echo ^<head^>
echo     ^<meta charset="UTF-8"^>
echo     ^<meta name="viewport" content="width=device-width, initial-scale=1.0"^>
echo     ^<title^>Установка - Исторический портрет^</title^>
echo     ^<style^>
echo         * { margin: 0; padding: 0; box-sizing: border-box; }
echo         body { 
echo             background: linear-gradient^(135deg, #1e293b, #374151^);
echo             color: white;
echo             font-family: -apple-system, sans-serif;
echo             min-height: 100vh;
echo             display: flex;
echo             align-items: center;
echo             justify-content: center;
echo             padding: 20px;
echo         }
echo         .install-card {
echo             max-width: 450px;
echo             background: rgba^(255,255,255,0.1^);
echo             border-radius: 25px;
echo             padding: 40px;
echo             text-align: center;
echo             backdrop-filter: blur^(20px^);
echo         }
echo         .app-icon {
echo             width: 100px;
echo             height: 100px;
echo             background: linear-gradient^(135deg, #d4af37, #fbbf24^);
echo             border-radius: 25px;
echo             display: flex;
echo             align-items: center;
echo             justify-content: center;
echo             font-size: 50px;
echo             margin: 0 auto 25px;
echo         }
echo         .btn {
echo             background: linear-gradient^(135deg, #d4af37, #cd7f32^);
echo             color: #1e293b;
echo             border: none;
echo             padding: 15px 30px;
echo             border-radius: 15px;
echo             font-weight: bold;
echo             cursor: pointer;
echo             width: 100%%;
echo             margin: 15px 0;
echo             font-size: 16px;
echo             text-decoration: none;
echo             display: inline-block;
echo         }
echo         .device-section {
echo             margin: 25px 0;
echo             padding: 20px;
echo             background: rgba^(255,255,255,0.05^);
echo             border-radius: 15px;
echo             text-align: left;
echo         }
echo         .qr-code {
echo             background: white;
echo             padding: 20px;
echo             border-radius: 15px;
echo             margin: 25px 0;
echo             display: inline-block;
echo         }
echo     ^</style^>
echo ^</head^>
echo ^<body^>
echo     ^<div class="install-card"^>
echo         ^<div class="app-icon"^>👑^</div^>
echo         ^<h1^>Исторический портрет^</h1^>
echo         ^<p style="margin: 20px 0;"^>Установите приложение для удобного доступа^</p^>
echo.        
echo         ^<a href="index.html" class="btn"^>🚀 Открыть приложение^</a^>
echo.        
echo         ^<div class="device-section"^>
echo             ^<h3^>📱 Android^</h3^>
echo             ^<p^>1. Откройте в Chrome^</p^>
echo             ^<p^>2. Меню ^(⋮^) → "Добавить на главный экран"^</p^>
echo         ^</div^>
echo.        
echo         ^<div class="device-section"^>
echo             ^<h3^>🍎 iPhone/iPad^</h3^>
echo             ^<p^>1. Откройте в Safari^</p^>
echo             ^<p^>2. "Поделиться" → "Добавить на экран Домой"^</p^>
echo         ^</div^>
echo.        
echo         ^<div class="qr-code"^>
echo             ^<img id="qr" style="width: 200px; height: 200px;" alt="QR код"^>
echo         ^</div^>
echo     ^</div^>
echo.    
echo     ^<script^>
echo         const qr = document.getElementById^('qr'^);
echo         qr.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200^&data=${encodeURIComponent^(window.location.origin^)}`;
echo     ^</script^>
echo ^</body^>
echo ^</html^>
) > "src\install.html"

echo 📖 Создание простого README...
(
echo # 👑 Исторический портрет
echo.
echo Веб-приложение для создания исторических портретов в стиле Речи Посполитой.
echo.
echo ## 🚀 Демо
echo [Открыть приложение](https://yourusername.github.io/historical-portrait/)
echo.
echo ## 📱 Установка
echo [Страница установки](https://yourusername.github.io/historical-portrait/install.html)
echo.
echo ## ✨ Возможности
echo - 📸 Загрузка фото с камеры
echo - 🎭 8 исторических ролей
echo - 💾 Сохранение результата
echo - 📱 PWA поддержка
echo.
echo ## 🛠️ Технологии
echo - HTML5, CSS3, JavaScript
echo - PWA ^(Progressive Web App^)
echo - GitHub Pages
) > "README.md"

echo.
echo ✅ ОЧИСТКА ЗАВЕРШЕНА!
echo ====================
echo.
echo 📁 Создана чистая структура:
echo    src\index.html     - Основное приложение
echo    src\manifest.json  - PWA манифест  
echo    src\install.html   - Страница установки
echo    README.md          - Документация
echo.
echo 🗑️  Удалено:
echo    ❌ Next.js компоненты
echo    ❌ React зависимости
echo    ❌ PWA сложности
echo    ❌ APK файлы
echo    ❌ Backend код
echo    ❌ Диагностические скрипты
echo.
echo 💡 Оставлено только:
echo    ✅ Один HTML файл с приложением
echo    ✅ PWA манифест
echo    ✅ Страница установки
echo    ✅ Скрипты для GitHub Pages
echo.
pause 