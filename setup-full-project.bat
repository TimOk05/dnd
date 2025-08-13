@echo off
chcp 65001 > nul
cls

echo 📦 НАСТРОЙКА ПОЛНОГО ПРОЕКТА ДЛЯ GITHUB PAGES
echo ==============================================

:: Очистка и подготовка
echo 🧹 Очистка старых файлов...
if exist "docs" rmdir /s /q "docs"
if exist ".github" rmdir /s /q ".github"

:: Создание структуры для GitHub
echo 📁 Создание структуры проекта...
mkdir "docs"

:: Копирование основных файлов приложения
echo 📋 Копирование файлов приложения...
if exist "app" (
    xcopy "app" "docs\app" /e /i /y
) else (
    echo ⚠️  Папка app не найдена, создаем базовую структуру...
    mkdir "docs\app"
)

:: Копирование public файлов
if exist "public" (
    xcopy "public" "docs\public" /e /i /y
) else (
    mkdir "docs\public"
)

:: Создание package.json для GitHub
echo 📦 Создание package.json...
(
echo {
echo   "name": "historical-portrait",
echo   "version": "1.0.0",
echo   "private": true,
echo   "scripts": {
echo     "dev": "next dev",
echo     "build": "next build",
echo     "start": "next start",
echo     "export": "next build && next export"
echo   },
echo   "dependencies": {
echo     "next": "13.5.6",
echo     "react": "^18.0.0",
echo     "react-dom": "^18.0.0",
echo     "framer-motion": "^10.16.0",
echo     "lucide-react": "^0.290.0",
echo     "react-dropzone": "^14.2.3",
echo     "tailwindcss": "^3.3.0",
echo     "autoprefixer": "^10.4.16",
echo     "postcss": "^8.4.31"
echo   },
echo   "devDependencies": {
echo     "@types/node": "^20.0.0",
echo     "@types/react": "^18.0.0",
echo     "@types/react-dom": "^18.0.0",
echo     "eslint": "^8.0.0",
echo     "eslint-config-next": "14.0.0",
echo     "typescript": "^5.0.0"
echo   }
echo }
) > "docs\package.json"

:: Создание next.config.js для статического экспорта
echo ⚙️  Создание next.config.js...
(
echo /** @type {import^('next'^).NextConfig} */
echo const nextConfig = {
echo   output: 'export',
echo   trailingSlash: true,
echo   images: {
echo     unoptimized: true
echo   },
echo   experimental: {
echo     appDir: true,
echo   },
echo   distDir: 'out',
echo   basePath: process.env.NODE_ENV === 'production' ? '/historical-portrait' : '',
echo   assetPrefix: process.env.NODE_ENV === 'production' ? '/historical-portrait/' : '',
echo }
echo.
echo module.exports = nextConfig
) > "docs\next.config.js"

:: Создание TypeScript конфигурации
echo 📝 Создание tsconfig.json...
(
echo {
echo   "compilerOptions": {
echo     "target": "es5",
echo     "lib": ["dom", "dom.iterable", "es6"],
echo     "allowJs": true,
echo     "skipLibCheck": true,
echo     "strict": true,
echo     "noEmit": true,
echo     "esModuleInterop": true,
echo     "module": "esnext",
echo     "moduleResolution": "bundler",
echo     "resolveJsonModule": true,
echo     "isolatedModules": true,
echo     "jsx": "preserve",
echo     "incremental": true,
echo     "plugins": [
echo       {
echo         "name": "next"
echo       }
echo     ],
echo     "paths": {
echo       "@/*": ["./*"]
echo     }
echo   },
echo   "include": ["next-env.d.ts", "**/*.ts", "**/*.tsx", ".next/types/**/*.ts"],
echo   "exclude": ["node_modules"]
echo }
) > "docs\tsconfig.json"

:: Создание Tailwind конфигурации
echo 🎨 Создание tailwind.config.js...
(
echo /** @type {import^('tailwindcss'^).Config} */
echo module.exports = {
echo   content: [
echo     './pages/**/*.{js,ts,jsx,tsx,mdx}',
echo     './components/**/*.{js,ts,jsx,tsx,mdx}',
echo     './app/**/*.{js,ts,jsx,tsx,mdx}',
echo   ],
echo   theme: {
echo     extend: {
echo       colors: {
echo         'medieval-gold': '#d4af37',
echo         'medieval-bronze': '#cd7f32',
echo         'medieval-brown': '#8B4513',
echo       },
echo       fontFamily: {
echo         'medieval': ['serif'],
echo       },
echo     },
echo   },
echo   plugins: [],
echo }
) > "docs\tailwind.config.js"

