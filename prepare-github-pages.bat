@echo off
chcp 65001 > nul
cls

echo 📦 ПОДГОТОВКА ДЛЯ GITHUB PAGES
echo ================================

:: Очистка и пересборка
echo 🧹 Очистка старой сборки...
if exist "docs" rmdir /s /q "docs"
if exist "out" rmdir /s /q "out"

:: Сборка приложения
echo 🔨 Сборка приложения...
call npm run build
if %ERRORLEVEL% neq 0 (
    echo ❌ Ошибка сборки
    pause
    exit /b 1
)

:: Создание папки docs для GitHub Pages
echo 📁 Создание папки docs...
mkdir "docs"

:: Копирование собранных файлов
echo 📋 Копирование файлов...
xcopy "out\*" "docs\" /e /i /y

:: Создание главной страницы установки
echo 📄 Создание страницы установки...
(
echo ^<!DOCTYPE html^>
echo ^<html lang="ru"^>
echo ^<head^>
echo     ^<meta charset="UTF-8"^>
echo     ^<meta name="viewport" content="width=device-width, initial-scale=1.0"^>
echo     ^<meta name="description" content="Создавайте исторические портреты в стиле Речи Посполитой и Великого Княжества Литовского"^>
echo     ^<title^>Исторический портрет - Установка^</title^>
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
echo             display: flex;
echo             align-items: center;
echo             justify-content: center;
echo             padding: 20px;
echo         }
echo         .install-container {
echo             max-width: 500px;
echo             background: rgba^(255,255,255,0.1^);
echo             border-radius: 25px;
echo             padding: 50px;
echo             text-align: center;
echo             backdrop-filter: blur^(20px^);
echo             border: 1px solid rgba^(255,255,255,0.2^);
echo             box-shadow: 0 20px 40px rgba^(0,0,0,0.3^);
echo         }
echo         .app-icon {
echo             width: 120px;
echo             height: 120px;
echo             background: linear-gradient^(135deg, #d4af37, #fbbf24^);
echo             border-radius: 25px;
echo             display: flex;
echo             align-items: center;
echo             justify-content: center;
echo             font-size: 60px;
echo             margin: 0 auto 25px;
echo             box-shadow: 0 10px 30px rgba^(212,175,55,0.3^);
echo         }
echo         .main-btn {
echo             background: linear-gradient^(135deg, #d4af37, #cd7f32^);
echo             color: #1e293b;
echo             border: none;
echo             padding: 18px 40px;
echo             border-radius: 15px;
echo             font-weight: bold;
echo             cursor: pointer;
echo             width: 100%%;
echo             margin: 15px 0;
echo             font-size: 18px;
echo             text-decoration: none;
echo             display: inline-block;
echo             transition: all 0.3s ease;
echo         }
echo         .main-btn:hover {
echo             transform: translateY^(-3px^);
echo             box-shadow: 0 10px 25px rgba^(212,175,55,0.4^);
echo         }
echo         .secondary-btn {
echo             background: rgba^(255,255,255,0.1^);
echo             color: white;
echo             border: 2px solid rgba^(255,255,255,0.3^);
echo             padding: 15px 30px;
echo             border-radius: 12px;
echo             font-weight: 600;
echo             cursor: pointer;
echo             width: 100%%;
echo             margin: 10px 0;
echo             font-size: 16px;
echo             text-decoration: none;
echo             display: inline-block;
echo             transition: all 0.3s ease;
echo         }
echo         .secondary-btn:hover {
echo             background: rgba^(255,255,255,0.2^);
echo         }
echo         .device-section {
echo             margin: 25px 0;
echo             padding: 20px;
echo             background: rgba^(255,255,255,0.05^);
echo             border-radius: 15px;
echo             border-left: 4px solid #d4af37;
echo             text-align: left;
echo         }
echo         .device-section h4 {
echo             color: #fbbf24;
echo             margin-bottom: 10px;
echo         }
echo         .install-steps {
echo             font-size: 14px;
echo             line-height: 1.6;
echo             opacity: 0.9;
echo         }
echo         .install-steps li {
echo             margin: 5px 0;
echo         }
echo         .qr-container {
echo             background: white;
echo             padding: 20px;
echo             border-radius: 15px;
echo             display: inline-block;
echo             margin: 20px 0;
echo         }
echo         .url-display {
echo             background: #374151;
echo             padding: 15px;
echo             border-radius: 10px;
echo             font-family: 'Courier New', monospace;
echo             margin: 15px 0;
echo             word-break: break-all;
echo             font-size: 13px;
echo             border: 1px solid rgba^(255,255,255,0.1^);
echo         }
echo         .feature-list {
echo             text-align: left;
echo             margin: 20px 0;
echo             padding: 20px;
echo             background: rgba^(34,197,94,0.1^);
echo             border-radius: 15px;
echo             border: 1px solid rgba^(34,197,94,0.2^);
echo         }
echo         .feature-list h4 {
echo             color: #10b981;
echo             margin-bottom: 15px;
echo             text-align: center;
echo         }
echo         .feature-list ul {
echo             list-style: none;
echo         }
echo         .feature-list li {
echo             margin: 8px 0;
echo             padding-left: 25px;
echo             position: relative;
echo         }
echo         .feature-list li:before {
echo             content: "✅";
echo             position: absolute;
echo             left: 0;
echo         }
echo         #install-prompt {
echo             display: none;
echo             margin: 20px 0;
echo             padding: 15px;
echo             background: rgba^(59,130,246,0.1^);
echo             border: 1px solid rgba^(59,130,246,0.3^);
echo             border-radius: 10px;
echo         }
echo     ^</style^>
echo ^</head^>
echo ^<body^>
echo     ^<div class="install-container"^>
echo         ^<div class="app-icon"^>👑^</div^>
echo         ^<h1 style="margin-bottom: 10px;"^>Исторический портрет^</h1^>
echo         ^<p style="opacity: 0.9; margin-bottom: 30px;"^>Создавайте исторические портреты в стиле Речи Посполитой и Великого Княжества Литовского^</p^>
echo.        
echo         ^<div id="install-prompt"^>
echo             ^<button class="main-btn" onclick="installApp()"^>📱 Установить приложение^</button^>
echo         ^</div^>
echo.        
echo         ^<a href="index.html" class="main-btn"^>🚀 Открыть приложение^</a^>
echo         ^<button class="secondary-btn" onclick="copyCurrentUrl()"^>📋 Скопировать ссылку^</button^>
echo.        
echo         ^<div class="feature-list"^>
echo             ^<h4^>✨ Возможности приложения^</h4^>
echo             ^<ul^>
echo                 ^<li^>Загрузка фото с камеры или галереи^</li^>
echo                 ^<li^>10 исторических ролей на выбор^</li^>
echo                 ^<li^>Красивые анимации и эффекты^</li^>
echo                 ^<li^>Сохранение результата в высоком качестве^</li^>
echo                 ^<li^>Работает офлайн после установки^</li^>
echo                 ^<li^>Поддержка всех устройств^</li^>
echo             ^</ul^>
echo         ^</div^>
echo.        
echo         ^<div class="device-section"^>
echo             ^<h4^>📱 Android^</h4^>
echo             ^<div class="install-steps"^>
echo                 ^<ol^>
echo                     ^<li^>Откройте ссылку в Chrome^</li^>
echo                     ^<li^>Нажмите меню ^(⋮^) → "Добавить на главный экран"^</li^>
echo                     ^<li^>Подтвердите установку^</li^>
echo                 ^</ol^>
echo             ^</div^>
echo         ^</div^>
echo.        
echo         ^<div class="device-section"^>
echo             ^<h4^>🍎 iPhone/iPad^</h4^>
echo             ^<div class="install-steps"^>
echo                 ^<ol^>
echo                     ^<li^>Откройте ссылку в Safari^</li^>
echo                     ^<li^>Нажмите "Поделиться" 📤^</li^>
echo                     ^<li^>Выберите "Добавить на экран Домой"^</li^>
echo                 ^</ol^>
echo             ^</div^>
echo         ^</div^>
echo.        
echo         ^<div class="device-section"^>
echo             ^<h4^>💻 Компьютер^</h4^>
echo             ^<div class="install-steps"^>
echo                 ^<ol^>
echo                     ^<li^>Откройте в Chrome или Edge^</li^>
echo                     ^<li^>В адресной строке появится значок "Установить"^</li^>
echo                     ^<li^>Или нажмите Ctrl+Shift+A^</li^>
echo                 ^</ol^>
echo             ^</div^>
echo         ^</div^>
echo.        
echo         ^<div class="qr-container"^>
echo             ^<img id="qr-code" style="width: 200px; height: 200px;" alt="QR код для установки"^>
echo         ^</div^>
echo         ^<p style="font-size: 14px; opacity: 0.8;"^>Отсканируйте QR код для быстрого доступа^</p^>
echo.        
echo         ^<div class="url-display" id="current-url"^>
echo             Загрузка ссылки...
echo         ^</div^>
echo     ^</div^>
echo.    
echo     ^<script^>
echo         // PWA Installation
echo         let deferredPrompt;
echo         
echo         window.addEventListener^('beforeinstallprompt', ^(e^) =^> {
echo             e.preventDefault^(^);
echo             deferredPrompt = e;
echo             document.getElementById^('install-prompt'^).style.display = 'block';
echo         }^);
echo.        
echo         function installApp^(^) {
echo             if ^(deferredPrompt^) {
echo                 deferredPrompt.prompt^(^);
echo                 deferredPrompt.userChoice.then^(^(choiceResult^) =^> {
echo                     if ^(choiceResult.outcome === 'accepted'^) {
echo                         console.log^('Приложение установлено'^);
echo                         document.getElementById^('install-prompt'^).style.display = 'none';
echo                     }
echo                     deferredPrompt = null;
echo                 }^);
echo             } else {
echo                 // Fallback instructions
echo                 alert^('Для установки:\n1. Откройте меню браузера\n2. Выберите "Добавить на главный экран"\n3. Подтвердите установку'^);
echo             }
echo         }
echo.        
echo         function copyCurrentUrl^(^) {
echo             const url = window.location.href;
echo             navigator.clipboard.writeText^(url^).then^(^(^) =^> {
echo                 alert^('✅ Ссылка скопирована в буфер обмена!'^);
echo             }^).catch^(^(^) =^> {
echo                 // Fallback for older browsers
echo                 const textArea = document.createElement^('textarea'^);
echo                 textArea.value = url;
echo                 document.body.appendChild^(textArea^);
echo                 textArea.select^(^);
echo                 document.execCommand^('copy'^);
echo                 document.body.removeChild^(textArea^);
echo                 alert^('✅ Ссылка скопирована!'^);
echo             }^);
echo         }
echo.        
echo         // Update URL and QR code
echo         window.addEventListener^('load', ^(^) =^> {
echo             const currentUrl = window.location.href;
echo             document.getElementById^('current-url'^).textContent = currentUrl;
echo             
echo             // Generate QR code
echo             const qrImg = document.getElementById^('qr-code'^);
echo             qrImg.src = `https://api.qrserver.com/v1/create-qr-code/?size=200x200^&data=${encodeURIComponent^(currentUrl^)}`;
echo         }^);
echo.        
echo         // Check if already installed
echo         if ^(window.matchMedia^('^(display-mode: standalone^)'^).matches^) {
echo             console.log^('Приложение уже установлено'^);
echo         }
echo     ^</script^>
echo ^</body^>
echo ^</html^>
) > "docs\install.html"

