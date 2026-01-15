<?php
header('Content-Type: application/json');
require_once __DIR__ . '/includes/db.php';

try {
    $db = getDBConnection();
    $action = $_GET['action'] ?? 'structure';
    
    if ($action === 'data') {
        $education = $db->query("SELECT * FROM education ORDER BY start_date DESC")->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(['success' => true, 'education' => $education]);
    } else {
        $columns = $db->query("DESCRIBE education")->fetchAll(PDO::FETCH_COLUMN, 0);
        echo json_encode(['success' => true, 'columns' => $columns]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
