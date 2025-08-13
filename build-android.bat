@echo off
echo 📱 СБОРКА ANDROID ПРИЛОЖЕНИЯ  
echo =============================

:: Создание Android проекта
call create-android-app.bat

:: Копирование HTML файла
copy "android-app.html" "android-app\www\index.html"

cd android-app

:: Сборка APK
echo 🔨 Сборка Android APK...
call npx cordova build android --release

echo ✅ Android APK готов!
echo 📁 Файл: android-app\platforms\android\app\build\outputs\apk\release\app-release-unsigned.apk
cd ..
pause
```
