<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Проверяем наличие PDF Parser
$pdfParserAvailable = false;
$error = '';

try {
    if (class_exists('Smalot\PdfParser\Parser')) {
        $pdfParserAvailable = true;
        $message = 'PDF Parser установлен и готов к работе!';
    } else {
        $error = 'PDF Parser не найден. Установите: composer require smalot/pdfparser';
    }
} catch (Exception $e) {
    $error = 'Ошибка при проверке PDF Parser: ' . $e->getMessage();
}

// Проверяем наличие PDF файлов
$pdfDir = '../pdf/';
$pdfFiles = [];

if (is_dir($pdfDir)) {
    $files = scandir($pdfDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'pdf') {
            $pdfFiles[] = $file;
        }
    }
}

echo json_encode([
    'pdfParserAvailable' => $pdfParserAvailable,
    'message' => $message ?? '',
    'error' => $error,
    'pdfFiles' => $pdfFiles,
    'pdfCount' => count($pdfFiles),
    'phpVersion' => PHP_VERSION,
    'extensions' => [
        'curl' => function_exists('curl_init'),
        'json' => function_exists('json_encode'),
        'fileinfo' => function_exists('finfo_open')
    ]
]);
?>