:: Создание PWA манифеста
echo 📱 Создание PWA манифеста...
(
echo {
echo   "name": "Исторический портрет",
echo   "short_name": "ИсторПортрет",
echo   "description": "Создавайте исторические портреты в стиле Речи Посполитой и Великого Княжества Литовского",
echo   "start_url": "./index.html",
echo   "display": "standalone",
echo   "background_color": "#1e293b",
echo   "theme_color": "#d4af37",
echo   "orientation": "portrait",
echo   "scope": "./",
echo   "categories": ["photo", "entertainment", "lifestyle"],
echo   "lang": "ru",
echo   "icons": [
echo     {
echo       "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDUxMiA1MTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiIGZpbGw9IiNkNGFmMzciLz48dGV4dCB4PSIyNTYiIHk9IjMyMCIgZm9udC1zaXplPSIyNDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMxZTI5M2IiPvCfkZE8L3RleHQ+PC9zdmc+",
echo       "sizes": "192x192",
echo       "type": "image/svg+xml",
echo       "purpose": "any"
echo     },
echo     {
echo       "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDUxMiA1MTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PHJlY3Qgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiIGZpbGw9IiNkNGFmMzciLz48dGV4dCB4PSIyNTYiIHk9IjMyMCIgZm9udC1zaXplPSIyNDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMxZTI5M2IiPvCfkZE8L3RleHQ+PC9zdmc+",
echo       "sizes": "512x512",
echo       "type": "image/svg+xml",
echo       "purpose": "any"
echo     },
echo     {
echo       "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgdmlld0JveD0iMCAwIDUxMiA1MTIiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+PGRlZnM+PG1hc2sgaWQ9Im1hc2siPjxyZWN0IHdpZHRoPSI1MTIiIGhlaWdodD0iNTEyIiBmaWxsPSJ3aGl0ZSIgcng9IjEwMCIvPjwvbWFzaz48L2RlZnM+PHJlY3Qgd2lkdGg9IjUxMiIgaGVpZ2h0PSI1MTIiIGZpbGw9IiNkNGFmMzciIG1hc2s9InVybCgjbWFzaykiLz48dGV4dCB4PSIyNTYiIHk9IjMyMCIgZm9udC1zaXplPSIyNDAiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiMxZTI5M2IiPvCfkZE8L3RleHQ+PC9zdmc+",
echo       "sizes": "512x512",
echo       "type": "image/svg+xml",
echo       "purpose": "maskable"
echo     }
echo   ],
echo   "shortcuts": [
echo     {
echo       "name": "Создать портрет",
echo       "short_name": "Создать",
echo       "description": "Быстро создать новый исторический портрет",
echo       "url": "./index.html",
echo       "icons": [
echo         {
echo           "src": "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iOTYiIGhlaWdodD0iOTYiIHZpZXdCb3g9IjAgMCA5NiA5NiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iOTYiIGhlaWdodD0iOTYiIGZpbGw9IiNkNGFmMzciIHJ4PSIyMCIvPjx0ZXh0IHg9IjQ4IiB5PSI2NSIgZm9udC1zaXplPSI0OCIgdGV4dC1hbmNob3I9Im1pZGRsZSI+8J+RkTwvdGV4dD48L3N2Zz4=",
echo           "sizes": "96x96"
echo         }
echo       ]
echo     }
echo   ]
echo }
) > "docs\manifest.json"