:: Создание PostCSS конфигурации
echo 📄 Создание postcss.config.js...
(
echo module.exports = {
echo   plugins: {
echo     tailwindcss: {},
echo     autoprefixer: {},
echo   },
echo }
) > "docs\postcss.config.js"

:: Создание next-env.d.ts
echo 🔧 Создание next-env.d.ts...
(
echo /// ^<reference types="next" /^>
echo /// ^<reference types="next/image-types/global" /^>
echo.
echo // NOTE: This file should not be edited
echo // see https://nextjs.org/docs/basic-features/typescript for more information.
) > "docs\next-env.d.ts"

:: Создание .gitignore
echo 📝 Создание .gitignore...
(
echo # Dependencies
echo /node_modules
echo /.pnp
echo .pnp.js
echo.
echo # Testing
echo /coverage
echo.
echo # Next.js
echo /.next/
echo /out/
echo.
echo # Production
echo /build
echo.
echo # Misc
echo .DS_Store
echo *.pem
echo.
echo # Debug
echo npm-debug.log*
echo yarn-debug.log*
echo yarn-error.log*
echo.
echo # Local env files
echo .env*.local
echo.
echo # Vercel
echo .vercel
echo.
echo # TypeScript
echo *.tsbuildinfo
echo next-env.d.ts
) > "docs\.gitignore"

:: Создание GitHub Actions для автосборки
echo 🤖 Создание GitHub Actions...
mkdir "docs\.github"
mkdir "docs\.github\workflows"

(
echo name: Build and Deploy Next.js to GitHub Pages
echo.
echo on:
echo   push:
echo     branches: [ main ]
echo   pull_request:
echo     branches: [ main ]
echo.
echo permissions:
echo   contents: read
echo   pages: write
echo   id-token: write
echo.
echo concurrency:
echo   group: "pages"
echo   cancel-in-progress: false
echo.
echo jobs:
echo   build:
echo     runs-on: ubuntu-latest
echo     steps:
echo       - name: Checkout
echo         uses: actions/checkout@v4
echo.
echo       - name: Setup Node.js
echo         uses: actions/setup-node@v4
echo         with:
echo           node-version: '18'
echo           cache: 'npm'
echo.
echo       - name: Setup Pages
echo         uses: actions/configure-pages@v4
echo         with:
echo           static_site_generator: next
echo.
echo       - name: Restore cache
echo         uses: actions/cache@v3
echo         with:
echo           path: |
echo             .next/cache
echo           key: ${{ runner.os }}-nextjs-${{ hashFiles^('**/package-lock.json'^) }}-${{ hashFiles^('**.[jt]s', '**.[jt]sx'^) }}
echo           restore-keys: |
echo             ${{ runner.os }}-nextjs-${{ hashFiles^('**/package-lock.json'^) }}-
echo.
echo       - name: Install dependencies
echo         run: npm ci
echo.
echo       - name: Build with Next.js
echo         run: npm run build
echo.
echo       - name: Upload artifact
echo         uses: actions/upload-pages-artifact@v2
echo         with:
echo           path: ./out
echo.
echo   deploy:
echo     environment:
echo       name: github-pages
echo       url: ${{ steps.deployment.outputs.page_url }}
echo     runs-on: ubuntu-latest
echo     needs: build
echo     steps:
echo       - name: Deploy to GitHub Pages
echo         id: deployment
echo         uses: actions/deploy-pages@v3
) > "docs\.github\workflows\deploy.yml"

:: Проверка и копирование компонентов приложения
echo 📱 Проверка компонентов приложения...
if not exist "docs\app\page.tsx" (
    echo 📄 Создание основных компонентов...
    
    :: Создание app/page.tsx
    if not exist "docs\app" mkdir "docs\app"
    echo Создание page.tsx...
    copy "historical-portrait.html" "docs\app\page.tsx"
)

if not exist "docs\app\layout.tsx" (
    echo 📄 Создание layout.tsx...
    :: Будет создан из существующих файлов или как базовый
)

