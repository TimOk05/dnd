// ===== ПРОСТОЙ МОБИЛЬНЫЙ ИНТЕРФЕЙС =====

// Определяем мобильное устройство
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

// Инициализация мобильных функций
document.addEventListener('DOMContentLoaded', function() {
    if (isMobile || isTouch) {
        initSimpleMobile();
    }
});

function initSimpleMobile() {
    // Добавляем класс для мобильных устройств
    document.body.classList.add('mobile-device');

    // Простое исправление лайаута
    fixSimpleLayout();

    // Инициализируем пролистывание
    initSmoothScrolling();

    // Инициализируем мобильные формы
    initMobileForms();
}

// ===== ПРОСТОЕ ИСПРАВЛЕНИЕ ЛАЙАУТА =====

function fixSimpleLayout() {
    // Переключатель темы - простое позиционирование
    const themeToggle = document.querySelector('.theme-toggle');
    if (themeToggle) {
        themeToggle.style.position = 'fixed';
        themeToggle.style.top = '20px';
        themeToggle.style.right = '20px';
        themeToggle.style.zIndex = '1000';
    }

    // Админ-ссылка - рядом с переключателем темы
    const adminLink = document.querySelector('.admin-link');
    if (adminLink) {
        adminLink.style.position = 'fixed';
        adminLink.style.top = '20px';
        adminLink.style.right = '80px';
        adminLink.style.zIndex = '1000';
    }

    // Пользовательская информация - вверху
    const userInfo = document.querySelector('.user-info');
    if (userInfo) {
        userInfo.style.position = 'fixed';
        userInfo.style.top = '20px';
        userInfo.style.left = '20px';
        userInfo.style.right = '160px';
        userInfo.style.zIndex = '1000';
        userInfo.style.background = 'rgba(255, 255, 255, 0.9)';
        userInfo.style.padding = '10px';
        userInfo.style.borderRadius = '8px';
    }

    // Основной контент - отступ сверху
    const parchment = document.querySelector('.parchment');
    if (parchment) {
        parchment.style.marginTop = '80px';
        parchment.style.paddingTop = '20px';
    }
}

// ===== ПЛАВНОЕ ПРОЛИСТЫВАНИЕ =====

function initSmoothScrolling() {
    // Добавляем плавное пролистывание для всех ссылок
    const links = document.querySelectorAll('a[href^="#"]');
    links.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Плавное пролистывание для кнопок навигации
    const navButtons = document.querySelectorAll('.fast-btn');
    navButtons.forEach(button => {
        button.addEventListener('click', function() {
            // Плавно прокручиваем к результату после действия
            setTimeout(() => {
                const resultElement = document.querySelector('.chat-box') ||
                    document.querySelector('.notes-block') ||
                    document.querySelector('.modal');
                if (resultElement) {
                    resultElement.scrollIntoView({
                        behavior: 'smooth',
                        block: 'nearest'
                    });
                }
            }, 500);
        });
    });
}

// ===== МОБИЛЬНЫЕ ФОРМЫ =====

function initMobileForms() {
    // Улучшаем поля ввода
    const inputs = document.querySelectorAll('input[type="text"], input[type="number"], select');
    inputs.forEach(input => {
        input.style.fontSize = '16px'; // Предотвращает зум на iOS
        input.style.padding = '12px';
        input.style.minHeight = '44px';
        input.style.borderRadius = '8px';
        input.style.border = '2px solid var(--border-primary)';
    });

    // Улучшаем кнопки
    const buttons = document.querySelectorAll('.fast-btn, button[type="submit"]');
    buttons.forEach(button => {
        button.style.minHeight = '44px';
        button.style.padding = '12px 20px';
        button.style.fontSize = '16px';
        button.style.borderRadius = '8px';
    });
}

// ===== ПРОСТЫЕ МОДАЛЬНЫЕ ОКНА =====

function openSimpleDiceModal() {
    const content = `
        <div style="text-align: center; padding: 20px;">
            <h3 style="margin-bottom: 20px;">🎲 Бросок костей</h3>
            <div style="margin-bottom: 15px;">
                <input type="text" id="dice-input" value="1d20" 
                       style="width: 100px; text-align: center; font-size: 18px; padding: 10px;">
            </div>
            <div style="margin-bottom: 20px;">
                <input type="text" id="dice-label" placeholder="Комментарий" 
                       style="width: 200px; padding: 10px;">
            </div>
            <button class="fast-btn" onclick="rollDice()" 
                    style="width: 100%; padding: 15px; font-size: 18px;">
                🎲 Бросить
            </button>
        </div>
    `;
    showModal(content);
    setTimeout(() => document.getElementById('dice-input').focus(), 100);
}

