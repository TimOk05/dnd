<?php
// Скрипт для обработки PDF файлов
// Требует установки: composer require smalot/pdfparser

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

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

// Функция для извлечения текста из PDF (если установлен PDF Parser)
function extractTextFromPDF($filePath) {
    if (!class_exists('Smalot\PdfParser\Parser')) {
        return "PDF Parser не установлен. Установите: composer require smalot/pdfparser";
    }
    
    try {
        $parser = new \Smalot\PdfParser\Parser();
        $pdf = $parser->parseFile($filePath);
        $text = $pdf->getText();
        return $text;
    } catch (Exception $e) {
        return "Ошибка при чтении PDF: " . $e->getMessage();
    }
}

// Обработка запросов
$action = $_GET['action'] ?? 'list';

switch ($action) {
    case 'list':
        echo json_encode([
            'status' => 'success',
            'files' => $pdfFiles,
            'count' => count($pdfFiles)
        ]);
        break;
        
    case 'extract':
        $filename = $_GET['file'] ?? '';
        if (empty($filename) || !in_array($filename, $pdfFiles)) {
            echo json_encode(['error' => 'Файл не найден']);
            break;
        }
        
        $filePath = $pdfDir . $filename;
        $text = extractTextFromPDF($filePath);
        
        echo json_encode([
            'status' => 'success',
            'filename' => $filename,
            'text' => $text,
            'length' => strlen($text)
        ]);
        break;
        
    default:
        echo json_encode(['error' => 'Неизвестное действие']);
}

?>
