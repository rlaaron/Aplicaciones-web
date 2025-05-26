# MoodTracker App

Una aplicación web simple para registrar y visualizar tu estado de ánimo diario.

## Descripción

MoodTracker es una aplicación web desarrollada con PHP y SQLite que permite a los usuarios:
- Registrar su estado de ánimo diario (feliz 😊, neutral 😐, o triste 😞)
- Visualizar el histórico de estados de ánimo en un gráfico
- Ver tendencias semanales de su estado emocional

## Estructura del Proyecto

```
finalProject/
├── index.php       # Archivo principal con la interfaz de usuario y lógica básica
├── data.php        # API para obtener datos históricos en formato JSON
├── moodtracker.db  # Base de datos SQLite con los registros
├── style.css       # Estilos CSS (incluidos en index.php)
└── README.md       # Este archivo
```

## Componentes Principales

### Base de Datos (SQLite)
- Tabla `registros`: Almacena los estados de ánimo con fecha
- Campos: `id`, `fecha`, `estado_animo`

```php
// Creación de la tabla en SQLite
$db->exec("
    CREATE TABLE IF NOT EXISTS registros (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        fecha DATE DEFAULT CURRENT_TIMESTAMP,
        estado_animo TEXT NOT NULL
    )
");
```

### Frontend
- Interfaz móvil-first con diseño responsivo
- Sistema de pestañas para navegar entre la vista diaria y el histórico
- Visualización de datos mediante Chart.js

```html
<!-- Sistema de pestañas para la navegación -->
<div class="tabs">
    <button class="tab active" onclick="openTab('today')">Hoy</button>
    <button class="tab" onclick="openTab('history')">Histórico</button>
</div>

<!-- Contenido de las pestañas -->
<div id="today" class="tab-content active">
    <p style="text-align:center;">Selecciona tu estado de ánimo</p>
</div>

<div id="history" class="tab-content">
    <canvas id="historyChart"></canvas>
</div>
```

```javascript
// Función para el sistema de pestañas
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
```

### Backend
- PHP para la lógica del servidor
- SQLite como base de datos ligera
- API simple para obtener datos históricos en formato JSON

```php
// Ejemplo de data.php - API para obtener datos históricos
<?php
header('Content-Type: application/json');
$db = new SQLite3('moodtracker.db');

// Consulta para obtener estados de ánimo predominantes por semana
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

// Formatear datos para el gráfico
$data = ['weeks' => [], 'predominant' => []];
while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
    $data['weeks'][] = $row['week'];
    $data['predominant'][] = $row['predominant'];
}

echo json_encode($data);
?>
```

```php
// Ejemplo de guardado de estado de ánimo en index.php
if (isset($_POST['estado'])) {
    $estado = $_POST['estado'];
    $db->exec("INSERT INTO registros (estado_animo) VALUES ('$estado')");
    echo "<p style='text-align:center;color:green;'>¡Guardado!</p>";
}
```

## Cómo Ejecutar el Proyecto

### Requisitos Previos
- PHP 7.0 o superior
- Extensión SQLite para PHP habilitada
- Navegador web moderno

### Instalación Local

1. Clona o descarga este repositorio en tu máquina local
2. Asegúrate de que PHP esté instalado correctamente

### Ejecución con VS Code y la Funcionalidad PORTS

VS Code ofrece una manera sencilla de ejecutar servidores web locales a través de su funcionalidad PORTS:

1. Abre el proyecto en VS Code
2. Instala la extensión "PHP Server" si aún no la tienes
3. Abre una terminal integrada en VS Code (`Terminal > New Terminal`)
4. Ejecuta un servidor PHP local con el siguiente comando:

   ```bash
   php -S localhost:8000
   ```

5. VS Code detectará automáticamente el puerto abierto y te mostrará una notificación
6. Haz clic en "Open in Browser" o accede manualmente a `http://localhost:8000` en tu navegador

Alternativamente, puedes usar la extensión "Live Server" o "PHP Server" de VS Code:

1. Haz clic derecho en `index.php`
2. Selecciona "PHP Server: Serve Project" o una opción similar según la extensión
3. El proyecto se abrirá automáticamente en tu navegador predeterminado

### Visualización de PORTS en VS Code

Para gestionar los puertos activos en VS Code:

1. Haz clic en el icono "PORTS" en la barra lateral (o presiona `Ctrl+Shift+P` y escribe "Ports: Focus on Ports View")
2. Verás una lista de todos los puertos activos
3. Desde aquí puedes:
   - Abrir el navegador para ver la aplicación
   - Copiar la URL local
   - Detener el servidor
   - Configurar reenvío de puertos (útil para desarrollo remoto)

## Personalización

- Modifica los estilos en la sección CSS dentro de `index.php`
- Ajusta la lógica de la base de datos en la parte superior de `index.php`
- Personaliza la visualización de datos en `data.php`

## Despliegue

Para desplegar esta aplicación en un servidor web:

1. Sube todos los archivos a tu servidor web (asegúrate de que soporte PHP y SQLite)
2. Configura los permisos adecuados para que el servidor pueda escribir en el archivo de base de datos
3. Accede a la aplicación a través de la URL de tu servidor

## Notas Importantes

- La aplicación crea automáticamente la base de datos si no existe
- Se generan datos de ejemplo para los últimos 90 días si la base de datos está vacía
- La visualización de datos agrupa los estados de ánimo por semanas

## Proceso de Desarrollo

### 1. Análisis y Planificación

