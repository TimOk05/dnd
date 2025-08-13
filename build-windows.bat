@echo off
echo 🪟 СБОРКА WINDOWS ПРИЛОЖЕНИЯ
echo ============================

:: Создание папки для Windows версии
if not exist "windows-build" mkdir "windows-build"
cd windows-build

:: Копирование файлов
copy "..\electron-package.json" "package.json"
copy "..\main.js" "."
copy "..\windows-app.html" "index.html"

:: Создание папки assets
if not exist "assets" mkdir "assets"

:: Установка зависимостей
echo 📦 Установка зависимостей...
call npm install

:: Сборка приложения
echo 🔨 Сборка Windows приложения...
call npm run build-win

cd ..

echo ✅ Windows приложение готово!
echo 📁 Файл: windows-build\dist\Исторический портрет Setup 1.0.0.exe
pause 