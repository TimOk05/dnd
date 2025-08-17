<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Обработка preflight запросов
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Проверяем метод запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

try {
    // Получаем данные запроса
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        throw new Exception('Invalid JSON');
    }
    
    $action = $input['action'] ?? '';
    
    switch ($action) {
        case 'upload':
            // Здесь будет загрузка файла (пока заглушка)
            echo json_encode([
                'status' => 'success',
                'message' => 'Функция загрузки будет добавлена позже'
            ]);
            break;
            
        case 'list':
            // Список загруженных файлов
            $pdfDir = '../pdf/';
            $files = [];
            
            if (is_dir($pdfDir)) {
                $pdfFiles = glob($pdfDir . '*.pdf');
                foreach ($pdfFiles as $file) {
                    $filename = basename($file);
                    $filesize = filesize($file);
                    $files[] = [
                        'name' => $filename,
                        'size' => $filesize,
                        'date' => date('Y-m-d H:i:s', filemtime($file))
                    ];
                }
            }
            
            echo json_encode([
                'status' => 'success',
                'files' => $files,
                'count' => count($files)
            ]);
            break;
            
        case 'delete':
            $filename = $input['filename'] ?? '';
            if (empty($filename)) {
                throw new Exception('Filename is required');
            }
            
            $filepath = '../pdf/' . basename($filename);
            if (file_exists($filepath) && unlink($filepath)) {
                echo json_encode([
                    'status' => 'success',
                    'message' => 'Файл удален'
                ]);
            } else {
                throw new Exception('File not found or cannot be deleted');
            }
            break;
            
        default:
            throw new Exception('Unknown action');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Server error',
        'details' => $e->getMessage()
    ]);
}
?>
