// ===== МОБИЛЬНЫЕ ФУНКЦИИ =====

// Определяем мобильное устройство
const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);
const isTouch = 'ontouchstart' in window || navigator.maxTouchPoints > 0;

// Инициализация мобильных функций
document.addEventListener('DOMContentLoaded', function() {
    if (isMobile || isTouch) {
        initMobileFeatures();
    }
    
    // Регистрация Service Worker для PWA
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function() {
            navigator.serviceWorker.register('/dnd/sw.js')
                .then(function(registration) {
                    console.log('SW registered: ', registration);
                })
                .catch(function(registrationError) {
                    console.log('SW registration failed: ', registrationError);
                });
        });
    }
    
    // Запрос разрешения на уведомления
    if ('Notification' in window && Notification.permission === 'default') {
        Notification.requestPermission();
    }
});

function initMobileFeatures() {
    // Добавляем класс для мобильных устройств
    document.body.classList.add('mobile-device');
    
    // Инициализируем жесты
    initSwipeGestures();
    
    // Инициализируем мобильную навигацию
    initMobileNavigation();
    
    // Инициализируем мобильные модальные окна
    initMobileModals();
    
    // Инициализируем мобильные формы
    initMobileForms();
    
    // Инициализируем мобильные кнопки
    initMobileButtons();
    
    // Инициализируем мобильный чат
    initMobileChat();
}

// ===== ЖЕСТЫ =====

function initSwipeGestures() {
    let startX, startY, endX, endY;
    const minSwipeDistance = 50;
    
    // Обработка касаний
    document.addEventListener('touchstart', function(e) {
        startX = e.touches[0].clientX;
        startY = e.touches[0].clientY;
    });
    
    document.addEventListener('touchend', function(e) {
        endX = e.changedTouches[0].clientX;
        endY = e.changedTouches[0].clientY;
        
        const deltaX = endX - startX;
        const deltaY = endY - startY;
        
        // Определяем направление свайпа
        if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > minSwipeDistance) {
            if (deltaX > 0) {
                // Свайп вправо - показать боковую панель
                handleSwipeRight();
            } else {
                // Свайп влево - скрыть боковую панель
                handleSwipeLeft();
            }
        } else if (Math.abs(deltaY) > Math.abs(deltaX) && Math.abs(deltaY) > minSwipeDistance) {
            if (deltaY > 0) {
                // Свайп вниз - обновить страницу
                handleSwipeDown();
            } else {
                // Свайп вверх - показать быстрые действия
                handleSwipeUp();
            }
        }
    });
}

function handleSwipeRight() {
    // Показать боковую панель с быстрыми действиями
    showQuickActions();
}

function handleSwipeLeft() {
    // Скрыть боковую панель
    hideQuickActions();
}

function handleSwipeDown() {
    // Показать индикатор обновления
    showRefreshIndicator();
}

function handleSwipeUp() {
    // Показать быстрые действия
    showQuickActions();
}

// ===== МОБИЛЬНАЯ НАВИГАЦИЯ =====

function initMobileNavigation() {
    // Создаем мобильную навигационную панель
    createMobileNav();
    
    // Добавляем обработчики для мобильных кнопок
    addMobileNavHandlers();
}

function createMobileNav() {
    const nav = document.createElement('div');
    nav.className = 'mobile-nav';
    nav.innerHTML = `
        <div class="mobile-nav-toggle">
            <span></span>
            <span></span>
            <span></span>
        </div>
        <div class="mobile-nav-menu">
            <a href="#" class="mobile-nav-item" data-action="dice">🎲 Кости</a>
            <a href="#" class="mobile-nav-item" data-action="npc">🤖 NPC</a>
            <a href="#" class="mobile-nav-item" data-action="initiative">⚔️ Инициатива</a>
            <a href="#" class="mobile-nav-item" data-action="notes">📝 Заметки</a>
            <a href="#" class="mobile-nav-item" data-action="stats">📊 Статистика</a>
            <a href="#" class="mobile-nav-item" data-action="theme">🌙 Тема</a>
        </div>
    `;
    
    document.body.appendChild(nav);
}

function addMobileNavHandlers() {
    const toggle = document.querySelector('.mobile-nav-toggle');
    const menu = document.querySelector('.mobile-nav-menu');
    const items = document.querySelectorAll('.mobile-nav-item');
    
    if (toggle) {
        toggle.addEventListener('click', function() {
            menu.classList.toggle('active');
            toggle.classList.toggle('active');
        });
    }
    
    items.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            const action = this.dataset.action;
            handleMobileNavAction(action);
            menu.classList.remove('active');
            toggle.classList.remove('active');
        });
    });
}

function handleMobileNavAction(action) {
    switch(action) {
        case 'dice':
            openDiceModal();
            break;
        case 'npc':
            openNpcModal();
            break;
        case 'initiative':
            openInitiativeModal();
            break;
        case 'notes':
            focusNotes();
            break;
        case 'stats':
            window.location.href = 'stats.php';
            break;
        case 'theme':
            toggleTheme();
            break;
    }
}

