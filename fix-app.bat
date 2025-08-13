@echo off
chcp 65001 > nul

echo 🔧 ИСПРАВЛЕНИЕ ПРИЛОЖЕНИЯ
echo =========================

echo 📄 Копирование рабочего файла...
copy "historical-portrait.html" "docs\index.html"

echo 📱 Обновление манифеста...
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
echo       "src": "data:image/svg+xml,%%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%%3E%%3Crect width='100' height='100' fill='%%23d4af37'/%%3E%%3Ctext x='50' y='65' font-size='50' text-anchor='middle'%%3E👑%%3C/text%%3E%%3C/svg%%3E",
echo       "sizes": "192x192",
echo       "type": "image/svg+xml"
echo     },
echo     {
echo       "src": "data:image/svg+xml,%%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%%3E%%3Crect width='100' height='100' fill='%%23d4af37'/%%3E%%3Ctext x='50' y='65' font-size='50' text-anchor='middle'%%3E👑%%3C/text%%3E%%3C/svg%%3E",
echo       "sizes": "512x512", 
echo       "type": "image/svg+xml"
echo     }
echo   ]
echo }
) > "docs\manifest.json"

echo ✅ Файлы исправлены!
echo.
echo 📤 Теперь загрузи обновленные файлы на GitHub:
echo    1. Перейди в свой репозиторий
echo    2. Открой файл index.html  
echo    3. Нажми "Edit" (карандаш)
echo    4. Удали все содержимое
echo    5. Скопируй содержимое из docs\index.html
echo    6. Commit changes
echo.
echo 🌐 Или перезагрузи весь файл docs\index.html
echo.
pause 