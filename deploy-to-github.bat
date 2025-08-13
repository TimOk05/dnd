@echo off
echo 📦 Деплой на GitHub Pages

:: Создание статической версии
npm run build

:: Копирование в docs папку для GitHub Pages
if exist "docs" rmdir /s /q "docs"
xcopy "out" "docs" / 