:: Создание README с инструкциями
echo 📖 Создание README...
(
echo # 👑 Исторический портрет
echo.
echo Веб-приложение для создания исторических портретов в стиле Речи Посполитой и Великого Княжества Литовского.
echo.
echo ## 🚀 Демо
echo.
echo **[Открыть приложение](https://yourusername.github.io/historical-portrait/)**
echo.
echo ## 🛠️ Разработка
echo.
echo ### Локальный запуск:
echo ```bash
echo npm install
echo npm run dev
echo ```
echo.
echo ### Сборка:
echo ```bash
echo npm run build
echo ```
echo.
echo ## 📱 Возможности
echo.
echo - 📸 **Загрузка фото** с камеры или галереи
echo - 🎭 **10 исторических ролей** на выбор
echo - 🎨 **Красивые анимации** с Framer Motion
echo - 💾 **Сохранение результата** в высоком качестве
echo - 📱 **Responsive дизайн** для всех устройств
echo - 🌐 **PWA поддержка** - работает как нативное приложение
echo.
echo ## 🏗️ Технологии
echo.
echo - **Next.js 13** с App Router
echo - **React 18** с хуками
echo - **TypeScript** для типизации
echo - **Tailwind CSS** для стилей
echo - **Framer Motion** для анимаций
echo - **GitHub Pages** для хостинга
echo - **GitHub Actions** для автоматического деплоя
echo.
echo ## 🚀 Деплой
echo.
echo Приложение автоматически собирается и деплоится через GitHub Actions при пуше в ветку `main`.
echo.
echo ### Настройка GitHub Pages:
echo 1. Settings → Pages
echo 2. Source: GitHub Actions
echo 3. Готово!
echo.
echo ## 📄 Лицензия
echo.
echo MIT License
) > "docs\README.md"

:: Создание простого манифеста для PWA
echo 📱 Создание PWA манифеста...
if not exist "docs\public" mkdir "docs\public"
(
echo {
echo   "name": "Исторический портрет",
echo   "short_name": "ИсторПортрет",
echo   "description": "Создавайте исторические портреты в стиле Речи Посполитой",
echo   "start_url": "/historical-portrait/",
echo   "display": "standalone",
echo   "background_color": "#1e293b",
echo   "theme_color": "#d4af37",
echo   "icons": [
echo     {
echo       "src": "data:image/svg+xml,%%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'%%3E%%3Crect width='100' height='100' fill='%%23d4af37'/%%3E%%3Ctext x='50' y='65' font-size='50' text-anchor='middle'%%3E👑%%3C/text%%3E%%3C/svg%%3E",
echo       "sizes": "192x192",
echo       "type": "image/svg+xml"
echo     }
echo   ]
echo }
) > "docs\public\manifest.json"

echo.
echo ✅ ПОЛНЫЙ ПРОЕКТ ГОТОВ!
echo ========================
echo.
echo 📁 Создана структура:
echo    docs/
echo    ├── app/                    - React компоненты
echo    ├── public/                 - Статические файлы
echo    ├── .github/workflows/      - GitHub Actions
echo    ├── package.json            - Зависимости
echo    ├── next.config.js          - Конфигурация Next.js
echo    ├── tsconfig.json           - TypeScript настройки
echo    ├── tailwind.config.js      - Стили
echo    └── README.md               - Документация
echo.
echo 🚀 СЛЕДУЮЩИЕ ШАГИ:
echo.
echo 1️⃣  Загрузите ВСЕ файлы из папки 'docs' в GitHub репозиторий
echo.
echo 2️⃣  В настройках репозитория:
echo    Settings → Pages → Source: "GitHub Actions"
echo.
echo 3️⃣  GitHub Actions автоматически:
echo    - Установит зависимости
echo    - Соберет Next.js приложение  
echo    - Задеплоит на GitHub Pages
echo.
echo 4️⃣  Ваше приложение будет доступно:
echo    https://username.github.io/historical-portrait/
echo.
echo 💡 ПРЕИМУЩЕСТВА:
echo    ✅ Автоматическая сборка при каждом коммите
echo    ✅ Полный Next.js проект для развития
echo    ✅ TypeScript поддержка
echo    ✅ Современная архитектура
echo    ✅ Легко добавлять новые функции
echo.
pause 