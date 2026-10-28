CREATE TABLE IF NOT EXISTS ConsultaUsuarios (
    id_consulta INT UNSIGNED NOT NULL AUTO_INCREMENT,
    nombre_usuario VARCHAR(100) NOT NULL,
    correo_usuario VARCHAR(100) NOT NULL,
    motivo VARCHAR(255) NULL,
    asunto VARCHAR(255) NULL,
    mensaje TEXT NOT NULL,
    fecha_consulta DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_consulta),
    
)