- **Análisis de Requisitos**: Se identificó la necesidad de una aplicación simple para el seguimiento del estado de ánimo diario
- **Investigación de Usuario**: Se determinó que los usuarios necesitaban una interfaz intuitiva y visualizaciones claras
- **Definición de Funcionalidades**: Registro diario, visualización histórica y análisis de tendencias
- **Selección de Tecnologías**: PHP y SQLite por su simplicidad y facilidad de despliegue

### 2. Diseño

- **Arquitectura de la Aplicación**: Estructura MVC simplificada
- **Diseño de Base de Datos**: Esquema simple con una tabla principal para los registros
- **Wireframes**: Bocetos iniciales de la interfaz con enfoque mobile-first
- **Diseño de UI/UX**: Sistema de pestañas, botones expresivos y paleta de colores adecuada

### 3. Implementación

- **Configuración del Entorno**: Preparación del entorno de desarrollo local con PHP y SQLite
  ```bash
  # Verificación de PHP y extensión SQLite
  php -v
  php -m | grep sqlite
  ```

- **Desarrollo Backend**: Creación de la base de datos, lógica de almacenamiento y recuperación de datos
  ```php
  // Creación de la estructura de la base de datos (index.php)
  $db->exec("
      CREATE TABLE IF NOT EXISTS registros (
          id INTEGER PRIMARY KEY AUTOINCREMENT,
          fecha DATE DEFAULT CURRENT_TIMESTAMP,
          estado_animo TEXT NOT NULL
      )
  ");
  
  // Implementación de la API para obtener datos (data.php)
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
              ELSE /* lógica para otros casos */
          END as predominant
      FROM registros
      WHERE fecha >= date('now', '-3 months')
      GROUP BY strftime('%W/%Y', fecha)
  ");
  ```

- **Desarrollo Frontend**: Implementación de la interfaz de usuario responsiva
  ```html
  <!-- Sistema de pestañas para la navegación (index.php) -->
  <div class="tabs">
      <button class="tab active" onclick="openTab('today')">Hoy</button>
      <button class="tab" onclick="openTab('history')">Histórico</button>
  </div>
  ```
  
  ```css
  /* Estilos responsivos (style.css) */
  body {
      font-family: 'Arial', sans-serif;
      margin: 0;
      padding: 20px;
      background: #f0f2f5;
  }
  .container {
      max-width: 800px;
      margin: 0 auto;
      background: white;
      padding: 20px;
      border-radius: 10px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
  }
  ```

- **Integración**: Conexión entre la interfaz y la base de datos
  ```javascript
  // Función para cargar datos históricos (index.php)
  function renderHistoryChart() {
      fetch('data.php')
          .then(response => response.json())
          .then(data => {
              // Código para renderizar el gráfico con Chart.js
          });
  }
  ```
  
  ```php
  // Guardado de estado de ánimo (index.php)
  if (isset($_POST['estado'])) {
      $estado = $_POST['estado'];
      $db->exec("INSERT INTO registros (estado_animo) VALUES ('$estado')");
      echo "<p style='text-align:center;color:green;'>¡Guardado!</p>";
  }
  ```

### 4. Pruebas

- **Pruebas de Funcionalidad**: Verificación del correcto registro y visualización de datos
- **Pruebas de Usabilidad**: Evaluación de la experiencia de usuario
- **Pruebas de Compatibilidad**: Comprobación en diferentes navegadores y dispositivos
- **Corrección de Errores**: Solución de problemas identificados durante las pruebas

### 5. Despliegue

- **Preparación para Producción**: Optimización del código y recursos
  ```php
  // Uso de CDN para bibliotecas externas (index.php)
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  
  // Estructura de archivos optimizada
  // - index.php: Interfaz principal y lógica básica
  // - data.php: API para datos JSON
  // - style.css: Estilos separados
  // - moodtracker.db: Base de datos SQLite
  ```

- **Documentación**: Creación del README y comentarios en el código
  ```php
  // Comentarios explicativos en el código (data.php)
  // Consulta compatible con SQLite
  $result = $db->query("
      SELECT 
          strftime('%W/%Y', fecha) as week,
          /* Lógica para determinar el estado predominante */
  ");
  ```

- **Despliegue Local**: Configuración para ejecutar la aplicación en entornos locales
  ```bash
  # Comando para iniciar un servidor PHP local
  php -S localhost:8000
  
  # Estructura de directorios para despliegue
  finalProject/
  ├── index.php       # Archivo principal
  ├── data.php        # API de datos
  ├── style.css       # Estilos CSS
  ├── moodtracker.db  # Base de datos
  └── README.md       # Documentación
  ```
  
  ```php
  // Configuración para entorno de desarrollo (index.php)
  // Seed de datos para pruebas
  if ($db->querySingle("SELECT COUNT(*) FROM registros") == 0) {
      $estados = ['😊', '😐', '😞'];
      for ($i = 90; $i >= 0; $i--) {
          $fecha = date('Y-m-d', strtotime("-$i days"));
          $estado = $estados[array_rand($estados)];
          $db->exec("INSERT INTO registros (fecha, estado_animo) VALUES ('$fecha', '$estado')");
      }
  }
  ```

### 6. Mantenimiento y Mejoras Futuras

- **Monitoreo**: Seguimiento del funcionamiento de la aplicación
- **Actualizaciones**: Plan para implementar nuevas funcionalidades como exportación de datos
- **Optimización**: Mejoras continuas en rendimiento y experiencia de usuario
