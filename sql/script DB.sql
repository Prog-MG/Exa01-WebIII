
CREATE DATABASE IF NOT EXISTS mybox
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE mybox;


CREATE TABLE IF NOT EXISTS usuarios (
    usuario VARCHAR(15) NOT NULL,
    contra  CHAR(32)    NOT NULL,
    nombre  VARCHAR(30) NOT NULL,
    email   VARCHAR(50) NOT NULL,
    PRIMARY KEY (usuario)
) ENGINE=InnoDB;

ALTER TABLE usuarios MODIFY contra VARCHAR(255) NOT NULL;


CREATE TABLE IF NOT EXISTS elementos (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    usuario        VARCHAR(15)  NOT NULL,            
    id_padre       INT UNSIGNED NULL,                
    nombre         VARCHAR(100) NOT NULL,
    es_carpeta     TINYINT(1)   NOT NULL DEFAULT 0,
    comentario     VARCHAR(50)  NULL,
    extension      VARCHAR(10)  NULL,                
    tipo_mime      VARCHAR(100) NULL,
    tamano         BIGINT UNSIGNED NOT NULL DEFAULT 0,
    contenido      LONGBLOB     NULL,
    fecha_creacion DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_usuario_padre (usuario, id_padre),
    CONSTRAINT fk_elem_usuario FOREIGN KEY (usuario)
        REFERENCES usuarios (usuario) ON DELETE CASCADE,
    CONSTRAINT fk_elem_padre FOREIGN KEY (id_padre)
        REFERENCES elementos (id) ON DELETE CASCADE   
) ENGINE=InnoDB;


CREATE TABLE IF NOT EXISTS comparticiones (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_elemento     INT UNSIGNED NOT NULL,
    usuario_destino VARCHAR(15)  NOT NULL,           
    fecha           DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_elemento_destino (id_elemento, usuario_destino),
    CONSTRAINT fk_comp_elem FOREIGN KEY (id_elemento)
        REFERENCES elementos (id) ON DELETE CASCADE,
    CONSTRAINT fk_comp_dest FOREIGN KEY (usuario_destino)
        REFERENCES usuarios (usuario) ON DELETE CASCADE
) ENGINE=InnoDB;
