# 🚀 Настройка AI на Netlify

## 📋 Пошаговая инструкция

### 1. Регистрация на Netlify
1. Перейдите на [netlify.com](https://netlify.com)
2. Нажмите "Sign up" и выберите "Sign up with GitHub"
3. Авторизуйтесь через GitHub

### 2. Подключение репозитория
1. В Netlify Dashboard нажмите "New site from Git"
2. Выберите "GitHub" и найдите ваш репозиторий
3. Настройте параметры сборки:
   - **Build command**: `npm run build` (или оставьте пустым)
   - **Publish directory**: `.` (корневая папка)
4. Нажмите "Deploy site"

### 3. Настройка переменных окружения
1. В Netlify Dashboard перейдите в **Site settings** → **Environment variables**
2. Добавьте переменную:
   - **Key**: `DEEPSEEK_API_KEY`
   - **Value**: ваш API ключ DeepSeek
3. Нажмите "Save"

### 4. Получение URL
1. После деплоя получите URL (например: `https://your-app-123456.netlify.app`)
2. Скопируйте этот URL

### 5. Обновление index.html
Замените URL в файле `index.html` на строке 447:

```javascript
const API_BASE_URL = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' ?
    '' // Локальная разработка
    :
    'https://your-app-123456.netlify.app'; // Ваш Netlify URL
```

### 6. Перезапуск деплоя
1. В Netlify Dashboard нажмите "Trigger deploy" → "Deploy site"
2. Дождитесь завершения деплоя

## 🎯 Проверка работы

1. Откройте ваше приложение на GitHub Pages
2. Перейдите на вкладку "💬 Чат с AI"
3. Напишите сообщение и нажмите "Отправить"
4. Должен прийти ответ от DeepSeek

## 🔍 Отладка

### Проверка функций
1. В Netlify Dashboard перейдите в **Functions**
2. Найдите функцию `api-orchestrate-simple`
3. Проверьте логи на наличие ошибок

### Проверка переменных
1. В **Site settings** → **Environment variables**
2. Убедитесь, что `DEEPSEEK_API_KEY` установлен

### Тест API
```bash
curl -X POST https://your-app.netlify.app/.netlify/functions/api-orchestrate-simple \
  -H "Content-Type: application/json" \
  -d '{"message":"Привет!","isChat":true}'
```

## ⚠️ Важные моменты

1. **API ключ** - никогда не коммитьте в репозиторий
2. **CORS** - уже настроен в функции
3. **Лимиты** - 125,000 запросов/месяц на бесплатном плане
4. **Холодный старт** - первая функция может загружаться медленнее

## 🆘 Если не работает

1. **Проверьте консоль браузера** (F12 → Console)
2. **Проверьте Network tab** - есть ли ошибки API
3. **Проверьте логи Netlify** - Functions → View function logs
4. **Убедитесь, что API ключ правильный**
5. **Проверьте URL в index.html**

## 🎉 Готово!

После настройки все AI функции будут работать на GitHub Pages! 🚀

---

## 🔄 Альтернативы

Если Netlify не подходит, попробуйте:

### Railway
- Перейдите на [railway.app](https://railway.app)
- Подключите GitHub репозиторий
- Настройте переменные окружения
- Получите URL

### Render
- Перейдите на [render.com](https://render.com)
- Создайте Web Service
- Подключите репозиторий
- Настройте переменные

### Cloudflare Workers
- Перейдите на [workers.cloudflare.com](https://workers.cloudflare.com)
- Создайте Worker
- Скопируйте код из `netlify/functions/api-orchestrate-simple.js`
- Настройте переменные