// ===== МОБИЛЬНЫЕ МОДАЛЬНЫЕ ОКНА =====

function initMobileModals() {
    // Улучшаем модальные окна для мобильных
    const modals = document.querySelectorAll('.modal');
    
    modals.forEach(modal => {
        // Добавляем возможность закрытия свайпом
        let startY = 0;
        let currentY = 0;
        
        modal.addEventListener('touchstart', function(e) {
            startY = e.touches[0].clientY;
        });
        
        modal.addEventListener('touchmove', function(e) {
            currentY = e.touches[0].clientY;
            const deltaY = currentY - startY;
            
            if (deltaY > 0) {
                modal.style.transform = `translateY(${deltaY}px)`;
            }
        });
        
        modal.addEventListener('touchend', function(e) {
            const deltaY = currentY - startY;
            
            if (deltaY > 100) {
                // Закрыть модальное окно
                closeModal();
            } else {
                // Вернуть на место
                modal.style.transform = '';
            }
        });
    });
}

// ===== МОБИЛЬНЫЕ ФОРМЫ =====

function initMobileForms() {
    const inputs = document.querySelectorAll('input[type="text"], input[type="number"]');
    
    inputs.forEach(input => {
        // Добавляем автофокус на мобильных
        if (input.id === 'messageInput') {
            setTimeout(() => {
                input.focus();
            }, 500);
        }
        
        // Улучшаем UX для мобильных форм
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.classList.remove('focused');
        });
    });
}

// ===== МОБИЛЬНЫЕ КНОПКИ =====

function initMobileButtons() {
    const buttons = document.querySelectorAll('.fast-btn, button[type="submit"], .modal .modal-save, .modal-regenerate');
    
    buttons.forEach(button => {
        // Добавляем haptic feedback на поддерживаемых устройствах
        button.addEventListener('click', function() {
            if (navigator.vibrate) {
                navigator.vibrate(50);
            }
        });
        
        // Улучшаем touch targets
        button.style.minHeight = '44px';
        button.style.touchAction = 'manipulation';
    });
}

// ===== МОБИЛЬНЫЙ ЧАТ =====

function initMobileChat() {
    const chatBox = document.querySelector('.chat-box');
    const messageInput = document.getElementById('messageInput');
    
    if (chatBox && messageInput) {
        // Автоматическая прокрутка к новым сообщениям
        const observer = new MutationObserver(function() {
            chatBox.scrollTop = chatBox.scrollHeight;
        });
        
        observer.observe(chatBox, {
            childList: true,
            subtree: true
        });
        
        // Улучшенная отправка сообщений
        messageInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                submitMessage();
            }
        });
    }
}

// ===== ВСПОМОГАТЕЛЬНЫЕ ФУНКЦИИ =====

function showQuickActions() {
    const quickActions = document.querySelector('.mobile-nav-menu');
    if (quickActions) {
        quickActions.classList.add('active');
    }
}

function hideQuickActions() {
    const quickActions = document.querySelector('.mobile-nav-menu');
    if (quickActions) {
        quickActions.classList.remove('active');
    }
}

function showRefreshIndicator() {
    // Показываем индикатор обновления
    const indicator = document.createElement('div');
    indicator.className = 'refresh-indicator';
    indicator.textContent = 'Потяните для обновления';
    document.body.appendChild(indicator);
    
    setTimeout(() => {
        indicator.remove();
    }, 2000);
}

function openDiceModal() {
    // Открываем модальное окно для броска костей
    showModal('<b class="mini-menu-title">Бросок костей:</b><div class="dice-input-wrap"><input type=text id=dice-input value="1d20" placeholder="1d20" style="width:80px;text-align:center"></div><div class="dice-label-wrap"><input type=text id=dice-label placeholder="Комментарий (необязательно)" style="width:200px"></div><button class="fast-btn" onclick="rollDice()">🎲 Бросить</button>');
    setTimeout(() => document.getElementById('dice-input').focus(), 100);
}

function openNpcModal() {
    // Открываем модальное окно для генерации NPC
    showModal('<b class="mini-menu-title">Генерация NPC:</b><div class="npc-race-wrap"><select id=npc-race style="width:120px"><option value="человек">Человек</option><option value="эльф">Эльф</option><option value="гном">Гном</option><option value="полуорк">Полуорк</option><option value="полурослик">Полурослик</option><option value="тифлинг">Тифлинг</option><option value="драконорожденный">Драконорожденный</option><option value="полуэльф">Полуэльф</option><option value="дворф">Дворф</option><option value="гоблин">Гоблин</option><option value="орк">Орк</option><option value="кобольд">Кобольд</option><option value="ящеролюд">Ящеролюд</option><option value="хоббит">Хоббит</option></select></div><div class="npc-class-wrap"><select id=npc-class style="width:120px"><option value="воин">Воин</option><option value="маг">Маг</option><option value="жрец">Жрец</option><option value="плут">Плут</option><option value="паладин">Паладин</option><option value="следопыт">Следопыт</option><option value="варвар">Варвар</option><option value="бард">Бард</option><option value="друид">Друид</option><option value="монах">Монах</option><option value="колдун">Колдун</option><option value="чародей">Чародей</option><option value="изобретатель">Изобретатель</option><option value="кровный охотник">Кровный охотник</option><option value="мистик">Мистик</option><option value="психоник">Психоник</option><option value="артифисер">Артифисер</option></select></div><button class="fast-btn" onclick="generateNpc()">🤖 Создать NPC</button>');
}

