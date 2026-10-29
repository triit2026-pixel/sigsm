-- 001_encuestas.sql: módulo de encuestas anónimas (no toca las tablas existentes)
SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS encuesta (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  titulo VARCHAR(150) NOT NULL,
  descripcion VARCHAR(500) NOT NULL DEFAULT '',
  token CHAR(12) NOT NULL,
  estado ENUM('borrador','activa','cerrada') NOT NULL DEFAULT 'borrador',
  fecha_creacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_actualizacion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uk_encuesta_token (token)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS encuesta_pregunta (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  encuesta_id INT UNSIGNED NOT NULL,
  orden SMALLINT UNSIGNED NOT NULL,
  texto VARCHAR(300) NOT NULL,
  tipo ENUM('texto','escala','opcion') NOT NULL,
  obligatoria TINYINT(1) NOT NULL DEFAULT 0,
  multiple TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (id),
  KEY idx_pregunta_encuesta (encuesta_id, orden),
  CONSTRAINT fk_pregunta_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuesta (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS encuesta_opcion (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  pregunta_id INT UNSIGNED NOT NULL,
  orden SMALLINT UNSIGNED NOT NULL,
  texto VARCHAR(150) NOT NULL,
  PRIMARY KEY (id),
  KEY idx_opcion_pregunta (pregunta_id, orden),
  CONSTRAINT fk_opcion_pregunta FOREIGN KEY (pregunta_id) REFERENCES encuesta_pregunta (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Cada envío de un paciente (anónimo: no guarda IP ni ningún dato personal)
CREATE TABLE IF NOT EXISTS encuesta_respuesta (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  encuesta_id INT UNSIGNED NOT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_respuesta_encuesta (encuesta_id, fecha),
  CONSTRAINT fk_respuesta_encuesta FOREIGN KEY (encuesta_id) REFERENCES encuesta (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Una fila por respuesta a una pregunta (en opción con varias marcas: una fila por opción)
CREATE TABLE IF NOT EXISTS encuesta_respuesta_detalle (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  respuesta_id INT UNSIGNED NOT NULL,
  pregunta_id INT UNSIGNED NOT NULL,
  valor_texto TEXT NULL,
  valor_escala TINYINT UNSIGNED NULL,
  opcion_id INT UNSIGNED NULL,
  PRIMARY KEY (id),
  KEY idx_detalle_respuesta (respuesta_id),
  KEY idx_detalle_pregunta (pregunta_id),
  CONSTRAINT fk_detalle_respuesta FOREIGN KEY (respuesta_id) REFERENCES encuesta_respuesta (id) ON DELETE CASCADE,
  CONSTRAINT fk_detalle_pregunta FOREIGN KEY (pregunta_id) REFERENCES encuesta_pregunta (id) ON DELETE CASCADE,
  CONSTRAINT fk_detalle_opcion FOREIGN KEY (opcion_id) REFERENCES encuesta_opcion (id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
