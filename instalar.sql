-- ============================================================
--  Kart-E-Mart — installation script

--preferrably just change the database name, not anydhing else
-- execute this command for create the necessary databases for the project


-- ── 1. PRODUCTOS ────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id          VARCHAR(50)     NOT NULL,
    nombre      VARCHAR(255)    NOT NULL,
    precio      DECIMAL(12,2)   NOT NULL DEFAULT 0.00,
    categoria   VARCHAR(80)     NOT NULL DEFAULT '',
    imagen      INT             NOT NULL DEFAULT 0,
    descripcion TEXT,
    ventas      INT             NOT NULL DEFAULT 0,
    disponible  TINYINT(1)      NOT NULL DEFAULT 1,
    tags_cache  VARCHAR(500)    NOT NULL DEFAULT '',
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 2. CATEGORÍAS ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(80) NOT NULL,
    UNIQUE KEY uq_categoria_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 3. TAGS ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS tags (
    id      INT AUTO_INCREMENT PRIMARY KEY,
    nombre  VARCHAR(80) NOT NULL,
    UNIQUE KEY uq_tag_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 4. RELACIÓN PRODUCTO ↔ TAGS ─────────────────────────────
CREATE TABLE IF NOT EXISTS producto_tags (
    producto_id VARCHAR(50) NOT NULL,
    tag_id      INT         NOT NULL,
    PRIMARY KEY (producto_id, tag_id),
    CONSTRAINT fk_pt_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pt_tag      FOREIGN KEY (tag_id)      REFERENCES tags(id)      ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 5. USUARIOS ─────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    usuario  VARCHAR(60)  NOT NULL,
    password VARCHAR(255) NOT NULL,
    nombre   VARCHAR(100) NOT NULL DEFAULT '',
    activo   TINYINT(1)   NOT NULL DEFAULT 1,
    creado   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ── 6. user admin as default ────────────────────────────
--  password: admin123  (change it inmediatly after install on usuarios.php)
INSERT IGNORE INTO usuarios (usuario, password, nombre)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

