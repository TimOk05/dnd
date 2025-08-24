<?php
/**
 * Улучшенная функция форматирования NPC
 * Работает с данными от внешних D&D API
 */

function formatNpcFromApi($npcData) {
    if (!$npcData || !isset($npcData['name'])) {
        return '<div class="npc-block-modern">
            <div class="npc-modern-header">Ошибка генерации</div>
            <div class="npc-modern-block">Не удалось получить данные NPC. Попробуйте ещё раз.</div>
        </div>';
    }
    
    $name = htmlspecialchars($npcData['name']);
    $description = htmlspecialchars($npcData['description'] ?? '');
    $appearance = htmlspecialchars($npcData['appearance'] ?? '');
    $traits = htmlspecialchars($npcData['traits'] ?? '');
    $technicalParams = $npcData['technical_params'] ?? [];
    
    $output = '<div class="npc-block-modern">';
    
    // Заголовок с именем
    $output .= '<div class="npc-modern-header">' . $name . '</div>';
    
    // Технические параметры (сворачиваемые)
    if (!empty($technicalParams)) {
        $techContent = '';
        if (is_array($technicalParams)) {
            $techContent = '<ul class="npc-modern-list">';
            foreach ($technicalParams as $param) {
                $techContent .= '<li>' . htmlspecialchars($param) . '</li>';
            }
            $techContent .= '</ul>';
        } else {
            $techContent = htmlspecialchars($technicalParams);
        }
        
        $output .= '<div class="npc-col-block">
            <div class="npc-collapsible-header collapsed" onclick="toggleTechnicalParams(this)">
                <div><span style="font-size:1.2em;">⚔️</span> <b>Технические параметры</b></div>
                <span class="toggle-icon">▼</span>
            </div>
            <div class="npc-collapsible-content collapsed">
                <div style="margin-top: 8px;">' . $techContent . '</div>
            </div>
        </div>';
    }
    
    // Описание
    if ($description) {
        $output .= '<div class="npc-col-block">
            <span style="font-size:1.2em;">📜</span> <b>Описание</b>
            <div class="npc-content">' . $description . '</div>
        </div>';
    }
    
    // Черты характера
    if ($traits) {
        $output .= '<div class="npc-col-block">
            <span style="font-size:1.2em;">🧠</span> <b>Черты характера</b>
            <div class="npc-content">' . $traits . '</div>
        </div>';
    }
    
    // Внешность
    if ($appearance) {
        $output .= '<div class="npc-col-block">
            <span style="font-size:1.2em;">👤</span> <b>Внешность</b>
            <div class="npc-content">' . $appearance . '</div>
        </div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Функция для создания HTML формы генерации NPC
 */
function createNpcGenerationForm() {
    $races = [
        'human' => 'Человек',
        'elf' => 'Эльф',
        'dwarf' => 'Дварф',
        'halfling' => 'Полурослик',
        'orc' => 'Орк',
        'tiefling' => 'Тифлинг',
        'dragonborn' => 'Драконорожденный',
        'gnome' => 'Гном',
        'half-elf' => 'Полуэльф',
        'half-orc' => 'Полуорк'
    ];
    
    $classes = [
        'fighter' => 'Воин',
        'wizard' => 'Волшебник',
        'rogue' => 'Плут',
        'cleric' => 'Жрец',
        'ranger' => 'Следопыт',
        'barbarian' => 'Варвар',
        'bard' => 'Бард',
        'druid' => 'Друид',
        'monk' => 'Монах',
        'paladin' => 'Паладин',
        'sorcerer' => 'Сорсерер',
        'warlock' => 'Колдун'
    ];
    
    $alignments = [
        'lawful good' => 'Законно-добрый',
        'neutral good' => 'Нейтрально-добрый',
        'chaotic good' => 'Хаотично-добрый',
        'lawful neutral' => 'Законно-нейтральный',
        'neutral' => 'Нейтральный',
        'chaotic neutral' => 'Хаотично-нейтральный',
        'lawful evil' => 'Законно-злой',
        'neutral evil' => 'Нейтрально-злой',
        'chaotic evil' => 'Хаотично-злой'
    ];
    
    $form = '<div class="npc-generation-form">
        <h3>Генерация NPC</h3>
        <form id="npcForm" method="post">
            <div class="form-group">
                <label for="race">Раса:</label>
                <select name="race" id="race" required>';
    
    foreach ($races as $value => $label) {
        $form .= '<option value="' . $value . '">' . $label . '</option>';
    }
    
    $form .= '</select>
            </div>
            
            <div class="form-group">
                <label for="class">Класс:</label>
                <select name="class" id="class" required>';
    
    foreach ($classes as $value => $label) {
        $form .= '<option value="' . $value . '">' . $label . '</option>';
    }
    
    $form .= '</select>
            </div>
            
            <div class="form-group">
                <label for="level">Уровень:</label>
                <input type="number" name="level" id="level" min="1" max="20" value="1" required>
            </div>
            
            <div class="form-group">
                <label for="alignment">Мировоззрение:</label>
                <select name="alignment" id="alignment" required>';
    
    foreach ($alignments as $value => $label) {
        $form .= '<option value="' . $value . '">' . $label . '</option>';
    }
    
    $form .= '</select>
            </div>
            
            <div class="form-group">
                <label for="background">Предыстория:</label>
                <input type="text" name="background" id="background" value="soldier" placeholder="Например: soldier, sage, criminal">
            </div>
            
            <div class="form-group">
                <label>
                    <input type="checkbox" name="use_external_api" id="use_external_api">
                    Использовать внешний API (если доступен)
                </label>
            </div>
            
            <button type="submit" class="generate-btn">Сгенерировать NPC</button>
        </form>
        
        <div id="npcResult" class="npc-result"></div>
    </div>';
    
    return $form;
}

/**
 * JavaScript для работы с формой генерации NPC
 */
function getNpcGenerationScript() {
    return '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const npcForm = document.getElementById("npcForm");
        const npcResult = document.getElementById("npcResult");
        
        if (npcForm) {
            npcForm.addEventListener("submit", function(e) {
                e.preventDefault();
                
                const formData = new FormData(npcForm);
                const submitBtn = npcForm.querySelector(".generate-btn");
                const originalText = submitBtn.textContent;
                
                // Показываем индикатор загрузки
                submitBtn.textContent = "Генерация...";
                submitBtn.disabled = true;
                npcResult.innerHTML = "<div class=\'loading\'>Генерация NPC...</div>";
                
                fetch("api/generate-npc.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.npc) {
                        // Форматируем результат
                        const formattedNpc = formatNpcFromApi(data.npc);
                        npcResult.innerHTML = formattedNpc;
                        
                        // Показываем уведомление об успехе
                        showToast("NPC успешно сгенерирован!", "success");
                    } else {
                        npcResult.innerHTML = "<div class=\'error\'>Ошибка: " + (data.error || "Неизвестная ошибка") + "</div>";
                        showToast("Ошибка генерации NPC", "error");
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    npcResult.innerHTML = "<div class=\'error\'>Ошибка сети. Попробуйте ещё раз.</div>";
                    showToast("Ошибка сети", "error");
                })
                .finally(() => {
                    // Восстанавливаем кнопку
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });
        }
    });
    
    function formatNpcFromApi(npcData) {
        if (!npcData || !npcData.name) {
            return "<div class=\'npc-block-modern\'><div class=\'npc-modern-header\'>Ошибка</div><div class=\'npc-modern-block\'>Неверные данные NPC</div></div>";
        }
        
        let output = "<div class=\'npc-block-modern\'>";
        output += "<div class=\'npc-modern-header\'>" + escapeHtml(npcData.name) + "</div>";
        
        // Технические параметры
        if (npcData.technical_params && npcData.technical_params.length > 0) {
            output += "<div class=\'npc-col-block\'>";
            output += "<div class=\'npc-collapsible-header collapsed\' onclick=\'toggleTechnicalParams(this)\'>";
            output += "<div><span style=\'font-size:1.2em;\'>⚔️</span> <b>Технические параметры</b></div>";
            output += "<span class=\'toggle-icon\'>▼</span></div>";
            output += "<div class=\'npc-collapsible-content collapsed\'>";
            output += "<ul class=\'npc-modern-list\'>";
            npcData.technical_params.forEach(param => {
                output += "<li>" + escapeHtml(param) + "</li>";
            });
            output += "</ul></div></div>";
        }
        
        // Описание
        if (npcData.description) {
            output += "<div class=\'npc-col-block\'>";
            output += "<span style=\'font-size:1.2em;\'>📜</span> <b>Описание</b>";
            output += "<div class=\'npc-content\'>" + escapeHtml(npcData.description) + "</div>";
            output += "</div>";
        }
        
        // Черты характера
        if (npcData.traits) {
            output += "<div class=\'npc-col-block\'>";
            output += "<span style=\'font-size:1.2em;\'>🧠</span> <b>Черты характера</b>";
            output += "<div class=\'npc-content\'>" + escapeHtml(npcData.traits) + "</div>";
            output += "</div>";
        }
        
        // Внешность
        if (npcData.appearance) {
            output += "<div class=\'npc-col-block\'>";
            output += "<span style=\'font-size:1.2em;\'>👤</span> <b>Внешность</b>";
            output += "<div class=\'npc-content\'>" + escapeHtml(npcData.appearance) + "</div>";
            output += "</div>";
        }
        
        output += "</div>";
        return output;
    }
    
    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }
    
    function showToast(message, type = "info") {
        const toast = document.createElement("div");
        toast.className = "npc-toast " + type;
        toast.textContent = message;
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.classList.add("active");
        }, 100);
        
        setTimeout(() => {
            toast.classList.remove("active");
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }
    </script>';
}

