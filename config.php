<?php
$db = new SQLite3('database.db');

// Tabla de categorías
$db->exec("
  CREATE TABLE IF NOT EXISTS categorias (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre TEXT NOT NULL
  )
");

// Insertar categorías básicas si no existen
$db->exec("INSERT OR IGNORE INTO categorias (nombre) VALUES ('Trabajo'), ('Familia'), ('Salud'), ('Ocio')");

// Tabla de registros (con categoría)
$db->exec("
  CREATE TABLE IF NOT EXISTS registros (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    fecha DATE DEFAULT CURRENT_TIMESTAMP,
    estado_animo TEXT NOT NULL,
    nota TEXT,
    categoria_id INTEGER,
    FOREIGN KEY (categoria_id) REFERENCES categorias(id)
  )
");
?>