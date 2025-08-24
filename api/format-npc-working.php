<?php
/**
 * Улучшенная функция форматирования NPC для работы с данными от внешних API
 */

function formatNpcFromWorkingApi($npcData) {
    if (!$npcData || !isset($npcData['name'])) {
        return '<div class="npc-block-modern">
            <div class="npc-modern-header">Ошибка генерации</div>
            <div class="npc-modern-block">Не удалось получить данные NPC. Попробуйте ещё раз.</div>
        </div>';
    }
    
    $name = htmlspecialchars($npcData['name']);
    $race = htmlspecialchars($npcData['race']);
    $class = htmlspecialchars($npcData['class']);
    $level = $npcData['level'];
    $alignment = htmlspecialchars($npcData['alignment']);
    $description = htmlspecialchars($npcData['description']);
    $appearance = htmlspecialchars($npcData['appearance']);
    $profession = htmlspecialchars($npcData['profession']);
    $technicalParams = $npcData['technical_params'] ?? [];
    $spells = $npcData['spells'] ?? [];
    
    $output = '<div class="npc-block-modern">';
    
    // Заголовок с именем
    $output .= '<div class="npc-modern-header">' . $name . '</div>';
    
    // Основная информация
    $output .= '<div class="npc-modern-block">';
    $output .= '<strong>Раса и класс:</strong> ' . $race . ' - ' . $class . ' (уровень ' . $level . ')<br>';
    $output .= '<strong>Мировоззрение:</strong> ' . $alignment . '<br>';
    $output .= '<strong>Профессия:</strong> ' . $profession;
    $output .= '</div>';
    
    // Описание
    if ($description) {
        $output .= '<div class="npc-modern-block">';
        $output .= '<strong>Описание:</strong><br>' . $description;
        $output .= '</div>';
    }
    
    // Внешность
    if ($appearance) {
        $output .= '<div class="npc-modern-block">';
        $output .= '<strong>Внешность:</strong><br>' . $appearance;
        $output .= '</div>';
    }
    
    // Технические параметры
    if (!empty($technicalParams)) {
        $output .= '<div class="npc-modern-block">';
        $output .= '<strong>Технические параметры:</strong><br>';
        $output .= '<ul>';
        foreach ($technicalParams as $param) {
            $output .= '<li>' . htmlspecialchars($param) . '</li>';
        }
        $output .= '</ul>';
        $output .= '</div>';
    }
    
    // Заклинания
    if (!empty($spells)) {
        $output .= '<div class="npc-modern-block">';
        $output .= '<strong>Заклинания:</strong><br>';
        
        foreach ($spells as $level => $spellList) {
            $levelName = $level === 'cantrips' ? 'Заговоры (0 уровень)' : 'Уровень ' . str_replace('level_', '', $level);
            $output .= '<strong>' . $levelName . ':</strong><br>';
            $output .= '<ul>';
            foreach ($spellList as $spell) {
                $output .= '<li>' . htmlspecialchars($spell) . '</li>';
            }
            $output .= '</ul>';
        }
        
        $output .= '</div>';
    }
    
    $output .= '</div>';
    
    return $output;
}

/**
 * Создание формы генерации NPC
 */