function openSimpleNpcModal() {
    const content = `
        <div style="padding: 20px;">
            <h3 style="text-align: center; margin-bottom: 20px;">🤖 Создать NPC</h3>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Раса:</label>
                <select id="npc-race" style="width: 100%; padding: 12px; font-size: 16px;">
                    <option value="человек">Человек</option>
                    <option value="эльф">Эльф</option>
                    <option value="гном">Гном</option>
                    <option value="полуорк">Полуорк</option>
                    <option value="полурослик">Полурослик</option>
                    <option value="тифлинг">Тифлинг</option>
                    <option value="драконорожденный">Драконорожденный</option>
                    <option value="полуэльф">Полуэльф</option>
                    <option value="дворф">Дворф</option>
                    <option value="гоблин">Гоблин</option>
                    <option value="орк">Орк</option>
                    <option value="кобольд">Кобольд</option>
                    <option value="ящеролюд">Ящеролюд</option>
                    <option value="хоббит">Хоббит</option>
                </select>
            </div>
            
            <div style="margin-bottom: 15px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Класс:</label>
                <select id="npc-class" style="width: 100%; padding: 12px; font-size: 16px;">
                    <option value="воин">Воин</option>
                    <option value="маг">Маг</option>
                    <option value="жрец">Жрец</option>
                    <option value="плут">Плут</option>
                    <option value="паладин">Паладин</option>
                    <option value="следопыт">Следопыт</option>
                    <option value="варвар">Варвар</option>
                    <option value="бард">Бард</option>
                    <option value="друид">Друид</option>
                    <option value="монах">Монах</option>
                    <option value="колдун">Колдун</option>
                    <option value="чародей">Чародей</option>
                    <option value="изобретатель">Изобретатель</option>
                    <option value="кровный охотник">Кровный охотник</option>
                    <option value="мистик">Мистик</option>
                    <option value="психоник">Психоник</option>
                    <option value="артифисер">Артифисер</option>
                </select>
            </div>
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 5px; font-weight: bold;">Уровень:</label>
                <input type="number" id="npc-level" value="1" min="1" max="20" 
                       style="width: 100%; padding: 12px; font-size: 16px;">
            </div>
            
            <button class="fast-btn" onclick="generateSimpleNpc()" 
                    style="width: 100%; padding: 15px; font-size: 18px;">
                🤖 Создать NPC
            </button>
        </div>
    `;
    showModal(content);
}

function openSimpleInitiativeModal() {
    const content = `
        <div style="text-align: center; padding: 20px;">
            <h3 style="margin-bottom: 20px;">⚔️ Добавить в инициативу</h3>
            <div style="margin-bottom: 15px;">
                <input type="text" id="initiative-name" placeholder="Имя персонажа" 
                       style="width: 200px; padding: 10px; font-size: 16px;">
            </div>
            <div style="margin-bottom: 20px;">
                <input type="number" id="initiative-value" placeholder="Инициатива" 
                       style="width: 100px; padding: 10px; font-size: 16px;">
            </div>
            <button class="fast-btn" onclick="addInitiative()" 
                    style="width: 100%; padding: 15px; font-size: 18px;">
                ⚔️ Добавить
            </button>
        </div>
    `;
    showModal(content);
    setTimeout(() => document.getElementById('initiative-name').focus(), 100);
}

// ===== ПРОСТЫЕ ФУНКЦИИ =====

function generateSimpleNpc() {
    const race = document.getElementById('npc-race').value;
    const npcClass = document.getElementById('npc-class').value;
    const level = document.getElementById('npc-level').value;

    if (!race || !npcClass || !level) {
        alert('Заполните все поля');
        return;
    }

    closeModal();
    setTimeout(() => {
        // Используем существующие функции
        window.npcRace = race;
        window.npcClass = npcClass;
        window.npcLevel = parseInt(level);
        generateNpcWithLevel();
    }, 300);
}

