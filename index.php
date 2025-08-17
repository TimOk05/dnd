<?php
$staticIndex = __DIR__ . '/apps/web/out/index.html';
if (file_exists($staticIndex)) {
    readfile($staticIndex);
    exit;
} else {
    echo "<h2>Фронтенд не собран</h2><p>Соберите фронтенд командой <code>pnpm --filter=dm-copilot-web run build && pnpm --filter=dm-copilot-web run export</code> и разместите содержимое <code>apps/web/out/</code> на сервере.</p>";
}
