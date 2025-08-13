@echo off
chcp 65001 > nul
cls

echo 🚀 БЫСТРЫЙ ДЕПЛОЙ НА GITHUB PAGES
echo =================================

echo 📁 Подготовка файлов для GitHub...
if not exist "docs" mkdir "docs"

:: Копирование чистых файлов
copy "src\index.html" "docs\"
copy "src\manifest.json" "docs\"
copy "src\install.html" "docs\"
copy "README.md" "docs\"

:: Создание .nojekyll
echo. > "docs\.nojekyll"

echo ✅ Файлы готовы в папке 'docs'

echo.
echo 🌐 СЛЕДУЮЩИЕ ШАГИ:
echo.
echo 1️⃣  Создайте репозиторий на GitHub
echo 2️⃣  Загрузите папку 'docs'
echo 3️⃣  Settings → Pages → Source: /docs
echo 4️⃣  Ваше приложение будет доступно!
echo.
echo 📱 Результат:
echo    https://username.github.io/repo-name/
echo.
pause