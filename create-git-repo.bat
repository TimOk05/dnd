@echo off
chcp 65001 > nul

echo 📦 СОЗДАНИЕ GIT РЕПОЗИТОРИЯ
echo ============================

:: Проверка Git
where git >nul 2>nul
if %ERRORLEVEL% neq 0 (
    echo ❌ Git не установлен
    echo 💡 Скачайте Git: https://git-scm.com/download/win
    pause
    exit /b 1
)

:: Инициализация репозитория
echo 🔧 Инициализация Git репозитория...
git init

:: Добавление файлов
echo 📝 Добавление файлов...
git add .

:: Первый коммит
echo 💾 Создание первого коммита...
git commit -m "🎉 Первая версия приложения Исторический портрет"

echo.
echo ✅ GIT РЕПОЗИТОРИЙ СОЗДАН!
echo.
echo 🚀 СЛЕДУЮЩИЙ ШАГ:
echo.
echo 1. Перейдите на https://github.com
echo 2. Создайте новый репозиторий (New repository)
echo 3. Назовите его: historical-portrait
echo 4. НЕ добавляйте README (он уже есть)
echo 5. Создайте репозиторий
echo.
echo 6. Скопируйте команды из раздела "push an existing repository":
echo    git remote add origin https://github.com/USERNAME/historical-portrait.git
echo    git branch -M main  
echo    git push -u origin main
echo.
pause