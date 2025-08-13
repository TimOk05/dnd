@echo off
chcp 65001 > nul
cls

echo 📱 СОЗДАНИЕ ANDROID ПРИЛОЖЕНИЯ
echo ===============================

:: Создание структуры Android проекта
echo 📁 Создание структуры проекта...
if not exist "android-app" mkdir "android-app"
cd android-app

:: Инициализация Cordova проекта
echo 🔧 Инициализация Cordova...
call npx cordova create . com.historicalportrait.app "Исторический портрет"

:: Добавление Android платформы
echo 🤖 Добавление Android платформы...
call npx cordova platform add android

:: Установка плагинов
echo 📦 Установка необходимых плагинов...
call npx cordova plugin add cordova-plugin-camera
call npx cordova plugin add cordova-plugin-file
call npx cordova plugin add cordova-plugin-device
call npx cordova plugin add cordova-plugin-whitelist
call npx cordova plugin add cordova-plugin-statusbar
call npx cordova plugin add cordova-plugin-splashscreen

echo ✅ Android проект создан!
cd ..

echo 📄 Создание Android HTML файла... 