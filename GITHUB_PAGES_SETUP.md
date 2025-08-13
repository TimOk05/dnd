# 🚀 Настройка AI для GitHub Pages

## 📋 Проблема
GitHub Pages - это статический хостинг, который не поддерживает серверную часть. Поэтому AI функции не работают "из коробки".

## 🔧 Решение: Развертывание API на Vercel

### 1. Подготовка проекта

1. **Создайте аккаунт на [Vercel](https://vercel.com)**
2. **Подключите ваш GitHub репозиторий к Vercel**

### 2. Настройка переменных окружения

В Vercel Dashboard:
1. Перейдите в Settings → Environment Variables
2. Добавьте переменную:
   - **Name**: `DEEPSEEK_API_KEY`
   - **Value**: ваш API ключ DeepSeek
   - **Environment**: Production, Preview, Development

### 3. Развертывание

1. **Push изменения в GitHub**
2. **Vercel автоматически развернет приложение**
3. **Получите URL** (например: `https://your-app.vercel.app`)

### 4. Обновление index.html

Замените URL в файле `index.html`:

```javascript
const API_BASE_URL = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1' 
    ? '' // Локальная разработка
    : 'https://your-app.vercel.app'; // Замените на ваш URL
```

### 5. Альтернативные решения

#### A. Netlify Functions
```bash
# Создайте папку netlify/functions
mkdir -p netlify/functions
```

#### B. Cloudflare Workers
```javascript
// Создайте worker для API
addEventListener('fetch', event => {
  event.respondWith(handleRequest(event.request))
})
```

#### C. Railway
- Подключите репозиторий к Railway
- Настройте переменные окружения
- Получите URL для API

## 🎯 Проверка работы

1. **Откройте ваше приложение на GitHub Pages**
2. **Перейдите на вкладку "AI Подсказки"**
3. **Попробуйте сгенерировать подсказку**
4. **Проверьте чат с DeepSeek**

## 🔍 Отладка

### Проверка API
```bash
curl -X POST https://your-app.vercel.app/api/orchestrate \
  -H "Content-Type: application/json" \
  -d '{"message":"test","isChat":true}'
```

### Проверка переменных окружения
В Vercel Dashboard → Settings → Environment Variables

## 📝 Примеры URL

- **Vercel**: `https://dm-copilot.vercel.app`
- **Netlify**: `https://dm-copilot.netlify.app`
- **Railway**: `https://dm-copilot.railway.app`

## ⚠️ Важные моменты

1. **API ключи** - никогда не коммитьте в репозиторий
2. **CORS** - убедитесь, что API разрешает запросы с GitHub Pages
3. **Лимиты** - следите за лимитами бесплатных планов
4. **Мониторинг** - проверяйте логи в Vercel Dashboard

## 🆘 Если что-то не работает

1. **Проверьте консоль браузера** (F12 → Console)
2. **Проверьте Network tab** - есть ли ошибки API
3. **Проверьте логи Vercel** - Dashboard → Functions
4. **Убедитесь, что API ключ правильный**
5. **Проверьте CORS настройки**

## 🎉 Готово!

После настройки все AI функции будут работать на GitHub Pages! 🚀