function openInitiativeModal() {
    // Открываем модальное окно для инициативы
    showModal('<b class="mini-menu-title">Добавить участника инициативы:</b><div class="initiative-input-wrap"><input type=text id=initiative-name placeholder="Имя персонажа" style="width:150px"></div><div class="initiative-value-wrap"><input type=number id=initiative-value placeholder="Инициатива" style="width:80px;text-align:center"></div><button class="fast-btn" onclick="addInitiative()">⚔️ Добавить</button>');
    setTimeout(() => document.getElementById('initiative-name').focus(), 100);
}

function focusNotes() {
    // Фокусируемся на заметках
    const notesBlock = document.querySelector('.notes-block');
    if (notesBlock) {
        notesBlock.scrollIntoView({ behavior: 'smooth' });
        notesBlock.style.animation = 'pulse 0.5s ease-in-out';
        setTimeout(() => {
            notesBlock.style.animation = '';
        }, 500);
    }
}

function submitMessage() {
    const form = document.getElementById('chatForm');
    if (form) {
        form.submit();
    }
}

// ===== CSS АНИМАЦИИ =====

const mobileStyles = `
<style>
/* Мобильная навигация */
.mobile-nav {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 60px;
    background: var(--bg-primary);
    border-bottom: 2px solid var(--border-primary);
    z-index: 1001;
    display: none;
}

.mobile-device .mobile-nav {
    display: block;
}

.mobile-nav-toggle {
    position: absolute;
    top: 15px;
    left: 15px;
    width: 30px;
    height: 30px;
    cursor: pointer;
    z-index: 1002;
}

.mobile-nav-toggle span {
    display: block;
    width: 100%;
    height: 3px;
    background: var(--text-primary);
    margin: 6px 0;
    transition: 0.3s;
}

.mobile-nav-toggle.active span:nth-child(1) {
    transform: rotate(-45deg) translate(-9px, 6px);
}

.mobile-nav-toggle.active span:nth-child(2) {
    opacity: 0;
}

.mobile-nav-toggle.active span:nth-child(3) {
    transform: rotate(45deg) translate(-8px, -8px);
}

.mobile-nav-menu {
    position: fixed;
    top: 60px;
    left: -100%;
    width: 250px;
    height: calc(100vh - 60px);
    background: var(--bg-secondary);
    border-right: 2px solid var(--border-primary);
    transition: 0.3s;
    z-index: 1000;
    padding: 20px;
}

.mobile-nav-menu.active {
    left: 0;
}

.mobile-nav-item {
    display: block;
    padding: 15px;
    margin: 5px 0;
    background: var(--bg-tertiary);
    border: 1px solid var(--border-primary);
    border-radius: 8px;
    text-decoration: none;
    color: var(--text-primary);
    font-size: 1.1em;
    transition: 0.3s;
}

.mobile-nav-item:hover {
    background: var(--bg-quaternary);
    transform: translateX(5px);
}

/* Индикатор обновления */
.refresh-indicator {
    position: fixed;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--accent-info);
    color: white;
    padding: 10px 20px;
    border-radius: 20px;
    z-index: 1000;
    animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
    from {
        transform: translateX(-50%) translateY(-100%);
        opacity: 0;
    }
    to {
        transform: translateX(-50%) translateY(0);
        opacity: 1;
    }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}

/* Мобильные улучшения для основного контента */
.mobile-device .parchment {
    margin-top: 70px;
}

.mobile-device .theme-toggle {
    top: 70px;
}

.mobile-device .admin-link {
    top: 70px;
}

/* Улучшения для touch-устройств */
.mobile-device .fast-btn:active,
.mobile-device button[type=submit]:active,
.mobile-device .modal .modal-save:active,
.mobile-device .modal-regenerate:active {
    transform: scale(0.95);
    transition: transform 0.1s;
}

/* Фокус на формах */
.mobile-device form.focused {
    border: 2px solid var(--accent-primary);
    border-radius: 8px;
    padding: 5px;
}

/* Улучшенные модальные окна для мобильных */
.mobile-device .modal {
    transition: transform 0.3s ease;
}

.mobile-device .modal.closing {
    transform: translateY(100%);
}
</style>
`;

// Добавляем стили в head
document.head.insertAdjacentHTML('beforeend', mobileStyles);
