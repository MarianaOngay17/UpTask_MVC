# UpTask_MVC
Proyecto Curso Desarrollo Web

Pasos iniciales:

Instalar dependencias

    npm install

Actualizar composer despues de instalar o actualizar archivos/dependencias

    composer update

Instalar phpmailer

    composer require phpmailer/phpmailer

Base de datos

    CREATE TABLE `usuarios` (
    `id` int NOT NULL AUTO_INCREMENT,
    `nombre` varchar(30) DEFAULT NULL,
    `email` varchar(30) DEFAULT NULL,
    `password` varchar(60) DEFAULT NULL,
    `token` varchar(15) DEFAULT NULL,
    `confirmado` tinyint(1) DEFAULT NULL,
    PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

    CREATE TABLE `proyectos` (
    `id` int NOT NULL AUTO_INCREMENT,
    `proyecto` varchar(60) DEFAULT NULL,
    `url` varchar(32) DEFAULT NULL,
    `propietarioId` int DEFAULT NULL,
    PRIMARY KEY (`id`),
    KEY `proyectos_usuarios_FK` (`propietarioId`),
    CONSTRAINT `proyectos_usuarios_FK` FOREIGN KEY (`propietarioId`) REFERENCES `usuarios` (`id`) ON DELETE SET NULL ON UPDATE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;