/**
 * CSS стили для формы генерации NPC
 */
function getNpcGenerationStyles() {
    return '
    <style>
    .npc-generation-form {
        max-width: 600px;
        margin: 0 auto;
        padding: 20px;
        background: var(--bg-secondary);
        border-radius: 12px;
        border: 2px solid var(--border-primary);
        box-shadow: 0 4px 20px var(--shadow-secondary);
    }
    
    .npc-generation-form h3 {
        text-align: center;
        color: var(--text-tertiary);
        margin-bottom: 20px;
        font-size: 1.4em;
    }
    
    .form-group {
        margin-bottom: 15px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 5px;
        color: var(--text-secondary);
        font-weight: 600;
    }
    
    .form-group input,
    .form-group select {
        width: 100%;
        padding: 10px 12px;
        border: 2px solid var(--border-primary);
        border-radius: 8px;
        background: var(--bg-primary);
        color: var(--text-primary);
        font-size: 1em;
        transition: all 0.3s ease;
    }
    
    .form-group input:focus,
    .form-group select:focus {
        outline: none;
        border-color: var(--accent-secondary);
        background: var(--bg-tertiary);
        box-shadow: 0 0 10px rgba(114, 9, 183, 0.3);
    }
    
    .generate-btn {
        width: 100%;
        padding: 12px 20px;
        background: var(--accent-success);
        color: var(--bg-secondary);
        border: none;
        border-radius: 8px;
        font-size: 1.1em;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-top: 10px;
    }
    
    .generate-btn:hover:not(:disabled) {
        background: var(--accent-info);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px var(--shadow-secondary);
    }
    
    .generate-btn:disabled {
        opacity: 0.6;
        cursor: not-allowed;
    }
    
    .npc-result {
        margin-top: 20px;
    }
    
    .loading {
        text-align: center;
        color: var(--text-tertiary);
        font-style: italic;
        padding: 20px;
    }
    
    .error {
        background: var(--accent-danger);
        color: var(--bg-secondary);
        padding: 15px;
        border-radius: 8px;
        text-align: center;
        font-weight: 600;
    }
    
    .npc-toast {
        position: fixed;
        top: 30px;
        left: 50%;
        transform: translateX(-50%);
        padding: 12px 24px;
        border-radius: 8px;
        font-weight: 600;
        z-index: 2000;
        opacity: 0;
        transition: opacity 0.3s ease;
    }
    
    .npc-toast.success {
        background: var(--accent-success);
        color: var(--bg-secondary);
    }
    
    .npc-toast.error {
        background: var(--accent-danger);
        color: var(--bg-secondary);
    }
    
    .npc-toast.active {
        opacity: 1;
    }
    </style>';
}
?>