// ===== ПРОСТЫЕ CSS СТИЛИ =====

const simpleMobileStyles = `
<style>
/* Простые мобильные стили */
.mobile-device .parchment {
    margin: 80px 10px 20px 10px;
    padding: 15px;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
}

/* Улучшенные кнопки */
.mobile-device .fast-btn {
    margin: 5px 0;
    padding: 15px 20px;
    font-size: 16px;
    border-radius: 10px;
    border: none;
    background: linear-gradient(135deg, #8B4513, #A0522D);
    color: white;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    transition: all 0.3s ease;
}

.mobile-device .fast-btn:active {
    transform: scale(0.98);
    box-shadow: 0 1px 4px rgba(0, 0, 0, 0.3);
}

/* Улучшенные поля ввода */
.mobile-device input[type="text"],
.mobile-device input[type="number"],
.mobile-device select {
    width: 100%;
    padding: 12px;
    font-size: 16px;
    border: 2px solid #ddd;
    border-radius: 8px;
    background: white;
    margin: 5px 0;
}

.mobile-device input:focus,
.mobile-device select:focus {
    outline: none;
    border-color: #8B4513;
    box-shadow: 0 0 5px rgba(139, 69, 19, 0.3);
}

/* Улучшенный чат */
.mobile-device .chat-box {
    max-height: 60vh;
    overflow-y: auto;
    padding: 15px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    border: 1px solid #ddd;
    margin: 10px 0;
}

/* Улучшенные заметки */
.mobile-device .notes-block {
    margin-top: 20px;
    padding: 15px;
    background: rgba(255, 255, 255, 0.9);
    border-radius: 10px;
    border: 1px solid #ddd;
}

/* Улучшенные модальные окна */
.mobile-device .modal {
    width: 95vw;
    max-width: 400px;
    height: auto;
    max-height: 90vh;
    margin: 5vh auto;
    border-radius: 15px;
    overflow-y: auto;
    background: white;
    box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
}

.mobile-device .modal-content {
    padding: 20px;
}

/* Улучшенная форма чата */
.mobile-device form {
    display: flex;
    gap: 10px;
    margin: 15px 0;
    align-items: center;
}

.mobile-device form input[type="text"] {
    flex: 1;
    margin: 0;
}

.mobile-device form button {
    padding: 12px 20px;
    font-size: 16px;
    border-radius: 8px;
    border: none;
    background: #8B4513;
    color: white;
    white-space: nowrap;
}

/* Улучшенные ссылки */
.mobile-device .reset-link {
    color: #8B4513;
    text-decoration: none;
    font-size: 14px;
    margin-left: 10px;
}

.mobile-device .reset-link:hover {
    text-decoration: underline;
}

/* Плавная прокрутка */
.mobile-device * {
    scroll-behavior: smooth;
}

/* Улучшенные заголовки */
.mobile-device h1 {
    text-align: center;
    margin: 20px 0;
    color: #8B4513;
    font-size: 24px;
    text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.1);
}

/* Адаптивная сетка кнопок */
.mobile-device .fast-bar {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 10px;
    margin: 15px 0;
}

/* Улучшенные подсказки */
.mobile-device .hotkeys-hint {
    text-align: center;
    margin: 10px 0;
    font-size: 12px;
    color: #666;
    opacity: 0.8;
}

/* Темная тема для мобильных */
.mobile-device[data-theme="dark"] .parchment {
    background: #2a2a2a;
    color: #fff;
}

.mobile-device[data-theme="dark"] input,
.mobile-device[data-theme="dark"] select {
    background: #3a3a3a;
    color: #fff;
    border-color: #555;
}

.mobile-device[data-theme="dark"] .chat-box,
.mobile-device[data-theme="dark"] .notes-block {
    background: rgba(58, 58, 58, 0.9);
    border-color: #555;
}

/* Анимации */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

.mobile-device .parchment {
    animation: fadeIn 0.5s ease-out;
}

/* Улучшенная доступность */
.mobile-device button:focus,
.mobile-device input:focus,
.mobile-device select:focus {
    outline: 2px solid #8B4513;
    outline-offset: 2px;
}
</style>
`;

// Добавляем стили в head
document.head.insertAdjacentHTML('beforeend', simpleMobileStyles);