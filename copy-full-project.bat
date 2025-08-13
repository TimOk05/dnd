@echo off
echo 📦 КОПИРОВАНИЕ ПОЛНОГО ПРОЕКТА

:: Очистка docs
if exist "docs" rmdir /s /q "docs"
mkdir "docs"

:: Копирование исходников (без лишнего)
xcopy "app" "docs\app" /e /i /exclude:node_modules
xcopy "public" "docs\public" /e /i
copy "package.json" "docs\"
copy "next.config.js" "docs\"
copy "tsconfig.json" "docs\"
copy "tailwind.config.js" "docs\"
copy "postcss.config.js" "docs\"
copy "README.md" "docs\"

:: Создание GitHub Action
mkdir "docs\.github\workflows"
(
echo name: Build and Deploy Next.js
echo on:
echo   push:
echo     branches: [ main ]
echo jobs:
echo   build-deploy:
echo     runs-on: ubuntu-latest
echo     steps:
echo     - uses: actions/checkout@v3
echo     - uses: actions/setup-node@v3
echo       with:
echo         node-version: '18'
echo     - run: npm install
echo     - run: npm run build
echo     - uses: peaceiris/actions-gh-pages@v3
echo       with:
echo         github_token: ${{ secrets.GITHUB_TOKEN }}
echo         publish_dir: ./out
) > "docs\.github\workflows\deploy.yml"

echo ✅ Полный проект скопирован в docs
echo 📤 Загрузите на GitHub и включите Actions
pause 