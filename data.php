<?php
header('Content-Type: application/json');
$db = new SQLite3('moodtracker.db');

// Consulta compatible con SQLite
$result = $db->query("
    SELECT 
        strftime('%W/%Y', fecha) as week,
        CASE 
            WHEN (
                SUM(CASE WHEN estado_animo = '😊' THEN 1 ELSE 0 END) > 
                SUM(CASE WHEN estado_animo = '😐' THEN 1 ELSE 0 END) AND
                SUM(CASE WHEN estado_animo = '😊' THEN 1 ELSE 0 END) > 
                SUM(CASE WHEN estado_animo = '😞' THEN 1 ELSE 0 END)
            ) THEN 2
            WHEN (
                SUM(CASE WHEN estado_animo = '😐' THEN 1 ELSE 0 END) > 
                SUM(CASE WHEN estado_animo = '😞' THEN 1 ELSE 0 END)
            ) THEN 1
            ELSE 0
        END as predominant
    FROM registros
    WHERE fecha >= date('now', '-3 months')
    GROUP BY strftime('%W/%Y', fecha)
    ORDER BY week
");

$data = ['weeks' => [], 'predominant' => []];

if ($result) {
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $data['weeks'][] = $row['week'];
        $data['predominant'][] = $row['predominant'];
    }
} else {
    // Manejo de error
    $data['error'] = $db->lastErrorMsg();
}

echo json_encode($data);
?>