-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-11-2026 a las 13:52:45
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sigsm`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad_sesion`
--

CREATE TABLE `actividad_sesion` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `rol` enum('medico','administrativo') NOT NULL,
  `ip` varchar(45) NOT NULL,
  `fecha_ingreso` datetime NOT NULL DEFAULT current_timestamp(),
  `ultima_actividad` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_salida` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `actividad_sesion`
--

INSERT INTO `actividad_sesion` (`id`, `usuario_id`, `email`, `rol`, `ip`, `fecha_ingreso`, `ultima_actividad`, `fecha_salida`) VALUES
(1, 3, 'falerotiago2026@gmail.com', 'administrativo', '::1', '2026-10-27 17:30:34', '2026-10-27 17:31:17', '2026-10-27 17:31:17'),
(2, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-27 17:31:42', '2026-10-27 17:32:15', '2026-10-27 17:32:15'),
(3, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-10-27 18:36:39', '2026-10-27 18:36:46', '2026-10-27 18:36:46'),
(4, 3, 'falerotiago2026@gmail.com', 'administrativo', '::1', '2026-10-27 18:37:03', '2026-10-27 18:37:08', '2026-10-27 18:37:08'),
(5, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-10-27 20:01:05', '2026-10-27 20:01:16', '2026-10-27 20:01:16'),
(6, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-27 20:01:31', '2026-10-27 20:38:56', '2026-10-27 20:38:56'),
(7, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-27 20:39:13', '2026-10-27 20:55:07', '2026-10-27 20:55:07'),
(8, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-27 20:57:33', '2026-10-27 21:39:03', '2026-10-27 21:39:03'),
(9, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-10-27 21:41:53', '2026-10-27 21:41:55', '2026-10-27 21:41:55'),
(10, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-27 21:42:30', '2026-10-27 21:44:48', '2026-10-27 21:44:48'),
(11, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-27 22:26:31', '2026-10-27 22:26:31', NULL),
(12, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-28 09:37:30', '2026-10-28 09:39:21', '2026-10-28 09:39:21'),
(13, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-10-28 09:40:19', '2026-10-28 09:40:42', '2026-10-28 09:40:42'),
(14, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-28 09:41:07', '2026-10-28 09:41:07', NULL),
(15, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-28 10:24:05', '2026-10-28 10:32:04', '2026-10-28 10:32:04'),
(16, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-28 11:20:39', '2026-10-28 11:25:52', '2026-10-28 11:25:52'),
(17, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-28 12:35:05', '2026-10-28 13:28:25', NULL),
(18, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-28 13:32:09', '2026-10-28 13:32:09', NULL),
(19, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 09:40:53', '2026-10-29 12:09:49', NULL),
(20, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 13:49:55', '2026-10-29 16:13:18', '2026-10-29 16:13:18'),
(21, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 16:15:36', '2026-10-29 16:17:41', '2026-10-29 16:17:41'),
(22, 5, 'felipe.flor2.garcia@gmail.com', 'administrativo', '::1', '2026-10-29 16:17:46', '2026-10-29 16:20:10', '2026-10-29 16:20:10'),
(23, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 16:20:19', '2026-10-29 16:23:47', '2026-10-29 16:23:47'),
(24, 5, 'felipe.flor2.garcia@gmail.com', 'administrativo', '::1', '2026-10-29 16:23:56', '2026-10-29 16:24:55', '2026-10-29 16:24:55'),
(25, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 16:25:03', '2026-10-29 16:25:33', '2026-10-29 16:25:33'),
(26, 5, 'felipe.flor2.garcia@gmail.com', 'administrativo', '::1', '2026-10-29 16:25:37', '2026-10-29 16:26:48', NULL),
(27, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 17:16:20', '2026-10-29 17:17:23', NULL),
(28, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-29 20:09:52', '2026-10-29 20:10:11', '2026-10-29 20:10:11'),
(29, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-30 07:45:30', '2026-10-30 08:02:26', '2026-10-30 08:02:26'),
(30, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-30 08:12:31', '2026-10-30 12:19:45', '2026-10-30 12:19:45'),
(31, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-30 13:59:15', '2026-10-30 19:53:17', '2026-10-30 19:53:17'),
(32, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-10-30 19:54:02', '2026-10-30 21:25:36', '2026-10-30 21:25:36'),
(33, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-11-01 14:10:48', '2026-11-01 21:22:52', '2026-11-01 21:22:52'),
(34, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-11-02 14:00:31', '2026-11-02 14:12:25', '2026-11-02 14:12:25'),
(35, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-11-02 14:12:44', '2026-11-02 14:15:07', '2026-11-02 14:15:07'),
(36, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-11-02 14:15:25', '2026-11-02 14:15:55', '2026-11-02 14:15:55'),
(37, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-11-02 14:16:11', '2026-11-02 14:17:42', '2026-11-02 14:17:42'),
(38, 1, 'triit2026@gmail.com', 'administrativo', '::1', '2026-11-02 14:26:40', '2026-11-02 14:29:03', '2026-11-02 14:29:03'),
(39, 4, 'pedrorodriguez@gmail.com', 'medico', '::1', '2026-11-02 14:30:04', '2026-11-02 14:30:30', '2026-11-02 14:30:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `activa` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id`, `nombre`, `activa`) VALUES
(1, 'C.T.I', 1),
(2, 'CARDIOLOGÍA', 1),
(3, 'CENTRO CARDIOVASCULAR', 1),
(4, 'CLÍNICA MÉDICA A', 1),
(5, 'CLÍNICA MÉDICA B', 1),
(6, 'CLÍNICA MÉDICA C', 1),
(7, 'DERMATOLOGÍA', 1),
(8, 'EMERGENCIA', 1),
(9, 'ENDOCRINOLOGÍA', 1),
(10, 'GASTROENTEROLOGÍA', 1),
(11, 'GERIATRÍA', 1),
(12, 'HEMATOLOGÍA', 1),
(13, 'INFECTOLOGÍA', 1),
(14, 'MEDICINA FÍSICA, REHABILITACIÓN Y MEDICINA DEL DEPORTE', 1),
(15, 'NEFROLOGÍA', 1),
(16, 'NEONATOLOGÍA', 1),
(17, 'NEUROLOGÍA', 1),
(18, 'ONCOLOGÍA', 1),
(19, 'PSIQUIATRÍA', 1),
(20, 'U.E. AUTOINMUNES SISTÉMICAS', 1),
(21, 'UNIDAD DE TABAQUISMO', 1),
(22, 'ANESTESIOLOGÍA', 1),
(23, 'CIRUGÍA CARDÍACA', 1),
(24, 'CIRUGÍA PLÁSTICA Y QUEMADOS', 1),
(25, 'CIRUGÍA VASCULAR PERIFÉRICA', 1),
(26, 'GINECOTOCOLOGICA B', 1),
(27, 'NEUROCIRUGÍA', 1),
(28, 'ODONTOLOGÍA', 1),
(29, 'OFTALMOLOGÍA', 1),
(30, 'OTORRINOLARINGOLOGÍA', 1),
(31, 'QUIRÚRGICA A', 1),
(32, 'QUIRÚRGICA B', 1),
(33, 'QUIRÚRGICA F', 1),
(34, 'UROLOGÍA', 1),
(35, 'TRAUMATOLOGÍA Y ORTOPEDIA DE ADULTOS', 1),
(36, 'CENTELLOGRAMA DE PERFUSIÓN MIOCÁRDICA', 1),
(37, 'ESTUDIOS DIAGNÓSTICOS CON PERTECNETATO', 1),
(38, 'INDICACIONES DE ENFERMERÍA PARA USUARIOS TRASPLANTADOS', 1),
(39, 'INDICACIONES DE INTERRUPCIÓN VOLUNTARIA DEL EMBARAZO', 1),
(40, 'INDICACIONES ECOCARDIOGRAMA CON DOBUTAMINA', 1),
(41, 'INDICACIONES ECOCARDIOGRAMA TRANSESOFÁGICO', 1),
(42, 'INDICACIONES PARA INGRESO A CENTRO DE NEFROLOGÍA Y TRASPLANTE', 1),
(43, 'INDICACIONES PARA PACIENTES EN TRATAMIENTO CON WARFARINA', 1),
(44, 'PLAN DE ALTA ENFERMERÍA, NEFROLOGÍA', 1),
(45, 'PREPARACIÓN PARA ESTUDIOS IMAGENOLÓGICOS', 1),
(46, 'PREVENCIÓN DE INFECCIONES', 1),
(47, 'PROSTATECTOMÍA RADICAL (INDICACIONES E INFORMACIÓN PARA EL PACIENTE)', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `documento`
--

CREATE TABLE `documento` (
  `id` int(11) NOT NULL,
  `token` char(12) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `categoria_id` int(11) NOT NULL,
  `archivo_nombre` varchar(255) NOT NULL,
  `archivo_original` varchar(255) NOT NULL,
  `visitas` int(10) UNSIGNED NOT NULL DEFAULT 0,
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `documento`
--

INSERT INTO `documento` (`id`, `token`, `titulo`, `descripcion`, `categoria_id`, `archivo_nombre`, `archivo_original`, `visitas`, `activo`, `creado_por`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'a26c2ee5c91c', 'IT Dictionary Camilo Masseras', NULL, 1, 'b602f24fa9bac560c6d807e4d7523e36.pdf', 'IT Dictionary - Camilo Masseras - Handouts 1, 2, 3, 4 and 6.pdf', 8, 1, 2, '2026-10-21 20:11:36', '2026-10-21 20:11:36'),
(2, '7bafa701af9b', 'Modelado de Sistemas', 'Modelado de sistemas de Ingenieria de Software', 23, 'a317ce8edb3b87468fe5501ac428507c.pdf', 'Modelado de Sistemas.pdf', 7, 1, 1, '2026-10-21 21:46:21', '2026-10-27 15:06:01'),
(3, 'f2ea1004c2cb', 'Medicación para regular Tabaquismo', NULL, 21, 'eb0abbd1a4e3e3eb921281077a3518de.pdf', 'Project_Scope_-_Text_Worksheet.pdf', 4, 1, 1, '2026-11-01 14:12:36', '2026-11-01 14:12:36');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta`
--

CREATE TABLE `encuesta` (
  `id` int(10) UNSIGNED NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` varchar(500) NOT NULL DEFAULT '',
  `categoria_id` int(11) DEFAULT NULL,
  `token` char(12) NOT NULL,
  `estado` enum('borrador','activa','cerrada') NOT NULL DEFAULT 'borrador',
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `encuesta`
--

INSERT INTO `encuesta` (`id`, `titulo`, `descripcion`, `categoria_id`, `token`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 'Encuesta de Satisfacción general', 'Esta es una encuesta de satisfacción creada para fines de prueba att Triit.', NULL, 'e65abd5e53d2', 'activa', '2026-10-27 20:49:16', '2026-10-30 12:17:19'),
(2, 'Encuesta satisfacción Atención del personal', 'Esta es una encuesta de satisfacción sobre la atención brindada por el personal del Hospital de Clínicas hacia los pacientes.', NULL, 'af5d27e5e785', 'activa', '2026-11-01 14:16:49', '2026-11-01 14:16:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_opcion`
--

CREATE TABLE `encuesta_opcion` (
  `id` int(10) UNSIGNED NOT NULL,
  `pregunta_id` int(10) UNSIGNED NOT NULL,
  `orden` smallint(5) UNSIGNED NOT NULL,
  `texto` varchar(150) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_pregunta`
--

CREATE TABLE `encuesta_pregunta` (
  `id` int(10) UNSIGNED NOT NULL,
  `encuesta_id` int(10) UNSIGNED NOT NULL,
  `orden` smallint(5) UNSIGNED NOT NULL,
  `texto` varchar(300) NOT NULL,
  `tipo` enum('texto','escala','opcion') NOT NULL,
  `obligatoria` tinyint(1) NOT NULL DEFAULT 0,
  `multiple` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `encuesta_pregunta`
--

INSERT INTO `encuesta_pregunta` (`id`, `encuesta_id`, `orden`, `texto`, `tipo`, `obligatoria`, `multiple`) VALUES
(3, 1, 1, 'Coméntanos en qué área en general crees que podemos mejorar', 'texto', 1, 0),
(4, 1, 2, '¿Considera rápida la atención del Hospital de Clínicas a nivel general? Siendo 5 muy rápido y 1 poco rápido', 'escala', 1, 0),
(5, 2, 1, 'Siendo 1 muy malo y 5 excelente ¿Como calificaría la atención general que le brinda el personal del Hospital de Clínicas hacia usted?', 'escala', 1, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_respuesta`
--

CREATE TABLE `encuesta_respuesta` (
  `id` int(10) UNSIGNED NOT NULL,
  `encuesta_id` int(10) UNSIGNED NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `encuesta_respuesta`
--

INSERT INTO `encuesta_respuesta` (`id`, `encuesta_id`, `fecha`) VALUES
(1, 1, '2026-10-30 21:38:16'),
(3, 1, '2026-11-01 20:43:03'),
(4, 1, '2026-11-01 21:20:52'),
(2, 2, '2026-11-01 14:17:56');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuesta_respuesta_detalle`
--

CREATE TABLE `encuesta_respuesta_detalle` (
  `id` int(10) UNSIGNED NOT NULL,
  `respuesta_id` int(10) UNSIGNED NOT NULL,
  `pregunta_id` int(10) UNSIGNED NOT NULL,
  `valor_texto` text DEFAULT NULL,
  `valor_escala` tinyint(3) UNSIGNED DEFAULT NULL,
  `opcion_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `encuesta_respuesta_detalle`
--

INSERT INTO `encuesta_respuesta_detalle` (`id`, `respuesta_id`, `pregunta_id`, `valor_texto`, `valor_escala`, `opcion_id`) VALUES
(1, 1, 3, 'me gustaria que se mejorara la área de emergencias, siempre hay que esperar mucho', NULL, NULL),
(2, 1, 4, NULL, 3, NULL),
(3, 2, 5, NULL, 4, NULL),
(4, 3, 3, 'en tdas manga de giles de guan', NULL, NULL),
(5, 3, 4, NULL, 1, NULL),
(6, 4, 3, 'ustedes se caen a pedazos', NULL, NULL),
(7, 4, 4, NULL, 2, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('medico','administrativo') NOT NULL DEFAULT 'medico',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `debe_cambiar_clave` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `email`, `password_hash`, `rol`, `activo`, `debe_cambiar_clave`) VALUES
(1, 'TRIIT', 'triit2026@gmail.com', '$2y$10$2XHY8AclVbPjrmFHzq1zLuxgW1EtkmB3sJs6n1DeSWsBhiV3o6WwG', 'administrativo', 1, 0),
(2, 'PRUEBA', 'prueba@gmail.com', '$2y$10$t5oxs8z0puDBHSSRfRaCBe022nPpkLbownu/SMVu8j10OuD5M1RRq', 'administrativo', 0, 0),
(3, 'Camilo Masseras', 'falerotiago2026@gmail.com', '$2y$10$pVfAmoMb7NnB9HUweZYGGOGOTx5fKiNl/.wriaAVbWtIpnIzBKXR.', 'administrativo', 1, 0),
(4, 'Pedro Rodriguéz', 'pedrorodriguez@gmail.com', '$2y$10$eNr9ALDK86b5z/QJ.mdu..iUqE5YdkEr5OvLGrvpaxXJ4UKFmXJEu', 'medico', 1, 0),
(5, 'Felipe García', 'felipe.flor2.garcia@gmail.com', '$2y$10$hG2FlnW.5psBfVhtpkJlsObj6F1oD6qyjsdPNlG.cd9j0/CISzacO', 'administrativo', 1, 0);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad_sesion`
--
ALTER TABLE `actividad_sesion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_actividad_ingreso` (`fecha_ingreso`),
  ADD KEY `idx_actividad_usuario` (`usuario_id`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`);

--
-- Indices de la tabla `documento`
--
ALTER TABLE `documento`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `categoria_id` (`categoria_id`),
  ADD KEY `creado_por` (`creado_por`);

--
-- Indices de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uk_encuesta_token` (`token`),
  ADD KEY `fk_encuesta_categoria` (`categoria_id`);

--
-- Indices de la tabla `encuesta_opcion`
--
ALTER TABLE `encuesta_opcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_opcion_pregunta` (`pregunta_id`,`orden`);

--
-- Indices de la tabla `encuesta_pregunta`
--
ALTER TABLE `encuesta_pregunta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pregunta_encuesta` (`encuesta_id`,`orden`);

--
-- Indices de la tabla `encuesta_respuesta`
--
ALTER TABLE `encuesta_respuesta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_respuesta_encuesta` (`encuesta_id`,`fecha`);

--
-- Indices de la tabla `encuesta_respuesta_detalle`
--
ALTER TABLE `encuesta_respuesta_detalle`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_detalle_respuesta` (`respuesta_id`),
  ADD KEY `idx_detalle_pregunta` (`pregunta_id`),
  ADD KEY `fk_detalle_opcion` (`opcion_id`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad_sesion`
--
ALTER TABLE `actividad_sesion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

--
-- AUTO_INCREMENT de la tabla `documento`
--
ALTER TABLE `documento`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `encuesta`
--
ALTER TABLE `encuesta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `encuesta_opcion`
--
ALTER TABLE `encuesta_opcion`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `encuesta_pregunta`
--
ALTER TABLE `encuesta_pregunta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `encuesta_respuesta`
--
ALTER TABLE `encuesta_respuesta`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `encuesta_respuesta_detalle`
--
ALTER TABLE `encuesta_respuesta_detalle`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividad_sesion`
--
ALTER TABLE `actividad_sesion`
  ADD CONSTRAINT `actividad_sesion_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `documento`
--
ALTER TABLE `documento`
  ADD CONSTRAINT `documento_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`),
  ADD CONSTRAINT `documento_ibfk_2` FOREIGN KEY (`creado_por`) REFERENCES `usuario` (`id`);

--
-- Filtros para la tabla `encuesta`
--
ALTER TABLE `encuesta`
  ADD CONSTRAINT `fk_encuesta_categoria` FOREIGN KEY (`categoria_id`) REFERENCES `categoria` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `encuesta_opcion`
--
ALTER TABLE `encuesta_opcion`
  ADD CONSTRAINT `fk_opcion_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `encuesta_pregunta` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `encuesta_pregunta`
--
ALTER TABLE `encuesta_pregunta`
  ADD CONSTRAINT `fk_pregunta_encuesta` FOREIGN KEY (`encuesta_id`) REFERENCES `encuesta` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `encuesta_respuesta`
--
ALTER TABLE `encuesta_respuesta`
  ADD CONSTRAINT `fk_respuesta_encuesta` FOREIGN KEY (`encuesta_id`) REFERENCES `encuesta` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `encuesta_respuesta_detalle`
--
ALTER TABLE `encuesta_respuesta_detalle`
  ADD CONSTRAINT `fk_detalle_opcion` FOREIGN KEY (`opcion_id`) REFERENCES `encuesta_opcion` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_pregunta` FOREIGN KEY (`pregunta_id`) REFERENCES `encuesta_pregunta` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_detalle_respuesta` FOREIGN KEY (`respuesta_id`) REFERENCES `encuesta_respuesta` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
