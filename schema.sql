-- S.I.G.S.M. — Módulo de gestión de documentación para pacientes
-- ATENCIÓN: la siguiente línea borra la tabla `documento` anterior (datos de prueba).
DROP TABLE IF EXISTS documento;

CREATE TABLE IF NOT EXISTS usuario (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100) NOT NULL,
    email         VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol           VARCHAR(20)  NOT NULL DEFAULT 'funcionario',
    activo        TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS categoria (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(100) NOT NULL UNIQUE,
    activa  TINYINT(1)   NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documento (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    token               CHAR(12)     NOT NULL UNIQUE,   -- identificador público (va en el QR)
    titulo              VARCHAR(150) NOT NULL,
    descripcion         VARCHAR(500) NULL,
    categoria_id        INT          NOT NULL,
    archivo_nombre      VARCHAR(255) NOT NULL,          -- nombre generado por el servidor
    archivo_original    VARCHAR(255) NOT NULL,          -- nombre con el que lo subió el funcionario
    visitas             INT UNSIGNED NOT NULL DEFAULT 0,
    activo              TINYINT(1)   NOT NULL DEFAULT 1,
    creado_por          INT          NOT NULL,
    fecha_creacion      DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categoria(id),
    FOREIGN KEY (creado_por)   REFERENCES usuario(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT IGNORE INTO categoria (nombre) VALUES
('Ginecología y obstetricia'),
('Urología'),
('Imagenología'),
('Medicina nuclear'),
('Cardiología'),
('Nefrología y trasplante'),
('Enfermería'),
('Prevención de infecciones'),
('Ostomías');
