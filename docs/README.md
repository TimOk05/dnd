# 🏰 Исторический портрет | Historical Portrait Generator

Веб-приложение для создания реалистичных портретов в образе исторических персонажей эпохи Речи Посполитой и Великого Княжества Литовского с использованием AI технологий.

## 🌟 Особенности

- **10 исторических ролей**: Крылатый гусар, Литовский боярин, Кузнец, Купец, Князь, Писарь, Казак, Монах, Охотник, Ремесленница
- **Сохранение идентичности**: Использование IP-Adapter FaceID для максимального сходства
- **Высокое качество**: Stable Diffusion XL + RealESRGAN + GFPGAN для улучшения
- **Реалистичные эффекты**: Шрамы, пыль, потертости соответствующие эпохе
- **Современный UI**: Красивый интерфейс с анимациями и прогресс-баром
- **Гибкие фоны**: Библиотека исторических фонов для каждой роли

## 🏗️ Архитектура

```
📁 historical-portrait/
├── 📁 app/                    # Next.js Frontend
│   ├── 📁 components/         # React компоненты
│   ├── 📁 services/           # API клиенты
│   ├── 📁 types/              # TypeScript типы
│   └── 📄 page.tsx           # Главная страница
├── 📁 backend/               # FastAPI Backend
│   ├── 📁 models/            # Pydantic модели
│   ├── 📁 services/          # Бизнес-логика
│   ├── 📁 workers/           # RQ воркеры генерации
│   ├── 📄 main.py           # FastAPI приложение
│   └── 📄 config.py         # Конфигурация
└── 📄 docker-compose.yml    # Инфраструктура
```

## 🚀 Быстрый старт

### Требования

- **Python 3.9+** для backend
- **Node.js 18+** для frontend
- **Redis** для очередей задач
- **MinIO/S3** для хранения файлов
- **CUDA GPU** (рекомендуется) для быстрой генерации

### 1. Клонирование репозитория

```bash
git clone <repository-url>
cd historical-portrait
```

### 2. Настройка Backend

```bash
cd backend

# Создание виртуального окружения
python -m venv venv
source venv/bin/activate  # Windows: venv\Scripts\activate

# Установка зависимостей
pip install -r requirements.txt

# Копирование конфигурации
cp env_example.txt .env
# Отредактируйте .env под ваши настройки
```

### 3. Настройка Frontend

```bash
cd app

# Установка зависимостей
npm install

# Создание конфигурации
echo "NEXT_PUBLIC_API_URL=http://localhost:8000" > .env.local
```

### 4. Запуск инфраструктуры

```bash
# Запуск Redis и MinIO
docker-compose up -d redis minio

# Или используйте локальные установки
```

### 5. Запуск приложения

```bash
# Терминал 1: Backend API
cd backend
python main.py

# Терминал 2: RQ Worker
cd backend
rq worker --url redis://localhost:6379

# Терминал 3: Frontend
cd app
npm run dev
```

Приложение будет доступно по адресу: http://localhost:3000

## 🐳 Docker Deployment

### Полный стек с Docker

```bash
# Сборка и запуск всех сервисов
docker-compose up --build

# Или только инфраструктурные сервисы
docker-compose up redis minio
```

### Конфигурация docker-compose.yml

```yaml
version: '3.8'
services:
  frontend:
    build: ./app
    ports:
      - "3000:3000"
    environment:
      - NEXT_PUBLIC_API_URL=http://localhost:8000
    depends_on:
      - backend

  backend:
    build: ./backend
    ports:
      - "8000:8000"
    environment:
      - REDIS_HOST=redis
      - S3_ENDPOINT=http://minio:9000
    depends_on:
      - redis
      - minio

  worker:
    build: ./backend
    command: rq worker --url redis://redis:6379
    environment:
      - REDIS_HOST=redis
      - S3_ENDPOINT=http://minio:9000
    depends_on:
      - redis
      - minio

  redis:
    image: redis:7-alpine
    ports:
      - "6379:6379"

  minio:
    image: minio/minio
    ports:
      - "9000:9000"
      - "9001:9001"
    environment:
      - MINIO_ACCESS_KEY=minioadmin
      - MINIO_SECRET_KEY=minioadmin
    command: server /data --console-address ":9001"
    volumes:
      - minio_data:/data

volumes:
  minio_data:
```

## 🎯 Конфигурация

### Переменные окружения

| Переменная | Описание | По умолчанию |
|------------|----------|--------------|
| `SD_MODEL_PATH` | Путь к Stable Diffusion XL | `stabilityai/stable-diffusion-xl-base-1.0` |
| `SD_DEVICE` | Устройство для генерации | `cuda` |
| `REDIS_HOST` | Адрес Redis | `localhost` |
| `S3_ENDPOINT` | Endpoint S3/MinIO | `http://localhost:9000` |
| `MAX_CONCURRENT_JOBS` | Максимум одновременных задач | `3` |
| `OUTPUT_IMAGE_WIDTH` | Ширина результата | `1080` |
| `OUTPUT_IMAGE_HEIGHT` | Высота результата | `1920` |

### Модели LoRA

Создайте папку `backend/models/lora/` и поместите туда LoRA модели:

```
models/lora/
├── medieval_armor_sdxl.safetensors
├── eastern_europe_attire.safetensors
├── artisan_attire.safetensors
├── merchant_attire.safetensors
├── noble_attire.safetensors
├── scribe_attire.safetensors
├── cossack_attire.safetensors
├── monk_attire.safetensors
├── hunter_attire.safetensors
└── peasant_attire.safetensors
```