function createNpcGenerationForm() {
    $output = '<div class="npc-generation-form">';
    $output .= '<form id="npcWorkingForm">';
    
    // Раса
    $output .= '<div class="form-group">';
    $output .= '<label for="race">Раса:</label>';
    $output .= '<select name="race" id="race" required>';
    $output .= '<option value="human">Человек</option>';
    $output .= '<option value="elf">Эльф</option>';
    $output .= '<option value="dwarf">Дварф</option>';
    $output .= '<option value="halfling">Полурослик</option>';
    $output .= '<option value="orc">Орк</option>';
    $output .= '<option value="tiefling">Тифлинг</option>';
    $output .= '<option value="dragonborn">Драконорожденный</option>';
    $output .= '<option value="gnome">Гном</option>';
    $output .= '<option value="half-elf">Полуэльф</option>';
    $output .= '<option value="half-orc">Полуорк</option>';
    $output .= '</select>';
    $output .= '</div>';
    
    // Класс
    $output .= '<div class="form-group">';
    $output .= '<label for="class">Класс:</label>';
    $output .= '<select name="class" id="class" required>';
    $output .= '<option value="fighter">Воин</option>';
    $output .= '<option value="wizard">Волшебник</option>';
    $output .= '<option value="rogue">Плут</option>';
    $output .= '<option value="cleric">Жрец</option>';
    $output .= '<option value="ranger">Следопыт</option>';
    $output .= '<option value="barbarian">Варвар</option>';
    $output .= '<option value="bard">Бард</option>';
    $output .= '<option value="druid">Друид</option>';
    $output .= '<option value="monk">Монах</option>';
    $output .= '<option value="paladin">Паладин</option>';
    $output .= '<option value="sorcerer">Сорсерер</option>';
    $output .= '<option value="warlock">Колдун</option>';
    $output .= '</select>';
    $output .= '</div>';
    
    // Уровень
    $output .= '<div class="form-group">';
    $output .= '<label for="level">Уровень:</label>';
    $output .= '<input type="number" name="level" id="level" min="1" max="20" value="1" required>';
    $output .= '</div>';
    
    // Мировоззрение
    $output .= '<div class="form-group">';
    $output .= '<label for="alignment">Мировоззрение:</label>';
    $output .= '<select name="alignment" id="alignment" required>';
    $output .= '<option value="lawful good">Законно-добрый</option>';
    $output .= '<option value="neutral good">Нейтрально-добрый</option>';
    $output .= '<option value="chaotic good">Хаотично-добрый</option>';
    $output .= '<option value="lawful neutral">Законно-нейтральный</option>';
    $output .= '<option value="neutral">Нейтральный</option>';
    $output .= '<option value="chaotic neutral">Хаотично-нейтральный</option>';
    $output .= '<option value="lawful evil">Законно-злой</option>';
    $output .= '<option value="neutral evil">Нейтрально-злой</option>';
    $output .= '<option value="chaotic evil">Хаотично-злой</option>';
    $output .= '</select>';
    $output .= '</div>';
    
    $output .= '<button type="submit" class="generate-btn">Сгенерировать NPC</button>';
    $output .= '</form>';
    $output .= '<div id="npcWorkingResult"></div>';
    $output .= '</div>';
    
    return $output;
}

/**
 * JavaScript для обработки формы генерации NPC
 */
function getNpcWorkingJavaScript() {
    return '
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const form = document.getElementById("npcWorkingForm");
        if (form) {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector(".generate-btn");
                const resultDiv = document.getElementById("npcWorkingResult");
                
                submitBtn.textContent = "Генерация...";
                submitBtn.disabled = true;
                resultDiv.innerHTML = "<p>Генерация NPC...</p>";
                
                fetch("api/generate-npc-working.php", {
                    method: "POST",
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success && data.npc) {
                        resultDiv.innerHTML = formatNpcFromWorkingApi(data.npc);
                    } else {
                        resultDiv.innerHTML = "<p class=\"error\">Ошибка: " + (data.error || "Неизвестная ошибка") + "</p>";
                    }
                })
                .catch(error => {
                    console.error("Error:", error);
                    resultDiv.innerHTML = "<p class=\"error\">Ошибка сети. Попробуйте ещё раз.</p>";
                })
                .finally(() => {
                    submitBtn.textContent = "Сгенерировать NPC";
                    submitBtn.disabled = false;
                });
            });
        }
    });
    
    function formatNpcFromWorkingApi(npc) {
        let output = "<div class=\"npc-block-modern\">";
        output += "<div class=\"npc-modern-header\">" + npc.name + "</div>";
        
        output += "<div class=\"npc-modern-block\">";
        output += "<strong>Раса и класс:</strong> " + npc.race + " - " + npc.class + " (уровень " + npc.level + ")<br>";
        output += "<strong>Мировоззрение:</strong> " + npc.alignment + "<br>";
        output += "<strong>Профессия:</strong> " + npc.profession;
        output += "</div>";
        
        if (npc.description) {
            output += "<div class=\"npc-modern-block\">";
            output += "<strong>Описание:</strong><br>" + npc.description;
            output += "</div>";
        }
        
        if (npc.appearance) {
            output += "<div class=\"npc-modern-block\">";
            output += "<strong>Внешность:</strong><br>" + npc.appearance;
            output += "</div>";
        }
        
        if (npc.technical_params && npc.technical_params.length > 0) {
            output += "<div class=\"npc-modern-block\">";
            output += "<strong>Технические параметры:</strong><br>";
            output += "<ul>";
            npc.technical_params.forEach(param => {
                output += "<li>" + param + "</li>";
            });
            output += "</ul>";
            output += "</div>";
        }
        
        if (npc.spells && Object.keys(npc.spells).length > 0) {
            output += "<div class=\"npc-modern-block\">";
            output += "<strong>Заклинания:</strong><br>";
            
            Object.entries(npc.spells).forEach(([level, spells]) => {
                const levelName = level === "cantrips" ? "Заговоры (0 уровень)" : "Уровень " + level.replace("level_", "");
                output += "<strong>" + levelName + ":</strong><br>";
                output += "<ul>";
                spells.forEach(spell => {
                    output += "<li>" + spell + "</li>";
                });
                output += "</ul>";
            });
            
            output += "</div>";
        }
        
        output += "</div>";
        return output;
    }
    </script>
    ';
}
?>
