-- Crear la base de datos si no existe
CREATE DATABASE IF NOT EXISTS neticlar_db;
USE neticlar_db;

-- Tabla para almacenar las secciones de contenido
CREATE TABLE IF NOT EXISTS content_sections (
    id INT AUTO_INCREMENT PRIMARY KEY,
    section_id VARCHAR(50) NOT NULL,
    element_id VARCHAR(100) NOT NULL,
    content_type ENUM('heading', 'paragraph', 'text', 'list_item') NOT NULL,
    content TEXT NOT NULL,
    page_source VARCHAR(50) NOT NULL DEFAULT 'index',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_element (section_id, element_id, page_source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