**Рекомендуемые источники LoRA:**
- [CivitAI](https://civitai.com) - Medieval, Historical clothing
- [HuggingFace](https://huggingface.co) - SDXL LoRA модели
- Поиск тегов: `medieval`, `historical`, `clothing`, `armor`

## 🌐 Production Deployment

### Frontend (Vercel)

```bash
# Установка Vercel CLI
npm i -g vercel

# Деплой frontend
cd app
vercel --prod

# Настройка переменных окружения в Vercel Dashboard:
# NEXT_PUBLIC_API_URL=https://your-backend-domain.com
```

### Backend (Render/Railway)

1. **Render.com**:
   ```yaml
   # render.yaml
   services:
     - type: web
       name: historical-portrait-api
       env: python
       buildCommand: pip install -r requirements.txt
       startCommand: uvicorn main:app --host 0.0.0.0 --port $PORT
       
     - type: worker
       name: generation-worker
       env: python
       buildCommand: pip install -r requirements.txt
       startCommand: rq worker --url $REDIS_URL
   ```

2. **Railway**:
   ```dockerfile
   # Dockerfile для backend
   FROM python:3.9-slim
   WORKDIR /app
   COPY requirements.txt .
   RUN pip install -r requirements.txt
   COPY . .
   CMD uvicorn main:app --host 0.0.0.0 --port $PORT
   ```

### GPU Inference (RunPod/Replicate)

```python
# runpod_handler.py
import runpod
from workers.generation_worker import process_generation

def handler(event):
    try:
        result = process_generation(event['input'])
        return {"success": True, "result": result}
    except Exception as e:
        return {"success": False, "error": str(e)}

runpod.serverless.start({"handler": handler})
```

## 📊 Мониторинг и логирование

### Статус сервисов

- **API Health**: `GET /health`
- **Redis**: `redis-cli ping`
- **MinIO**: http://localhost:9001 (admin/admin)

### Логи

```bash
# Backend логи
tail -f backend/logs/app.log

# Worker логи
rq info --url redis://localhost:6379

# Frontend логи
npm run dev # или проверить в браузере
```

## 🔧 Разработка

### Добавление новой роли

1. Обновите `HISTORICAL_ROLES` в `backend/models/roles.py`
2. Добавьте соответствующую LoRA модель
3. Обновите иконки в `app/components/RoleSelector.tsx`

### API Endpoints

- `GET /` - Информация о API
- `GET /health` - Статус здоровья
- `GET /presets` - Список ролей
- `POST /upload` - Загрузка изображения
- `POST /generate` - Запуск генерации
- `GET /result/{job_id}` - Статус генерации
- `GET /download/{job_id}` - Скачивание результата

### Структура API ответов

```typescript
// Успешная загрузка
interface UploadResponse {
  success: boolean
  file_id: string
  metadata: FileMetadata
}

// Статус генерации
interface GenerationJob {
  id: string
  status: 'queued' | 'processing' | 'completed' | 'failed'
  progress: number
  result_url?: string
  error_message?: string
}
```

## 🎨 Кастомизация

### Изменение ролей

Отредактируйте массив `HISTORICAL_ROLES` в:
- Backend: `backend/models/roles.py`
- Frontend: `app/types/roles.ts`

### Стили и темы

Настройте цвета в `app/tailwind.config.js`:

```javascript
theme: {
  extend: {
    colors: {
      historical: {
        gold: '#d4af37',
        bronze: '#cd7f32', 
        crimson: '#dc143c',
      }
    }
  }
}
```

## 🐛 Troubleshooting

### Частые проблемы

1. **CUDA Out of Memory**:
   ```python
   # В config.py
   SD_DEVICE = "cpu"  # Переключение на CPU
   ```

2. **Redis Connection Failed**:
   ```bash
   docker-compose up redis
   # или установить Redis локально
   ```

3. **LoRA модели не загружаются**:
   ```bash
   # Проверить папку models/lora/
   ls -la backend/models/lora/
   ```

4. **Медленная генерация**:
   - Используйте GPU вместо CPU
   - Уменьшите `num_inference_steps`
   - Используйте `float16` вместо `float32`

### Логи отладки

```bash
# Включить debug режим
export DEBUG=true

# Детальные логи воркера
rq worker --url redis://localhost:6379 --verbose
```

## 📈 Performance

### Оптимизация

- **GPU Memory**: Используйте `enable_model_cpu_offload()`
- **Batch Processing**: Группируйте запросы
- **Caching**: Кешируйте загруженные модели
- **Load Balancing**: Несколько worker'ов для высокой нагрузки

### Scaling

```yaml
# docker-compose.override.yml для production
services:
  worker:
    deploy:
      replicas: 3  # Несколько воркеров
      
  backend:
    deploy:
      replicas: 2  # Load balancing
```

## 📄 Лицензия

MIT License - используйте свободно для коммерческих и некоммерческих проектов.

## 🤝 Contributing

1. Fork репозиторий
2. Создайте feature branch
3. Сделайте коммит изменений
4. Создайте Pull Request

## 📞 Поддержка

- GitHub Issues для багов и feature requests
- Discord/Telegram для обсуждения разработки
- Email для коммерческих вопросов

---

**Создано с ❤️ для сохранения исторического наследия** 