# 🔧 Исправление проблемы с GitHub Pages

## 🎯 **Проблема:**
GitHub Pages показывает README.md вместо приложения

## ✅ **Решение:**

### **Вариант 1: Переименовать demo.html в index.html**

1. **В вашем репозитории:**
   - Переименуйте `demo.html` в `index.html`
   - Или скопируйте содержимое `demo.html` в новый файл `index.html`

2. **Структура файлов должна быть:**
```
timok05.github.io/dnd/
├── index.html          ← Главная страница
├── README.md           ← Документация
├── demo.html           ← Демо версия
└── другие файлы...
```

### **Вариант 2: Настройка GitHub Pages**

1. **В настройках репозитория:**
   - Settings → Pages
   - Source: Deploy from a branch
   - Branch: main
   - Folder: / (root)

2. **Создать файл `.nojekyll`:**
   - Добавьте пустой файл `.nojekyll` в корень репозитория
   - Это отключит Jekyll и позволит показывать HTML файлы

### **Вариант 3: Использовать ветку gh-pages**

1. **Создать ветку gh-pages:**
```bash
git checkout -b gh-pages
git add .
git commit -m "Add demo app"
git push origin gh-pages
```

2. **В настройках:**
   - Settings → Pages
   - Source: Deploy from a branch
   - Branch: gh-pages
   - Folder: / (root)

## 🚀 **Быстрое исправление:**

1. **Переименуйте файл:**
   - `demo.html` → `index.html`

2. **Добавьте `.nojekyll`:**
   - Создайте пустой файл `.nojekyll`

3. **Обновите репозиторий:**
```bash
git add .
git commit -m "Fix GitHub Pages"
git push
```

4. **Подождите 5-10 минут** - GitHub Pages обновится

## 🎮 **После исправления:**

Ваше приложение будет доступно по адресу:
```
https://timok05.github.io/dnd/
```

Теперь вместо README.md будет открываться ваше приложение! 🎲