:: Создание главной страницы (переименование index.html)
if exist "docs\index.html" (
    echo ✅ Главная страница уже существует
) else (
    echo ❌ Главная страница не найдена, копирую из приложения
    copy "historical-portrait.html" "docs\index.html"
)

:: Создание файла .nojekyll для GitHub Pages
echo. > "docs\.nojekyll"

:: Создание README для GitHub
echo 📖 Создание README...
(
echo # 👑 Исторический портрет
echo.
echo Веб-приложение для создания исторических портретов в стиле Речи Посполитой и Великого Княжества Литовского.
echo.
echo ## 🚀 Демо
echo.
echo **[Открыть приложение](https://yourusername.github.io/historical-portrait/)**
echo.
echo ## 📱 Установка
echo.
echo **[Страница установки](https://yourusername.github.io/historical-portrait/install.html)**
echo.
echo ### Android
echo 1. Откройте ссылку в Chrome
echo 2. Меню ^(⋮^) → "Добавить на главный экран"
echo 3. Подтвердите установку
echo.
echo ### iPhone/iPad  
echo 1. Откройте ссылку в Safari
echo 2. Нажмите "Поделиться" 📤
echo 3. "Добавить на экран Домой"
echo.
echo ### Компьютер
echo 1. Откройте в Chrome/Edge
echo 2. Нажмите "Установить" в адресной строке
echo 3. Или Ctrl+Shift+A
echo.
echo ## ✨ Возможности
echo.
echo - 📸 **Загрузка фото** с камеры или галереи
echo - 🎭 **10 исторических ролей** на выбор
echo - 🎨 **Красивые анимации** и эффекты
echo - 💾 **Сохранение результата** в высоком качестве
echo - 📱 **PWA поддержка** - работает как нативное приложение
echo - 🌐 **Офлайн режим** после установки
echo - 🔄 **Кроссплатформенность** - все устройства
echo.
echo ## 🛠️ Технологии
echo.
echo - **Frontend**: HTML5, CSS3, JavaScript ^(ES6+^)
echo - **PWA**: Service Worker, Web App Manifest
echo - **UI/UX**: Responsive Design, CSS Grid/Flexbox
echo - **Hosting**: GitHub Pages
echo.
echo ## 📄 Лицензия
echo.
echo MIT License
) > "README.md"

echo.
echo ✅ ПОДГОТОВКА ЗАВЕРШЕНА!
echo ========================
echo.
echo 📁 Файлы готовы в папке 'docs'
echo 📱 Создана страница установки: docs\install.html
echo 🌐 Главная страница: docs\index.html
echo 📋 PWA манифест: docs\manifest.json
echo.
echo 🚀 СЛЕДУЮЩИЕ ШАГИ:
echo.
echo 1️⃣  Создайте репозиторий на GitHub
echo 2️⃣  Загрузите все файлы из этой папки
echo 3️⃣  Включите GitHub Pages в настройках
echo 4️⃣  Ваше приложение будет доступно по адресу:
echo    https://yourusername.github.io/repositoryname/
echo.
echo 📖 Подробная инструкция будет показана далее...
echo.
pause 