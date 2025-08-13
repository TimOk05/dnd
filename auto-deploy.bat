@echo off
chcp 65001 > nul

echo 🚀 АВТОМАТИЧЕСКИЙ ДЕПЛОЙ НА GITHUB PAGES
echo ========================================

:: Подготовка файлов
call prepare-github-pages.bat

:: Git операции
echo 📤 Загрузка на GitHub...
git add .
git commit -m "🔄 Обновление приложения - %date% %time%"

:: Проверка remote
git remote -v | findstr origin >nul
if %ERRORLEVEL% neq 0 (
    echo ❌ Remote origin не настроен
    echo 💡 Сначала создайте репозиторий на GitHub
    echo 💡 Затем выполните: git remote add origin https://github.com/USERNAME/REPO.git
    pause
    exit /b 1
)

git push origin main

echo.
echo ✅ ДЕПЛОЙ ЗАВЕРШЕН!
echo.
echo 🌐 Ваше приложение будет доступно через 1-2 минуты по адресу:
echo    https://USERNAME.github.io/REPOSITORYNAME/
echo.
echo 📱 Страница установки:
echo    https://USERNAME.github.io/REPOSITORYNAME/install.html
echo.
pause