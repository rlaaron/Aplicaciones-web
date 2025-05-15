<?php
// config.php (integrado directamente)
$db = new SQLite3('moodtracker.db');

// Crear tabla y datos iniciales
$db->exec("
    CREATE TABLE IF NOT EXISTS registros (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        fecha DATE DEFAULT CURRENT_TIMESTAMP,
        estado_animo TEXT NOT NULL
    )
");

// Seed de datos (3 meses atrás)
if ($db->querySingle("SELECT COUNT(*) FROM registros") == 0) {
    $estados = ['😊', '😐', '😞'];
    for ($i = 90; $i >= 0; $i--) {
        $fecha = date('Y-m-d', strtotime("-$i days"));
        $estado = $estados[array_rand($estados)];
        $db->exec("INSERT INTO registros (fecha, estado_animo) VALUES ('$fecha', '$estado')");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MoodTracker</title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 400px;
            margin: 0 auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .phone-container {
            background: white;
            border-radius: 30px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            color: #333;
            margin-bottom: 30px;
        }
        .mood-buttons {
            display: flex;
            justify-content: space-around;
            margin: 40px 0;
        }
        .mood-btn {
            font-size: 3em;
            background: none;
            border: none;
            cursor: pointer;
            transition: transform 0.2s;
        }
        .mood-btn:hover {
            transform: scale(1.2);
        }
        .tabs {
            display: flex;
            margin-top: 20px;
        }
        .tab {
            padding: 10px 20px;
            background: #eee;
            border: none;
            cursor: pointer;
            flex: 1;
            text-align: center;
        }
        .tab.active {
            background: #4285f4;
            color: white;
        }
        .tab-content {
            display: none;
            margin-top: 20px;
        }
        .tab-content.active {
            display: block;
        }
        #historyChart {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="phone-container">
        <h1>¿Cómo te sientes hoy?</h1>
        
        <form method="POST">
            <div class="mood-buttons">
                <button type="submit" name="estado" value="😊" class="mood-btn">😊</button>
                <button type="submit" name="estado" value="😐" class="mood-btn">😐</button>
                <button type="submit" name="estado" value="😞" class="mood-btn">😞</button>
            </div>
        </form>

        <?php

        if (isset($_POST['estado'])) {
            $estado = $_POST['estado'];
            $db->exec("INSERT INTO registros (estado_animo) VALUES ('$estado')");
            echo "<p style='text-align:center;color:green;'>¡Guardado!</p>";
        }
        ?>

        <div class="tabs">
            <button class="tab active" onclick="openTab('today')">Hoy</button>
            <button class="tab" onclick="openTab('history')">Histórico</button>
        </div>

        <div id="today" class="tab-content active">
            <p style="text-align:center;">Selecciona tu estado de ánimo</p>
        </div>

        <div id="history" class="tab-content">
            <canvas id="historyChart"></canvas>
        </div>
    </div>

    <script>
        // Sistema de pestañas
        function openTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.classList.remove('active');
            });
            document.querySelectorAll('.tab').forEach(tab => {
                tab.classList.remove('active');
            });
            document.getElementById(tabName).classList.add('active');
            event.currentTarget.classList.add('active');
            
            if (tabName === 'history') {
                renderHistoryChart();
            }
        }

        // Gráfico histórico
        // Gráfico histórico (versión robusta)
function renderHistoryChart() {
    fetch('data.php')
        .then(response => {
            if (!response.ok) throw new Error('Network response was not ok');
            return response.json();
        })
        .then(data => {
            console.log("Datos recibidos:", data); // Verifica los datos
            
            const ctx = document.getElementById('historyChart');
            if (ctx.chart) ctx.chart.destroy(); // Destruye gráfico anterior
            
            ctx.chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: data.weeks,
                    datasets: [{
                        label: 'Estado predominante',
                        data: data.predominant,
                        borderColor: '#4285f4',
                        backgroundColor: 'rgba(66, 133, 244, 0.1)',
                        tension: 0.3,
                        pointRadius: 5
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            min: 0,
                            max: 2,
                            ticks: {
                                stepSize: 1,
                                callback: function(value) {
                                    return ['😞', '😐', '😊'][value];
                                }
                            }
                        }
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error loading chart data:', error);
            document.getElementById('history').innerHTML = ≥÷.
                '<p style="color:red;">Error al cargar los datos. Verifica la consola.</p>';
        });
}

        // Datos iniciales para el gráfico
        <?php
        // Datos para el gráfico (simplificado)
        $weeks = [];
        $predominant = [];
        for ($i = 11; $i >= 0; $i--) {
            $weeks[] = date('W/Y', strtotime("-$i weeks"));
            $predominant[] = rand(0, 2); // 0=😞, 1=😐, 2=😊
        }
        ?>
    </script>
</body>
</html>