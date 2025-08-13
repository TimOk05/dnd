@echo off
chcp 65001 > nul

echo 📱 БЫСТРАЯ СБОРКА ANDROID APK
echo =============================

echo 🔧 Установка Cordova глобально...
call npm install -g cordova

echo 🚀 Запуск полной сборки...
call build-android-full.bat

echo.
echo ✅ Готово! APK файл создан.
echo 📲 Теперь можете установить на телефон!
pause