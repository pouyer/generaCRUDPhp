
SET FOREIGN_KEY_CHECKS = 0;
SET SQL_MODE = '';

DROP VIEW IF EXISTS `v_acc_menu`;
DROP VIEW IF EXISTS `v_acc_programa`;
DROP VIEW IF EXISTS `v_acc_modulo`;
DROP VIEW IF EXISTS `v_acc_rol`;
DROP VIEW IF EXISTS `v_acc_usuario`;
DROP VIEW IF EXISTS `v_acc_rol_x_usuario`;
DROP VIEW IF EXISTS `v_acc_programa_x_rol`;

DROP TABLE IF EXISTS `acc_programa_x_rol`;
DROP TABLE IF EXISTS `acc_rol_x_usuario`;
DROP TABLE IF EXISTS `acc_programa`;
DROP TABLE IF EXISTS `acc_usuario`;
DROP TABLE IF EXISTS `acc_rol`;
DROP TABLE IF EXISTS `acc_modulo`;

CREATE TABLE `acc_modulo` (
  `id_modulo` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_modulo` varchar(125) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `acc_rol` (
  `id_rol` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(125) DEFAULT NULL,
  `estado` varchar(45) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_rol`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `acc_usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(128) NOT NULL,
  `fullname` varchar(250) NOT NULL,
  `correo` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `estado` varchar(45) NOT NULL DEFAULT 'A',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `acc_programa` (
  `id_programas` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_menu` varchar(128) DEFAULT NULL,
  `ruta` varchar(250) DEFAULT NULL,
  `nombre_archivo` varchar(150) DEFAULT NULL,
  `orden` int(11) DEFAULT NULL,
  `estado` varchar(45) DEFAULT 'A',
  `id_modulo` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_programas`),
  KEY `programas_id_modulo_fk` (`id_modulo`),
  CONSTRAINT `programas_id_modulo_fk` FOREIGN KEY (`id_modulo`) REFERENCES `acc_modulo` (`id_modulo`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `acc_rol_x_usuario` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_usuario`,`id_rol`),
  KEY `rol_x_usuario_id_rol_fk` (`id_rol`),
  CONSTRAINT `rol_x_usuario_id_rol_fk` FOREIGN KEY (`id_rol`) REFERENCES `acc_rol` (`id_rol`),
  CONSTRAINT `rol_x_usuario_id_usuario_fk` FOREIGN KEY (`id_usuario`) REFERENCES `acc_usuario` (`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `acc_programa_x_rol` (
  `id_programas` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id_programas`,`id_rol`),
  KEY `programa_x_rol_id_rol_fk` (`id_rol`),
  CONSTRAINT `programa_x_rol_id_programas_fk` FOREIGN KEY (`id_programas`) REFERENCES `acc_programa` (`id_programas`),
  CONSTRAINT `programa_x_rol_id_rol_fk` FOREIGN KEY (`id_rol`) REFERENCES `acc_rol` (`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `acc_modulo` (`nombre_modulo`, `orden`, `estado`) 
VALUES ('Seguridad y Accesos', 100, 'A');

INSERT INTO `acc_rol` (`nombre_rol`, `estado`) 
VALUES ('Super Administrador', 'A'), ('Administrador Accesos', 'A');

INSERT INTO `acc_usuario` (`username`, `fullname`, `correo`, `password`, `estado`) 
VALUES ('admin', 'Super Administrador', 'admin@admin.com', 'admin', 'A');

INSERT INTO `acc_programa` ( `nombre_menu`, `ruta`, `nombre_archivo`, `orden`, `estado`, `id_modulo`) 
VALUES ('Modulos',NULL,'vista_acc_modulo.php',1,'A',1),
('Roles',NULL,'vista_acc_rol.php',2,'A',1),
('Usuarios',NULL,'vista_acc_usuario.php',3,'A',1),
('Programas',NULL,'vista_acc_programa.php',4,'A',1),
('Programas por Rol',NULL,'vista_roles_programas.php',5,'A',1);

INSERT INTO `acc_rol_x_usuario` (`id_usuario`, `id_rol`) 
SELECT 1, id_rol FROM acc_rol WHERE nombre_rol IN ('Super Administrador', 'Administrador Accesos');

INSERT INTO `acc_programa_x_rol` (`id_programas`, `id_rol`)
select `id_programas` ,`r`.`id_rol`  from `acc_programa` `p` , `acc_rol` `r` 
WHERE `nombre_rol` IN ('Super Administrador', 'Administrador Accesos')
 order by `r`.`id_rol`, `id_programas`;

CREATE VIEW `v_acc_menu` AS
    SELECT DISTINCT
        `p`.`nombre_menu` AS `nombre_menu`,
        `p`.`ruta` AS `ruta_programa`,
        `p`.`nombre_archivo` AS `nombre_programaPHP`,
        `u`.`username` AS `username`,
        `u`.`id_usuario` AS `id_usuario`,
        `m`.`nombre_modulo` AS `modulo`
    FROM
        (((((`acc_usuario` `u`
        JOIN `acc_rol_x_usuario` `ru` ON (`ru`.`id_usuario` = `u`.`id_usuario`))
        JOIN `acc_programa_x_rol` `pr` ON (`pr`.`id_rol` = `ru`.`id_rol`))
        JOIN `acc_rol` `r` ON (`r`.`id_rol` = `pr`.`id_rol`))
        JOIN `acc_programa` `p` ON (`p`.`id_programas` = `pr`.`id_programas`))
        LEFT JOIN `acc_modulo` `m` ON (`m`.`id_modulo` = `p`.`id_modulo`))
    WHERE
        `u`.`estado` = 'A'
        AND `p`.`estado` = 'A'
        AND `r`.`estado` = 'A'
    ORDER BY `m`.`orden`, `p`.`orden`;

CREATE VIEW `v_acc_programa` AS
    SELECT 
        `p`.`id_programas` AS `id_programas`,
        `p`.`nombre_menu` AS `nombre_menu`,
        `p`.`ruta` AS `ruta`,
        `p`.`nombre_archivo` AS `nombre_archivo`,
        `p`.`id_modulo` AS `id_modulo`,
        `p`.`orden` AS `orden`,
        `p`.`estado` AS `estado`,
        CASE
            WHEN `p`.`estado` = 'A' THEN 'Activo'
            WHEN `p`.`estado` = 'I' THEN 'Inactivo'
            ELSE `p`.`estado`
        END AS `nombre_estado`,
        `m`.`nombre_modulo` AS `nombre_modulo`,
        `p`.`fecha_creacion` AS `fecha_creacion`
    FROM
        (`acc_programa` `p`
        LEFT JOIN `acc_modulo` `m` ON (`m`.`id_modulo` = `p`.`id_modulo`));    

CREATE VIEW `v_acc_modulo` AS
    SELECT 
        `m`.`id_modulo` AS `id_modulo`,
        `m`.`nombre_modulo` AS `nombre_modulo`,
        `m`.`orden` AS `orden`,
        `m`.`estado` AS `estado`,
        CASE
            WHEN `m`.`estado` = 'A' THEN 'Activo'
            WHEN `m`.`estado` = 'I' THEN 'Inactivo'
            ELSE `m`.`estado`
        END AS `nombre_estado`,
        `m`.`fecha_creacion` AS `fecha_creacion`
    FROM
        `acc_modulo` `m`;

CREATE VIEW `v_acc_rol` AS
    SELECT 
        `r`.`id_rol` AS `id_rol`,
        `r`.`nombre_rol` AS `nombre_rol`,
        `r`.`estado` AS `estado`,
        CASE
            WHEN `r`.`estado` = 'A' THEN 'Activo'
            WHEN `r`.`estado` = 'I' THEN 'Inactivo'
            ELSE `r`.`estado`
        END AS `nombre_estado`,
        `r`.`fecha_creacion` AS `fecha_creacion`
    FROM
        `acc_rol` `r` ;

CREATE VIEW `v_acc_usuario` AS
    SELECT 
        `u`.`id_usuario` AS `id_usuario`,
        `u`.`username` AS `username`,
        `u`.`fullname` AS `fullname`,
        `u`.`correo` AS `correo`,
        `u`.`password` AS `password`,
        `u`.`estado` AS `estado`,
        CASE
            WHEN `u`.`estado` = 'A' THEN 'Activo'
            WHEN `u`.`estado` = 'I' THEN 'Inactivo'
            ELSE `u`.`estado`
        END AS `nombre_estado`,
        `u`.`fecha_creacion` AS `fecha_creacion`
    FROM
        `acc_usuario` `u` ;            

CREATE VIEW `v_acc_rol_x_usuario` AS
    SELECT 
        `ru`.`id_usuario` AS `id_usuario`,
        IFNULL(`u`.`fullname`, `u`.`username`) AS `nombre_usuario`,
        `ru`.`id_rol` AS `id_rol`,
        `r`.`nombre_rol` AS `nombre_rol`,
        `ru`.`fecha_creacion` AS `fecha_creacion`
    FROM
        ((`acc_rol_x_usuario` `ru`
        JOIN `acc_usuario` `u` ON (`u`.`id_usuario` = `ru`.`id_usuario`))
        JOIN `acc_rol` `r` ON (`r`.`id_rol` = `ru`.`id_rol`));

CREATE VIEW `v_acc_programa_x_rol` AS
    SELECT 
        `pr`.`id_programas` AS `id_programas`,
        `p`.`nombre_menu` AS `nombre_programa`,
        `pr`.`id_rol` AS `id_rol`,
        `r`.`nombre_rol` AS `nombre_rol`,
        `pr`.`fecha_creacion` AS `fecha_creacion`
    FROM
        ((`acc_programa_x_rol` `pr`
        JOIN `acc_programa` `p` ON (`p`.`id_programas` = `pr`.`id_programas`))
        JOIN `acc_rol` `r` ON (`r`.`id_rol` = `pr`.`id_rol`));        

SET FOREIGN_KEY_CHECKS = 1;
