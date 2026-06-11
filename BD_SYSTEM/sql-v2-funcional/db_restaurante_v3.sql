-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 11, 2026 at 04:13 AM
-- Server version: 8.0.30
-- PHP Version: 8.1.10

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_restaurante`
--

-- --------------------------------------------------------

--
-- Table structure for table `asistencia`
--

DROP TABLE IF EXISTS `asistencia`;
CREATE TABLE `asistencia` (
  `AsistenciaID` int NOT NULL,
  `EmpleadoID` int NOT NULL,
  `Fecha` date NOT NULL,
  `Hora_Entrada` time DEFAULT NULL,
  `Hora_Salida` time DEFAULT NULL,
  `Horas_Extra` decimal(5,2) NOT NULL DEFAULT '0.00',
  `Observaciones` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `asistencia`
--

INSERT INTO `asistencia` (`AsistenciaID`, `EmpleadoID`, `Fecha`, `Hora_Entrada`, `Hora_Salida`, `Horas_Extra`, `Observaciones`) VALUES
(1, 1, '2026-05-18', '07:55:00', '20:10:00', '0.00', NULL),
(2, 2, '2026-05-18', '07:50:00', '14:05:00', '0.00', NULL),
(3, 3, '2026-05-18', '08:02:00', '14:00:00', '0.00', NULL),
(4, 4, '2026-05-18', '14:00:00', '20:30:00', '0.50', 'Turno extendido por evento'),
(5, 5, '2026-05-18', '07:45:00', '20:15:00', '0.25', NULL),
(6, 6, '2026-05-18', '08:00:00', '14:00:00', '0.00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `caja`
--

DROP TABLE IF EXISTS `caja`;
CREATE TABLE `caja` (
  `CajaID` int NOT NULL,
  `EmpleadoID` int NOT NULL,
  `Monto_Apertura` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Monto_Cierre` decimal(10,2) DEFAULT NULL,
  `Fecha_hora_Apertura` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Fecha_hora_Cierre` datetime DEFAULT NULL,
  `Estado` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Abierta',
  `Observaciones` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `caja`
--

INSERT INTO `caja` (`CajaID`, `EmpleadoID`, `Monto_Apertura`, `Monto_Cierre`, `Fecha_hora_Apertura`, `Fecha_hora_Cierre`, `Estado`, `Observaciones`) VALUES
(1, 2, '200.00', NULL, '2026-05-19 08:00:00', NULL, 'Abierta', NULL),
(2, 14, '200.00', '400.00', '2026-06-04 02:25:10', '2026-06-10 18:51:36', 'Cerrada', 'nada ayer\n\n[ARQUEO] Sobrante: S/ 200.00\nEsperado: S/ 200.00\nContado: S/ 400.00');

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `CatID` int NOT NULL,
  `Nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Descripcion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Plato'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`CatID`, `Nombre`, `Descripcion`, `Tipo`) VALUES
(1, 'Arroces y Chaufas', 'Platos a base de arroz salteado al wok', 'Plato'),
(2, 'Sopas y Caldos', 'Caldos y sopas preparados al momento', 'Plato'),
(3, 'Carnes al Wok', 'Carnes y mariscos salteados con verduras', 'Plato'),
(4, 'Bebidas', 'Bebidas frías y calientes', 'Plato'),
(5, 'Granos y Cereales', 'Arroz, fideos y harinas', 'Insumo'),
(6, 'Carnes y Aves', 'Pollo, chancho y res', 'Insumo'),
(7, 'Verduras', 'Verduras frescas y congeladas', 'Insumo'),
(8, 'Salsas y Condimentos', 'Sillao, oyster sauce, aceites y especias', 'Insumo'),
(9, 'Combinados', 'Tallarin saltado con verduras y porción de chaufa', 'Insumo');

-- --------------------------------------------------------

--
-- Table structure for table `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `ClienteID` int NOT NULL,
  `Nombre_Apellidos` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tipo_Documento` char(5) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'DNI',
  `Num_Documento` varchar(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Direccion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Correo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `UsuarioID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Correo UNIQUE permite registro web; NULL para clientes anónimos presenciales';

--
-- Dumping data for table `cliente`
--

INSERT INTO `cliente` (`ClienteID`, `Nombre_Apellidos`, `Tipo_Documento`, `Num_Documento`, `Telefono`, `Direccion`, `Correo`, `Fecha_Creacion`, `UsuarioID`) VALUES
(1, 'Consumidor Final', 'DNI', '00000000', NULL, NULL, NULL, '2026-05-19 16:32:09', NULL),
(2, 'Pedro Salas Vega', 'DNI', '12345678', '987001122', 'Jr. Lima 123, Tumbes', 'pedro.salas@email.com', '2026-05-19 16:32:09', 6),
(3, 'Empresa SAC', 'RUC', '20512345678', '072-1234', 'Av. Principal 456, Tumbes', 'factura@empresa.com', '2026-05-19 16:32:09', NULL),
(4, 'Lucía Ramos Torres', 'DNI', '87654321', '976002233', 'Calle Los Jardines 78', 'lucia.ramos@email.com', '2026-05-19 16:32:09', 7),
(5, 'CARLO ANDRE MESTANZA', 'DNI', '77534805', '5589636582', 'Av. Siempre Viva 742', 'andre@wong.com', '2026-05-19 17:00:26', 8),
(6, 'LUIS CASTILLO VINCES', 'DNI', '74125896', '12345569', 'AV.', 'LUIS@matsue.com', '2026-05-21 16:57:48', 9),
(7, 'JANINA SALDAÑA', 'DNI', '78945612', '369857415', NULL, 'JANINA@GMAIL.COM', '2026-05-22 14:48:27', 10),
(8, 'ALDO GUERRERO', 'DNI', '86324058', '745896311', 'aa.hh- jiron lo almendros 127', 'lleyker@cliente.com', '2026-05-29 15:28:52', 23),
(9, 'juan', 'RUC', '12345678910', '987765432', NULL, 'pepito@gmail.com', '2026-05-29 15:48:17', 24),
(10, 'SARA SOSA CALDERO', 'DNI', '12345678', '966666666', NULL, 'sarita@gmail.com', '2026-06-04 01:01:30', 26),
(11, 'PRUEBA RESERVAS', 'DNI', '77534885', '937660992', 'Av. Siempre Viva 742', 'prueba@gmail.com', '2026-06-05 00:47:33', 27),
(12, 'Test Usuario', 'DNI', '00000000', '999999999', NULL, 'test@example.com', '2026-06-05 00:56:52', NULL),
(13, 'andremestanza', 'DNI', '00000000', '9857415269', NULL, 'andre@wo.com', '2026-06-05 00:59:55', NULL),
(14, 'CARLO MESTANZA', 'DNI', '00000000', '933965596', NULL, 'SUPUBASE@GMAIL.COM', '2026-06-05 01:31:35', NULL),
(15, 'CALOES', 'DNI', '00000000', '5874126933', NULL, 'CALOLE@DAWD.COM', '2026-06-05 01:37:04', NULL),
(16, 'DWDWD', 'DNI', '00000000', '7412583', NULL, 'DADWDCDD@KADW.COM', '2026-06-05 01:38:44', NULL),
(17, 'ADWDWD', 'DNI', '00000000', '74125863', NULL, 'CW@HMD.COM', '2026-06-05 01:39:43', NULL),
(18, 'andremestanza', 'DNI', '00000000', '741258963', NULL, 'gg@gmail.com', '2026-06-05 16:21:10', NULL),
(19, 'sara Lisbeth', 'DNI', '77044052', '928001411', NULL, 'sarasosa@gmail.com', '2026-06-05 16:31:40', 28);

-- --------------------------------------------------------

--
-- Table structure for table `comprobante_pago`
--

DROP TABLE IF EXISTS `comprobante_pago`;
CREATE TABLE `comprobante_pago` (
  `ComPagID` int NOT NULL,
  `PedidoID` int NOT NULL,
  `ClienteID` int DEFAULT NULL,
  `MetPagID` int NOT NULL,
  `Tipo_Comprobante` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Serie` char(4) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Numero` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `IGV` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Total` decimal(10,2) NOT NULL,
  `Fecha_Emision` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Referencia_Externa` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `comprobante_pago`
--

INSERT INTO `comprobante_pago` (`ComPagID`, `PedidoID`, `ClienteID`, `MetPagID`, `Tipo_Comprobante`, `Serie`, `Numero`, `IGV`, `Total`, `Fecha_Emision`, `Referencia_Externa`) VALUES
(1, 1, 1, 1, 'Boleta', 'B001', '00000001', '7.92', '44.00', '2026-05-19 16:32:09', NULL),
(2, 3, 2, 4, 'Boleta', 'B001', '00000002', '5.04', '28.00', '2026-05-19 16:32:09', 'TXN-CULQI-2026-001');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

DROP TABLE IF EXISTS `delivery`;
CREATE TABLE `delivery` (
  `DeliveryID` int NOT NULL,
  `PedidoID` int NOT NULL,
  `RepartidorID` int DEFAULT NULL,
  `ZonaID` int DEFAULT NULL,
  `Direccion_Entrega` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Referencia_Direccion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `Costo_Delivery` decimal(8,2) NOT NULL DEFAULT '0.00',
  `Fecha_Asignacion` datetime DEFAULT NULL,
  `Fecha_Entrega` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`DeliveryID`, `PedidoID`, `RepartidorID`, `ZonaID`, `Direccion_Entrega`, `Referencia_Direccion`, `Estado`, `Costo_Delivery`, `Fecha_Asignacion`, `Fecha_Entrega`) VALUES
(1, 3, NULL, 1, 'Jr. Lima 123, Tumbes', 'Casa amarilla, puerta azul', 'Pendiente', '3.00', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `detalle_modificador`
--

DROP TABLE IF EXISTS `detalle_modificador`;
CREATE TABLE `detalle_modificador` (
  `DetModID` int NOT NULL,
  `DetPedID` int NOT NULL,
  `OpcionID` int NOT NULL,
  `Precio_Aplicado` decimal(8,2) NOT NULL DEFAULT '0.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Snapshots de precios de modificadores al momento de la venta';

--
-- Dumping data for table `detalle_modificador`
--

INSERT INTO `detalle_modificador` (`DetModID`, `DetPedID`, `OpcionID`, `Precio_Aplicado`) VALUES
(1, 3, 1, '0.00'),
(2, 5, 9, '0.00');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_pedido`
--

DROP TABLE IF EXISTS `detalle_pedido`;
CREATE TABLE `detalle_pedido` (
  `DetPedID` int NOT NULL,
  `PedidoID` int NOT NULL,
  `VarianteID` int NOT NULL,
  `Cantidad` smallint NOT NULL,
  `Precio_Unitario` decimal(8,2) NOT NULL,
  `Subtotal` decimal(10,2) NOT NULL,
  `Notas` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`DetPedID`, `PedidoID`, `VarianteID`, `Cantidad`, `Precio_Unitario`, `Subtotal`, `Notas`) VALUES
(1, 1, 1, 2, '18.00', '36.00', NULL),
(2, 1, 9, 2, '4.00', '8.00', NULL),
(3, 2, 3, 1, '24.00', '24.00', NULL),
(4, 2, 5, 2, '16.00', '32.00', NULL),
(5, 3, 7, 1, '28.00', '28.00', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `empleado`
--

DROP TABLE IF EXISTS `empleado`;
CREATE TABLE `empleado` (
  `EmpleadoID` int NOT NULL,
  `Nombre_Apellidos` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `DNI` char(8) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Sueldo` decimal(8,2) NOT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `Fecha_Contratacion` datetime NOT NULL,
  `RolID` int NOT NULL,
  `TurnoID` int NOT NULL,
  `UsuarioID` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Personal del restaurante. UsuarioID NULL = sin acceso al sistema';

--
-- Dumping data for table `empleado`
--

INSERT INTO `empleado` (`EmpleadoID`, `Nombre_Apellidos`, `DNI`, `Telefono`, `Sueldo`, `Estado`, `Fecha_Contratacion`, `RolID`, `TurnoID`, `UsuarioID`) VALUES
(1, 'Carlos Mendoza Ríos', '45231876', '987654321', '2500.00', 'Activo', '2023-01-15 08:00:00', 1, 4, 1),
(2, 'María Torres Huanca', '52341987', '976543218', '1800.00', 'Activo', '2023-03-01 08:00:00', 2, 2, 2),
(3, 'José Quispe Lima', '63452198', '965432187', '1500.00', 'Activo', '2023-03-15 08:00:00', 3, 1, 3),
(4, 'Ana Flores Chávez', '74563209', '954321876', '1500.00', 'Activo', '2023-04-01 08:00:00', 3, 2, 4),
(5, 'Luis Wong Taipe', '85674310', '943218765', '2000.00', 'Activo', '2022-11-01 08:00:00', 4, 4, NULL),
(6, 'Rosa Mamani Ccopa', '96785425', '932187654', '1600.00', 'Activo', '2024-01-10 08:00:00', 5, 1, 5),
(14, 'Admin Sistema', '12345678', '999999999', '3000.00', 'Activo', '2026-05-29 01:10:11', 1, 1, 19),
(15, '', '75007544', '999999999', '3000.00', 'activo', '2026-05-29 01:58:51', 3, 1, 20),
(16, 'PIERO ANDRE MESTANZA VINCES', '41852258', '985698524', '1130.00', 'activo', '2026-05-29 15:21:27', 3, 4, 21),
(17, 'RAMOS CABREJO BESA GAMPIS', '77458968', '123654789', '1200.00', 'activo', '2026-05-29 15:25:30', 4, 2, 22),
(18, 'SARA SOSA CALDERON', '30080037', '999999999', '3000.00', 'activo', '2026-06-04 00:45:32', 5, 3, 25);

-- --------------------------------------------------------

--
-- Table structure for table `insumo`
--

DROP TABLE IF EXISTS `insumo`;
CREATE TABLE `insumo` (
  `InsumoID` int NOT NULL,
  `Nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Precio_Costo` decimal(8,2) NOT NULL DEFAULT '0.00',
  `Stock_Actual` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Stock_Minimo` decimal(10,2) NOT NULL DEFAULT '0.00',
  `Unidad_Medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Estado` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `CatID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Insumos de almacén. Nunca mezclar con Plato (carta del cliente)';

--
-- Dumping data for table `insumo`
--

INSERT INTO `insumo` (`InsumoID`, `Nombre`, `Descripcion`, `Precio_Costo`, `Stock_Actual`, `Stock_Minimo`, `Unidad_Medida`, `Estado`, `CatID`) VALUES
(1, 'Arroz largo', 'Arroz largo extra para chaufa', '2.50', '80.00', '20.00', 'kg', 'Activo', 5),
(2, 'Pollo entero', 'Pollo fresco por kilo', '8.50', '25.00', '5.00', 'kg', 'Activo', 6),
(3, 'Lomo de res', 'Lomo fino de res', '22.00', '10.00', '2.00', 'kg', 'Activo', 6),
(4, 'Cebolla china', 'Cebolla china fresca', '3.00', '8.00', '2.00', 'kg', 'Activo', 7),
(5, 'Huevo', 'Huevo de gallina fresco', '0.50', '60.00', '12.00', 'unidad', 'Activo', 7),
(6, 'Sillao oscuro', 'Sillao oscuro premium', '6.00', '5.00', '1.00', 'lt', 'Activo', 8),
(7, 'Aceite vegetal', 'Aceite para saltear al wok', '5.50', '10.00', '2.00', 'lt', 'Activo', 8),
(8, 'Camarones medianos', 'Camarones frescos pelados', '18.00', '4.00', '1.00', 'kg', 'Activo', 8);

-- --------------------------------------------------------

--
-- Table structure for table `integracion_externa`
--

DROP TABLE IF EXISTS `integracion_externa`;
CREATE TABLE `integracion_externa` (
  `IntExtID` int NOT NULL,
  `PedidoID` int NOT NULL,
  `Plataforma` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ID_Externo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Estado` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Recibido',
  `Comision` decimal(8,2) NOT NULL DEFAULT '0.00',
  `Fecha_Sync` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `kardex`
--

DROP TABLE IF EXISTS `kardex`;
CREATE TABLE `kardex` (
  `KardexID` int NOT NULL,
  `InsumoID` int NOT NULL,
  `ProveedorID` int DEFAULT NULL,
  `EmpleadoID` int DEFAULT NULL,
  `Tipo_Movimiento` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Cantidad` decimal(10,2) NOT NULL,
  `Precio_Unitario` decimal(8,2) NOT NULL DEFAULT '0.00',
  `Fecha` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Motivo` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Lote` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='EmpleadoID registra responsabilidad en mermas y ajustes';

--
-- Dumping data for table `kardex`
--

INSERT INTO `kardex` (`KardexID`, `InsumoID`, `ProveedorID`, `EmpleadoID`, `Tipo_Movimiento`, `Cantidad`, `Precio_Unitario`, `Fecha`, `Motivo`, `Lote`) VALUES
(1, 1, 1, 6, 'Entrada', '100.00', '2.50', '2026-05-19 16:32:09', 'Compra semanal de arroz', 'LOTE-2026-001'),
(2, 2, 2, 6, 'Entrada', '30.00', '8.50', '2026-05-19 16:32:09', 'Compra diaria de pollo', 'LOTE-2026-002'),
(3, 3, 2, 6, 'Entrada', '12.00', '22.00', '2026-05-19 16:32:09', 'Compra de lomo para la semana', 'LOTE-2026-003'),
(4, 8, 3, 6, 'Entrada', '5.00', '18.00', '2026-05-19 16:32:09', 'Compra de camarones frescos', 'LOTE-2026-004'),
(5, 1, NULL, 5, 'Salida', '20.00', '2.50', '2026-05-19 16:32:09', 'Consumo cocina turno mañana', NULL),
(6, 2, NULL, 5, 'Salida', '5.00', '8.50', '2026-05-19 16:32:09', 'Consumo cocina turno mañana', NULL),
(7, 5, NULL, 6, 'Merma', '3.00', '0.50', '2026-05-19 16:32:09', 'Huevos rotos en almacén', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `mesa`
--

DROP TABLE IF EXISTS `mesa`;
CREATE TABLE `mesa` (
  `MesaID` int NOT NULL,
  `Codigo_Mesa` varchar(10) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Numero_Mesa` smallint NOT NULL,
  `Capacidad` tinyint NOT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mesa`
--

INSERT INTO `mesa` (`MesaID`, `Codigo_Mesa`, `Numero_Mesa`, `Capacidad`, `Estado`) VALUES
(1, 'MESA-01', 2, 4, 'Disponible'),
(2, 'MESA-02', 2, 4, 'Disponible'),
(3, 'MESA-03', 3, 6, 'Disponible'),
(4, 'MESA-04', 4, 2, 'Disponible'),
(5, 'MESA-05', 5, 8, 'Disponible'),
(6, 'MESA-06', 6, 4, 'Disponible'),
(7, 'BARRA-01', 7, 2, 'Disponible'),
(8, 'MESA-07', 7, 6, 'Reservada');

-- --------------------------------------------------------

--
-- Table structure for table `metodo_pago`
--

DROP TABLE IF EXISTS `metodo_pago`;
CREATE TABLE `metodo_pago` (
  `MetPagID` int NOT NULL,
  `Nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Requiere_Referencia` tinyint NOT NULL DEFAULT '0',
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Catálogo configurable por el admin. Escalable a nuevas empresas';

--
-- Dumping data for table `metodo_pago`
--

INSERT INTO `metodo_pago` (`MetPagID`, `Nombre`, `Icono`, `Requiere_Referencia`, `Estado`) VALUES
(1, 'Efectivo', 'efectivo.png', 0, 1),
(2, 'Yape', 'yape.png', 1, 1),
(3, 'Plin', 'plin.png', 1, 1),
(4, 'Tarjeta', 'tarjeta.png', 1, 1),
(5, 'Transferencia', 'transferencia.png', 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `modificador_grupo`
--

DROP TABLE IF EXISTS `modificador_grupo`;
CREATE TABLE `modificador_grupo` (
  `GrupoID` int NOT NULL,
  `PlatoID` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Tipo` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'multiple',
  `Obligatorio` tinyint NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modificador_grupo`
--

INSERT INTO `modificador_grupo` (`GrupoID`, `PlatoID`, `Nombre`, `Tipo`, `Obligatorio`) VALUES
(1, 1, 'Ingredientes a retirar', 'multiple', 0),
(2, 1, 'Extras', 'multiple', 0),
(3, 3, 'Proteína del Wantán', 'unico', 1),
(4, 4, 'Término de cocción', 'unico', 1),
(5, 5, 'Salsa', 'unico', 0);

-- --------------------------------------------------------

--
-- Table structure for table `modificador_opcion`
--

DROP TABLE IF EXISTS `modificador_opcion`;
CREATE TABLE `modificador_opcion` (
  `OpcionID` int NOT NULL,
  `GrupoID` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Precio_Extra` decimal(8,2) NOT NULL DEFAULT '0.00',
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `modificador_opcion`
--

INSERT INTO `modificador_opcion` (`OpcionID`, `GrupoID`, `Nombre`, `Precio_Extra`, `Estado`) VALUES
(1, 1, 'Sin cebolla china', '0.00', 1),
(2, 1, 'Sin huevo', '0.00', 1),
(3, 1, 'Sin sillao', '0.00', 1),
(4, 2, 'Extra salsa sillao', '2.00', 1),
(5, 2, 'Extra pollo', '4.00', 1),
(6, 3, 'Cerdo', '0.00', 1),
(7, 3, 'Pollo', '0.00', 1),
(8, 3, 'Mixto', '2.00', 1),
(9, 4, 'Término medio', '0.00', 1),
(10, 4, 'Bien cocido', '0.00', 1),
(11, 5, 'Agridulce', '0.00', 1),
(12, 5, 'Tamarindo', '0.00', 1),
(13, 5, 'Sin salsa', '0.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `pedido`
--

DROP TABLE IF EXISTS `pedido`;
CREATE TABLE `pedido` (
  `PedidoID` int NOT NULL,
  `EmpleadoID` int NOT NULL,
  `MesaID` int DEFAULT NULL,
  `ClienteID` int DEFAULT NULL,
  `Fecha_Hora` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Origen` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Local',
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `Estado_Pago` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `Tipo_Pedido` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Observaciones` text COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Origen+Estado_Pago permiten diferenciar canal web vs local';

--
-- Dumping data for table `pedido`
--

INSERT INTO `pedido` (`PedidoID`, `EmpleadoID`, `MesaID`, `ClienteID`, `Fecha_Hora`, `Origen`, `Estado`, `Estado_Pago`, `Tipo_Pedido`, `Observaciones`) VALUES
(1, 3, 1, 1, '2026-05-19 16:32:09', 'Local', 'Entregado', 'Pagado_Local', 'Mesa', NULL),
(2, 3, 2, 2, '2026-05-19 16:32:09', 'Local', 'En cocina', 'Pendiente', 'Mesa', NULL),
(3, 2, NULL, 2, '2026-05-19 16:32:09', 'Web', 'Pendiente', 'Pagado_Online', 'Delivery', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `plato`
--

DROP TABLE IF EXISTS `plato`;
CREATE TABLE `plato` (
  `PlatoID` int NOT NULL,
  `Nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Descripcion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Imagen_URL` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT 'assets/img/platos/default.png',
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Disponible',
  `Orden` smallint NOT NULL DEFAULT '0',
  `Es_Top` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Marca platos destacados en sección Top Ventas (1=Sí, 0=No)',
  `Es_Promo` tinyint(1) NOT NULL DEFAULT '0' COMMENT 'Marca platos en promoción (1=Sí, 0=No)',
  `CatID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Carta del restaurante. Imagen_URL para e-commerce y carta digital';

--
-- Dumping data for table `plato`
--

INSERT INTO `plato` (`PlatoID`, `Nombre`, `Descripcion`, `Imagen_URL`, `Estado`, `Orden`, `Es_Top`, `Es_Promo`, `CatID`) VALUES
(1, 'Arroz Chaufa', 'Arroz salteado con pollo, huevo, cebolla china y silla', 'assets/img/platos/plato_1781145864_67a2561a.jpg', 'Disponible', 3, 1, 1, 3),
(2, 'Arroz Chaufa espe.', 'Arroz salteado con pollo, cerdo, camarones y verduras varia', 'assets/img/platos/plato_1781145815_89e2620a.png', 'Disponible', 2, 1, 0, 1),
(3, 'Sopa Wantán', 'Caldo con wantanes de cerdo y verduras de temporada', 'assets/img/platos/sopa_wantan.jpg', 'Disponible', 3, 1, 0, 2),
(4, 'Lomo Saltado Chifa', 'Lomo de res salteado con pimientos, tomate y sillao', 'assets/img/platos/lomo_saltado.jpg', 'Disponible', 4, 0, 1, 3),
(5, 'Pollo Tipakay', 'Pollo frito crujiente bañado en salsa agridulce de la casa', 'assets/img/platos/tipakay.jpg', 'Oculto', 5, 0, 1, 3),
(6, 'Inca Kola 500ml', 'Bebida gaseosa personal', 'assets/img/platos/incakola.jpg', 'Disponible', 6, 0, 0, 4);

-- --------------------------------------------------------

--
-- Table structure for table `plato_seccion`
--

DROP TABLE IF EXISTS `plato_seccion`;
CREATE TABLE `plato_seccion` (
  `PlatoSeccionID` int NOT NULL,
  `SeccionID` int NOT NULL,
  `PlatoID` int NOT NULL,
  `Orden` int NOT NULL DEFAULT '0' COMMENT 'Orden del plato dentro de la sección',
  `Fecha_Agregado` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Platos asignados manualmente a secciones';

-- --------------------------------------------------------

--
-- Table structure for table `plato_variante`
--

DROP TABLE IF EXISTS `plato_variante`;
CREATE TABLE `plato_variante` (
  `VarianteID` int NOT NULL,
  `PlatoID` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Precio_Venta` decimal(8,2) NOT NULL,
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `plato_variante`
--

INSERT INTO `plato_variante` (`VarianteID`, `PlatoID`, `Nombre`, `Precio_Venta`, `Estado`) VALUES
(1, 1, 'Personal', '18.00', 1),
(2, 1, 'Para dos', '32.00', 1),
(3, 2, 'Personal', '26.00', 1),
(4, 2, 'Para dos', '44.00', 1),
(5, 3, 'Regular', '16.00', 1),
(6, 3, 'Grande', '22.00', 1),
(7, 4, 'Porción regular', '28.00', 1),
(8, 5, 'Porción regular', '22.00', 1),
(9, 6, 'Unidad', '4.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `proveedor`
--

DROP TABLE IF EXISTS `proveedor`;
CREATE TABLE `proveedor` (
  `ProveedorID` int NOT NULL,
  `Razon_Social` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Ruc` char(11) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Contacto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Direccion` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Tipo_Producto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Correo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado` char(1) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'A'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `proveedor`
--

INSERT INTO `proveedor` (`ProveedorID`, `Razon_Social`, `Ruc`, `Contacto`, `Telefono`, `Direccion`, `Tipo_Producto`, `Correo`, `Estado`) VALUES
(1, 'Distribuidora Wong SAC', '20411234567', 'Sr. Wong', '072-123456', 'Av. Comercio 100, Tumbes', 'Abarrotes y salsas', 'ventas@wongdist.com', 'A'),
(2, 'Avícola Los Andes EIRL', '20522345675', 'Sra. Quispe', '072-234567', 'Mercado Central, Tumbes', 'Carnes y aves', 'alosandes@mail.com', 'A'),
(3, 'Mariscos del Norte SAC', '20633456789', 'Sr. Torres', '072-345678', 'Puerto Pesquero, Tumbes', 'Mariscos frescos', 'mnorte@mail.com', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `receta`
--

DROP TABLE IF EXISTS `receta`;
CREATE TABLE `receta` (
  `RecetaID` int NOT NULL,
  `VarianteID` int NOT NULL,
  `InsumoID` int NOT NULL,
  `Cantidad` decimal(10,4) NOT NULL,
  `Unidad_Medida` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Fórmula de producción: variante → insumos necesarios y cantidades';

--
-- Dumping data for table `receta`
--

INSERT INTO `receta` (`RecetaID`, `VarianteID`, `InsumoID`, `Cantidad`, `Unidad_Medida`) VALUES
(1, 1, 1, '0.2500', 'kg'),
(2, 1, 2, '0.1500', 'kg'),
(3, 1, 5, '1.0000', 'unidad'),
(4, 1, 4, '0.0300', 'kg'),
(5, 1, 6, '0.0200', 'lt'),
(6, 1, 7, '0.0300', 'lt'),
(7, 2, 1, '0.5000', 'kg'),
(8, 2, 2, '0.3000', 'kg'),
(9, 2, 5, '2.0000', 'unidad'),
(10, 2, 4, '0.0600', 'kg'),
(11, 2, 6, '0.0400', 'lt'),
(12, 2, 7, '0.0600', 'lt');

-- --------------------------------------------------------

--
-- Table structure for table `repartidor`
--

DROP TABLE IF EXISTS `repartidor`;
CREATE TABLE `repartidor` (
  `RepartidorID` int NOT NULL,
  `EmpleadoID` int DEFAULT NULL,
  `Nombre` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Vehiculo` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `repartidor`
--

INSERT INTO `repartidor` (`RepartidorID`, `EmpleadoID`, `Nombre`, `Telefono`, `Vehiculo`, `Estado`) VALUES
(1, NULL, 'Miguel Sosa', '998877665', 'Moto - MX-1234', 'Disponible'),
(2, NULL, 'Carlos Rivas', '997766554', 'Bicicleta', 'Disponible');

-- --------------------------------------------------------

--
-- Table structure for table `reserva`
--

DROP TABLE IF EXISTS `reserva`;
CREATE TABLE `reserva` (
  `ReservaID` int NOT NULL,
  `Token_Cancelacion` char(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  `ClienteID` int DEFAULT NULL,
  `Nombre_Contacto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono_Contacto` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Correo_Contacto` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Reserva` date NOT NULL,
  `Hora_Reserva` time NOT NULL,
  `Num_Comensales` tinyint NOT NULL,
  `MesaID` int DEFAULT NULL,
  `Estado` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Pendiente',
  `Observaciones` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nota_Admin` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `PedidoID` int DEFAULT NULL,
  `EmpleadoID` int DEFAULT NULL,
  `Fecha_Creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Fecha_Modificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla central del módulo de reservas.\r\n         Token_Cancelacion: PHP genera bin2hex(random_bytes(16)).\r\n         Nombre/Telefono/Correo_Contacto se guardan siempre para\r\n         no perder datos si el cliente elimina su cuenta.\r\n         PedidoID se vincula desde POS al confirmar llegada.';

--
-- Dumping data for table `reserva`
--

INSERT INTO `reserva` (`ReservaID`, `Token_Cancelacion`, `ClienteID`, `Nombre_Contacto`, `Telefono_Contacto`, `Correo_Contacto`, `Fecha_Reserva`, `Hora_Reserva`, `Num_Comensales`, `MesaID`, `Estado`, `Observaciones`, `Nota_Admin`, `PedidoID`, `EmpleadoID`, `Fecha_Creacion`, `Fecha_Modificacion`) VALUES
(1, 'd40a83cd08040909dc0cbaa0ebc2dc17', 12, 'Test Usuario', '999999999', 'test@example.com', '2026-06-06', '19:00:00', 4, NULL, 'Pendiente', 'Reserva de prueba desde test.html', NULL, NULL, NULL, '2026-06-05 00:56:52', '2026-06-05 00:56:52'),
(2, '96f5c39ac5d1cfe36278668b4033dace', 13, 'andremestanza', '9857415269', 'andre@wo.com', '2026-06-06', '14:00:00', 4, NULL, 'Pendiente', 'slecciona platillo', NULL, NULL, NULL, '2026-06-05 00:59:55', '2026-06-05 00:59:55'),
(3, 'f7198c78ff93f27302d4c9a79ebe529f', 11, 'caloles vinces', '937660992', 'prueba@gmail.com', '2026-06-06', '14:00:00', 2, NULL, 'Pendiente', 'ddd', NULL, NULL, NULL, '2026-06-05 01:14:10', '2026-06-05 01:14:10'),
(4, '79c7ad394b13789926f634a604982404', 14, 'CARLO MESTANZA', '933965596', 'SUPUBASE@GMAIL.COM', '2026-06-13', '14:00:00', 6, NULL, 'Pendiente', 'CALOLES', NULL, NULL, NULL, '2026-06-05 01:31:35', '2026-06-05 01:31:35'),
(5, '5b62f85da31afdc2df0d7e9adc1b91fa', 15, 'CALOES', '5874126933', 'CALOLE@DAWD.COM', '2026-06-06', '21:05:00', 6, NULL, 'Pendiente', 'DEDD', NULL, NULL, NULL, '2026-06-05 01:37:04', '2026-06-05 01:37:04'),
(6, '73b00ffc9d65a673f003692660366059', 16, 'DWDWD', '7412583', 'DADWDCDD@KADW.COM', '2026-06-06', '14:00:00', 3, NULL, 'Pendiente', '', NULL, NULL, NULL, '2026-06-05 01:38:44', '2026-06-05 01:38:44'),
(7, '2575503f2282ddb7d6b78f1313f4ee5f', 17, 'ADWDWD', '74125863', 'CW@HMD.COM', '2026-06-13', '15:00:00', 2, NULL, 'Pendiente', '', NULL, NULL, NULL, '2026-06-05 01:39:43', '2026-06-05 01:39:43'),
(8, 'b6ec896576716ed23039b3f2298df00e', 11, 'caloles vinces', '937660992', 'prueba@gmail.com', '2026-06-06', '14:00:00', 2, NULL, 'Pendiente', '', NULL, NULL, NULL, '2026-06-05 01:40:20', '2026-06-05 01:40:20'),
(9, 'de300387692d3487196cefdc10866816', 11, 'caloles vinces', '937660992', 'prueba@gmail.com', '2026-06-06', '15:00:00', 3, NULL, 'Pendiente', '', NULL, NULL, NULL, '2026-06-05 01:49:56', '2026-06-05 01:49:56'),
(10, 'aba801f7996af22b2aab7b9cc0216aee', 11, 'caloles vinces', '937660992', 'prueba@gmail.com', '2026-06-06', '15:00:00', 6, NULL, 'Pendiente', 'SEDECE', NULL, NULL, NULL, '2026-06-05 16:11:34', '2026-06-05 16:11:34'),
(11, 'c9d29e02e0b499c2e52f7ff27a053743', 11, 'caloles vinces', '937660992', 'prueba@gmail.com', '2026-06-06', '17:13:00', 7, NULL, 'Pendiente', 'SSWDED', NULL, NULL, NULL, '2026-06-05 16:14:06', '2026-06-05 16:14:06'),
(12, '8e128cc3c9eba36d96d92e8976f202c8', 18, 'andremestanza', '741258963', 'gg@gmail.com', '2026-06-08', '15:20:00', 4, NULL, 'Pendiente', 'jbjjjg', NULL, NULL, NULL, '2026-06-05 16:21:10', '2026-06-05 16:21:10'),
(13, '1b74993ec9e120c165e31d0454ffa0a8', 19, 'sara Lisbeth', '928001411', 'sarasosa@gmail.com', '2026-06-06', '19:00:00', 6, NULL, 'Pendiente', '', NULL, NULL, NULL, '2026-06-05 16:34:12', '2026-06-05 16:34:12'),
(14, 'a68eaaa1e6093dc169cd79c2d38b41fa', 11, 'PRUEBA RESERVAS', '937660992', 'prueba@gmail.com', '2026-06-13', '21:59:00', 5, NULL, 'Pendiente', 'necesito reservar el 2do piso', NULL, NULL, NULL, '2026-06-10 17:59:40', '2026-06-10 17:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `reserva_config`
--

DROP TABLE IF EXISTS `reserva_config`;
CREATE TABLE `reserva_config` (
  `ConfigID` int NOT NULL,
  `Anticipacion_Min_Hrs` tinyint NOT NULL DEFAULT '2',
  `Anticipacion_Max_Dias` tinyint NOT NULL DEFAULT '30',
  `Tolerancia_Min` tinyint NOT NULL DEFAULT '15',
  `Duracion_Estimada_Min` smallint NOT NULL DEFAULT '90',
  `Max_Comensales` tinyint NOT NULL DEFAULT '20',
  `Requiere_Confirmacion` tinyint NOT NULL DEFAULT '1',
  `Mensaje_Confirmacion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Mensaje_Cancelacion` varchar(500) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado` tinyint NOT NULL DEFAULT '1',
  `Fecha_Modificacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Reglas globales del sistema de reservas. Una fila activa (Estado=1).\r\n         PHP lee esto una vez por sesión igual que Config_Fiscal.';

--
-- Dumping data for table `reserva_config`
--

INSERT INTO `reserva_config` (`ConfigID`, `Anticipacion_Min_Hrs`, `Anticipacion_Max_Dias`, `Tolerancia_Min`, `Duracion_Estimada_Min`, `Max_Comensales`, `Requiere_Confirmacion`, `Mensaje_Confirmacion`, `Mensaje_Cancelacion`, `Estado`, `Fecha_Modificacion`) VALUES
(1, 2, 30, 15, 90, 20, 1, 'Tu reserva en Chifa Dragón de Oro está confirmada. Te esperamos el {fecha} a las {hora}. ¡Que disfrutes!', 'Tu reserva ha sido cancelada. Si tienes dudas llámanos al 072-123456.', 1, '2026-06-05 00:37:25');

-- --------------------------------------------------------

--
-- Table structure for table `reserva_historial`
--

DROP TABLE IF EXISTS `reserva_historial`;
CREATE TABLE `reserva_historial` (
  `HistorialID` int NOT NULL,
  `ReservaID` int NOT NULL,
  `Estado_Antes` varchar(25) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado_Nuevo` varchar(25) COLLATE utf8mb4_unicode_ci NOT NULL,
  `EmpleadoID` int DEFAULT NULL,
  `Origen` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'admin',
  `Observacion` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Cambio` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Auditoría de cada cambio de estado de una reserva.\r\n         Origen=cliente_web cuando cancela por token sin login.\r\n         Origen=sistema para cancelaciones automáticas por no-show.';

--
-- Dumping data for table `reserva_historial`
--

INSERT INTO `reserva_historial` (`HistorialID`, `ReservaID`, `Estado_Antes`, `Estado_Nuevo`, `EmpleadoID`, `Origen`, `Observacion`, `Fecha_Cambio`) VALUES
(1, 1, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 00:56:52'),
(2, 2, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 00:59:55'),
(3, 3, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:14:10'),
(4, 4, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:31:35'),
(5, 5, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:37:04'),
(6, 6, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:38:44'),
(7, 7, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:39:43'),
(8, 8, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:40:20'),
(9, 9, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 01:49:56'),
(10, 10, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 16:11:34'),
(11, 11, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 16:14:06'),
(12, 12, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 16:21:10'),
(13, 13, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-05 16:34:12'),
(14, 14, NULL, 'Pendiente', NULL, 'cliente_web', 'Reserva creada desde web', '2026-06-10 17:59:40');

-- --------------------------------------------------------

--
-- Table structure for table `seccion_web`
--

DROP TABLE IF EXISTS `seccion_web`;
CREATE TABLE `seccion_web` (
  `SeccionID` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Nombre interno de la sección',
  `Titulo` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Título visible en la web',
  `Subtitulo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Subtítulo descriptivo',
  `Icono` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Clase FontAwesome (ej: fas fa-star)',
  `Slug` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'Identificador URL-friendly (ej: top-ventas)',
  `Orden` int NOT NULL DEFAULT '0' COMMENT 'Orden de aparición en la web',
  `Tipo_Filtro` enum('campo','categoria','tag','manual') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'campo' COMMENT 'Tipo de filtro: campo BD, categoría, etiqueta o selección manual',
  `Campo_Filtro` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL COMMENT 'Si tipo=campo: nombre del campo (ej: Es_Top)',
  `Categoria_Filtro` int DEFAULT NULL COMMENT 'Si tipo=categoria: ID de la categoría',
  `Estilo_Seccion` enum('carousel','grid','list') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'carousel' COMMENT 'Tipo de visualización en frontend',
  `Limite_Platos` int DEFAULT '8' COMMENT 'Cantidad máxima de platos a mostrar',
  `Estado` tinyint(1) NOT NULL DEFAULT '1' COMMENT '1=Visible en web, 0=Oculto',
  `Fecha_Creacion` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `Fecha_Modificacion` datetime DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Gestión dinámica de secciones del menú web';

--
-- Dumping data for table `seccion_web`
--

INSERT INTO `seccion_web` (`SeccionID`, `Nombre`, `Titulo`, `Subtitulo`, `Icono`, `Slug`, `Orden`, `Tipo_Filtro`, `Campo_Filtro`, `Categoria_Filtro`, `Estilo_Seccion`, `Limite_Platos`, `Estado`, `Fecha_Creacion`, `Fecha_Modificacion`) VALUES
(1, 'Top Ventas', 'Top Ventas', 'Los favoritos de nuestros clientes', 'fas fa-star', 'top-ventas', 1, 'campo', 'Es_Top', NULL, 'carousel', 8, 1, '2026-06-10 20:36:42', NULL),
(2, 'Promociones', 'Promociones', 'Ofertas especiales que no puedes perderte', 'fas fa-tags', 'promociones', 2, 'campo', 'Es_Promo', NULL, 'carousel', 8, 1, '2026-06-10 20:36:42', NULL),
(3, 'Menú Completo', 'Menú del Chifa', 'Descubre lo mejor del chifa Matsue: sopas, chaufas, tallarines y más', 'fas fa-utensils', 'menu-completo', 3, 'campo', NULL, NULL, 'grid', 50, 1, '2026-06-10 20:36:42', NULL),
(4, 'Sopas Especiales', 'Nuestras Sopas', 'Caldos y sopas caseras tradicionales', 'fas fa-soup', 'sopas', 4, 'categoria', NULL, NULL, 'carousel', 6, 0, '2026-06-10 20:36:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tipo_rol`
--

DROP TABLE IF EXISTS `tipo_rol`;
CREATE TABLE `tipo_rol` (
  `RolID` int NOT NULL,
  `Nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Descripcion` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `tipo_rol`
--

INSERT INTO `tipo_rol` (`RolID`, `Nombre`, `Descripcion`) VALUES
(1, 'Administrador', 'Acceso total al sistema'),
(2, 'Cajero', 'Gestión de caja, pedidos y comprobantes'),
(3, 'Mozo', 'Toma de pedidos en sala'),
(4, 'Cocinero', 'Preparación de platos en cocina'),
(5, 'Almacenero', 'Control de inventario e insumos'),
(6, 'TESTER', 'UN CHUMAS');

-- --------------------------------------------------------

--
-- Table structure for table `tipo_usuario`
--

DROP TABLE IF EXISTS `tipo_usuario`;
CREATE TABLE `tipo_usuario` (
  `TipUsuID` int NOT NULL,
  `Descripcion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Scope` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'backoffice'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Roles de acceso al sistema';

--
-- Dumping data for table `tipo_usuario`
--

INSERT INTO `tipo_usuario` (`TipUsuID`, `Descripcion`, `Scope`) VALUES
(1, 'Administrador del sistema', 'backoffice'),
(2, 'Cajero / Vendedor', 'backoffice'),
(3, 'Mozo', 'backoffice'),
(4, 'Almacenero', 'backoffice'),
(5, 'Cliente web', 'web');

-- --------------------------------------------------------

--
-- Table structure for table `turno`
--

DROP TABLE IF EXISTS `turno`;
CREATE TABLE `turno` (
  `TurnoID` int NOT NULL,
  `Descripcion` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Hora_Inicio` time NOT NULL,
  `Hora_Fin` time NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `turno`
--

INSERT INTO `turno` (`TurnoID`, `Descripcion`, `Hora_Inicio`, `Hora_Fin`) VALUES
(1, 'Mañana', '08:00:00', '14:00:00'),
(2, 'Tarde', '14:00:00', '20:00:00'),
(3, 'Noche', '20:00:00', '23:59:00'),
(4, 'Completo', '08:00:00', '20:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE `usuario` (
  `UsuarioID` int NOT NULL,
  `Login` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Activo',
  `Token_Reset` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Fecha_Token` datetime DEFAULT NULL,
  `TipUsuID` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Tabla central de autenticación para empleados y clientes web';

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`UsuarioID`, `Login`, `Contrasena`, `Estado`, `Token_Reset`, `Fecha_Token`, `TipUsuID`) VALUES
(1, 'admin', '$2y$10$hashAdmin000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 1),
(2, 'cajera.maria', '$2y$10$hashMaria000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 2),
(3, 'mozo.jose', '$2y$10$hashJose0000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 3),
(4, 'mozo.ana', '$2y$10$hashAna00000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 3),
(5, 'almacen.rosa', '$2y$10$hashRosa0000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 4),
(6, 'pedro.salas', '$2y$10$hashPedro000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 5),
(7, 'lucia.ramos', '$2y$10$hashLucia000000000000000000000000000000000000000000000', 'Activo', NULL, NULL, 5),
(8, 'andre@wong.com', '$2y$10$YlfJWRssr9q0jhCE8bMZCuEh4vYxiIu3tbvJCmbHlQBOymY.4KaIm', 'Activo', NULL, NULL, 5),
(9, 'LUIS@matsue.com', '$2y$10$I6vEJYYl0ZArK8zZTgPhdeI90iFUJ5BZFpkdUhCVuoUcPSikcwNX.', 'Activo', NULL, NULL, 5),
(10, 'JANINA@GMAIL.COM', '$2y$10$2KQlruLd5z9VcIh.CPCF4OuJa2y1OR12ALlowcjLwYTQ2VcXBkNfO', 'Activo', NULL, NULL, 5),
(14, 'admin@matsue.local', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Activo', NULL, NULL, 1),
(16, 'test@admin.com', '$2y$10$OaCEP5NihY6tSuPeW0B2heXZKA6SQeggtSJXMyUZbRGkGMqIGv5T6', 'Activo', NULL, NULL, 1),
(19, 'admin@test.com', '$2y$10$7q1Pxl21KFo.PrmUdkw4oOHSp.EKuFGWm/bG0/FTFzqPcWovn6Gd.', 'Activo', NULL, NULL, 1),
(20, 'lleyker@admin.com', '$2y$10$3xa6cHXaqjvzI0C9oHF9q.XFyl2RR41YJnX69IxOKDkSeEOxYbPGC', 'Activo', NULL, NULL, 4),
(21, 'pierito@jujus.com', '$2y$10$7NxP3SujceWwWZ.B4KbsPO1jwA5yo8RSqD6ioJQ7YYxAuoUbd68YK', 'Inactivo', NULL, NULL, 3),
(22, 'ramos@gmail.com', '$2y$10$87/cbK/YoXQNShAjwK7o5.TxF.NGYE4stDYSjAxrXjn6BsWnGRh6C', 'Inactivo', NULL, NULL, 2),
(23, 'lleyker@cliente.com', '$2y$10$B02PbJeH5AW7RQXbOBYLfu0NpqBxCM9VI.YLNh3jJgfU3kj8.Oe72', 'Activo', NULL, NULL, 5),
(24, 'pepito@gmail.com', '$2y$10$6TPQOgMRsDrN17TB8JsEX.vY0EL5u1i8wh6DSkXJm4V44p6GubyZm', 'Inactivo', NULL, NULL, 5),
(25, 'sara@gmail.com', '$2y$10$B/WNwFLmHjRwBwTIq8TlCO.bCAkRFKBw8X1ebgGNqSCt0WQclQXcy', 'Activo', NULL, NULL, 3),
(26, 'sarita@gmail.com', '$2y$10$N47vgjlHDpEZdBi1t.4FMefJ/mAfWplgWAamVOKSvJ5BZ10ZYgZHe', 'Activo', NULL, NULL, 5),
(27, 'prueba@gmail.com', '$2y$10$1SFfn4gyGZwMaSKfMWsnBuja513KjKozDBiKifvp3yV4jBznGoqTq', 'Activo', NULL, NULL, 5),
(28, 'sarasosa@gmail.com', '$2y$10$O8UK7K7rCovr2L4O6mNM8e8OcPV89MDsm.B2EuVpmxTVfFS7Lj/se', 'Activo', NULL, NULL, 5);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_carta_web`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_carta_web`;
CREATE TABLE `v_carta_web` (
`Categoria` varchar(150)
,`Descripcion` varchar(500)
,`Imagen_URL` varchar(255)
,`Orden` smallint
,`Plato` varchar(150)
,`PlatoID` int
,`Precio_Venta` decimal(8,2)
,`Variante` varchar(100)
,`VarianteID` int
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_detalle_pedido_completo`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_detalle_pedido_completo`;
CREATE TABLE `v_detalle_pedido_completo` (
`Cantidad` smallint
,`DetPedID` int
,`Modificadores` text
,`Notas` varchar(200)
,`PedidoID` int
,`Plato` varchar(150)
,`Precio_Unitario` decimal(8,2)
,`Subtotal` decimal(10,2)
,`Variante` varchar(100)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_kardex_trazable`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_kardex_trazable`;
CREATE TABLE `v_kardex_trazable` (
`Cantidad` decimal(10,2)
,`Fecha` datetime
,`Insumo` varchar(150)
,`KardexID` int
,`Lote` varchar(50)
,`Motivo` varchar(200)
,`Precio_Unitario` decimal(8,2)
,`Proveedor` varchar(200)
,`Responsable` varchar(100)
,`Tipo_Movimiento` varchar(20)
,`Unidad_Medida` varchar(20)
,`Valor_Total` decimal(18,4)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_pedidos_activos`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_pedidos_activos`;
CREATE TABLE `v_pedidos_activos` (
`Cliente` varchar(150)
,`Codigo_Mesa` varchar(10)
,`Empleado` varchar(100)
,`Estado` varchar(20)
,`Estado_Pago` varchar(30)
,`Fecha_Hora` datetime
,`Origen` varchar(20)
,`PedidoID` int
,`Tipo_Pedido` varchar(20)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_platos_por_seccion`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_platos_por_seccion`;
CREATE TABLE `v_platos_por_seccion` (
`Descripcion` varchar(500)
,`Imagen_URL` varchar(255)
,`Orden_En_Seccion` int
,`Plato_Nombre` varchar(150)
,`Plato_Orden` smallint
,`PlatoID` int
,`Precio_Min` decimal(8,2)
,`Seccion_Nombre` varchar(100)
,`SeccionID` int
,`Slug` varchar(50)
,`Tipo_Filtro` enum('campo','categoria','tag','manual')
,`Total_Variantes` bigint
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_stock_critico`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_stock_critico`;
CREATE TABLE `v_stock_critico` (
`Categoria` varchar(150)
,`InsumoID` int
,`Nombre` varchar(150)
,`Stock_Actual` decimal(10,2)
,`Stock_Minimo` decimal(10,2)
,`Unidad_Medida` varchar(20)
,`Unidades_Faltantes` decimal(11,2)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_ventas_dia`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `v_ventas_dia`;
CREATE TABLE `v_ventas_dia` (
`Metodo_Pago` varchar(50)
,`Monto_Total` decimal(32,2)
,`Total_Comprobantes` bigint
);

-- --------------------------------------------------------

--
-- Table structure for table `zona_cobertura`
--

DROP TABLE IF EXISTS `zona_cobertura`;
CREATE TABLE `zona_cobertura` (
  `ZonaID` int NOT NULL,
  `Nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Precio_Delivery` decimal(8,2) NOT NULL DEFAULT '0.00',
  `Tiempo_Estimado_Min` int NOT NULL DEFAULT '30',
  `Estado` tinyint NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `zona_cobertura`
--

INSERT INTO `zona_cobertura` (`ZonaID`, `Nombre`, `Precio_Delivery`, `Tiempo_Estimado_Min`, `Estado`) VALUES
(1, 'Zona Centro', '3.00', 20, 1),
(2, 'Zona Norte', '5.00', 35, 1),
(3, 'Zona Sur', '5.00', 35, 1),
(4, 'Zona Este', '7.00', 45, 1),
(5, 'Fuera de cobertura', '0.00', 0, 0);

-- --------------------------------------------------------

--
-- Structure for view `v_carta_web`
--
DROP TABLE IF EXISTS `v_carta_web`;

DROP VIEW IF EXISTS `v_carta_web`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_carta_web`  AS SELECT `pl`.`PlatoID` AS `PlatoID`, `pl`.`Nombre` AS `Plato`, `pl`.`Descripcion` AS `Descripcion`, `pl`.`Imagen_URL` AS `Imagen_URL`, `pl`.`Orden` AS `Orden`, `cat`.`Nombre` AS `Categoria`, `pv`.`VarianteID` AS `VarianteID`, `pv`.`Nombre` AS `Variante`, `pv`.`Precio_Venta` AS `Precio_Venta` FROM ((`plato` `pl` join `categoria` `cat` on((`pl`.`CatID` = `cat`.`CatID`))) join `plato_variante` `pv` on((`pl`.`PlatoID` = `pv`.`PlatoID`))) WHERE ((`pl`.`Estado` = 'Disponible') AND (`pv`.`Estado` = 1) AND (`cat`.`Tipo` = 'Plato')) ORDER BY `cat`.`Nombre` ASC, `pl`.`Orden` ASC, `pv`.`Precio_Venta` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `v_detalle_pedido_completo`
--
DROP TABLE IF EXISTS `v_detalle_pedido_completo`;

DROP VIEW IF EXISTS `v_detalle_pedido_completo`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_detalle_pedido_completo`  AS SELECT `dp`.`DetPedID` AS `DetPedID`, `dp`.`PedidoID` AS `PedidoID`, `pl`.`Nombre` AS `Plato`, `pv`.`Nombre` AS `Variante`, `dp`.`Cantidad` AS `Cantidad`, `dp`.`Precio_Unitario` AS `Precio_Unitario`, `dp`.`Subtotal` AS `Subtotal`, `dp`.`Notas` AS `Notas`, group_concat(`mo`.`Nombre` order by `mo`.`Nombre` ASC separator ', ') AS `Modificadores` FROM ((((`detalle_pedido` `dp` join `plato_variante` `pv` on((`dp`.`VarianteID` = `pv`.`VarianteID`))) join `plato` `pl` on((`pv`.`PlatoID` = `pl`.`PlatoID`))) left join `detalle_modificador` `dm` on((`dp`.`DetPedID` = `dm`.`DetPedID`))) left join `modificador_opcion` `mo` on((`dm`.`OpcionID` = `mo`.`OpcionID`))) GROUP BY `dp`.`DetPedID`, `dp`.`PedidoID`, `pl`.`Nombre`, `pv`.`Nombre`, `dp`.`Cantidad`, `dp`.`Precio_Unitario`, `dp`.`Subtotal`, `dp`.`Notas``Notas`  ;

-- --------------------------------------------------------

--
-- Structure for view `v_kardex_trazable`
--
DROP TABLE IF EXISTS `v_kardex_trazable`;

DROP VIEW IF EXISTS `v_kardex_trazable`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_kardex_trazable`  AS SELECT `k`.`KardexID` AS `KardexID`, `k`.`Fecha` AS `Fecha`, `k`.`Tipo_Movimiento` AS `Tipo_Movimiento`, `i`.`Nombre` AS `Insumo`, `k`.`Cantidad` AS `Cantidad`, `i`.`Unidad_Medida` AS `Unidad_Medida`, `k`.`Precio_Unitario` AS `Precio_Unitario`, (`k`.`Cantidad` * `k`.`Precio_Unitario`) AS `Valor_Total`, `k`.`Motivo` AS `Motivo`, `k`.`Lote` AS `Lote`, coalesce(`p`.`Razon_Social`,'—') AS `Proveedor`, coalesce(`e`.`Nombre_Apellidos`,'—') AS `Responsable` FROM (((`kardex` `k` join `insumo` `i` on((`k`.`InsumoID` = `i`.`InsumoID`))) left join `proveedor` `p` on((`k`.`ProveedorID` = `p`.`ProveedorID`))) left join `empleado` `e` on((`k`.`EmpleadoID` = `e`.`EmpleadoID`))) ORDER BY `k`.`Fecha` AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `v_pedidos_activos`
--
DROP TABLE IF EXISTS `v_pedidos_activos`;

DROP VIEW IF EXISTS `v_pedidos_activos`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_pedidos_activos`  AS SELECT `p`.`PedidoID` AS `PedidoID`, `p`.`Origen` AS `Origen`, `p`.`Tipo_Pedido` AS `Tipo_Pedido`, `p`.`Estado` AS `Estado`, `p`.`Estado_Pago` AS `Estado_Pago`, `p`.`Fecha_Hora` AS `Fecha_Hora`, `m`.`Codigo_Mesa` AS `Codigo_Mesa`, coalesce(`c`.`Nombre_Apellidos`,'Anónimo') AS `Cliente`, `e`.`Nombre_Apellidos` AS `Empleado` FROM (((`pedido` `p` left join `mesa` `m` on((`p`.`MesaID` = `m`.`MesaID`))) left join `cliente` `c` on((`p`.`ClienteID` = `c`.`ClienteID`))) join `empleado` `e` on((`p`.`EmpleadoID` = `e`.`EmpleadoID`))) WHERE (`p`.`Estado` not in ('Pagado','Cancelado'))  ;

-- --------------------------------------------------------

--
-- Structure for view `v_platos_por_seccion`
--
DROP TABLE IF EXISTS `v_platos_por_seccion`;

DROP VIEW IF EXISTS `v_platos_por_seccion`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_platos_por_seccion`  AS SELECT `sw`.`SeccionID` AS `SeccionID`, `sw`.`Slug` AS `Slug`, `sw`.`Nombre` AS `Seccion_Nombre`, `sw`.`Tipo_Filtro` AS `Tipo_Filtro`, `pl`.`PlatoID` AS `PlatoID`, `pl`.`Nombre` AS `Plato_Nombre`, `pl`.`Descripcion` AS `Descripcion`, `pl`.`Imagen_URL` AS `Imagen_URL`, `pl`.`Orden` AS `Plato_Orden`, `ps`.`Orden` AS `Orden_En_Seccion`, min(`pv`.`Precio_Venta`) AS `Precio_Min`, count(`pv`.`PlatoID`) AS `Total_Variantes` FROM (((`seccion_web` `sw` left join `plato_seccion` `ps` on((`sw`.`SeccionID` = `ps`.`SeccionID`))) left join `plato` `pl` on((((`sw`.`Tipo_Filtro` = 'manual') and (`ps`.`PlatoID` = `pl`.`PlatoID`)) or ((`sw`.`Tipo_Filtro` = 'categoria') and (`pl`.`CatID` = `sw`.`Categoria_Filtro`)) or (`sw`.`Tipo_Filtro` = 'campo')))) left join `plato_variante` `pv` on(((`pl`.`PlatoID` = `pv`.`PlatoID`) and (`pv`.`Estado` = 1)))) WHERE ((`sw`.`Estado` = 1) AND (`pl`.`Estado` = 'Disponible')) GROUP BY `sw`.`SeccionID`, `sw`.`Slug`, `sw`.`Nombre`, `sw`.`Tipo_Filtro`, `pl`.`PlatoID`, `pl`.`Nombre`, `pl`.`Descripcion`, `pl`.`Imagen_URL`, `pl`.`Orden`, `ps`.`Orden``Orden`  ;

-- --------------------------------------------------------

--
-- Structure for view `v_stock_critico`
--
DROP TABLE IF EXISTS `v_stock_critico`;

DROP VIEW IF EXISTS `v_stock_critico`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stock_critico`  AS SELECT `i`.`InsumoID` AS `InsumoID`, `i`.`Nombre` AS `Nombre`, `i`.`Stock_Actual` AS `Stock_Actual`, `i`.`Stock_Minimo` AS `Stock_Minimo`, `i`.`Unidad_Medida` AS `Unidad_Medida`, `c`.`Nombre` AS `Categoria`, (`i`.`Stock_Minimo` - `i`.`Stock_Actual`) AS `Unidades_Faltantes` FROM (`insumo` `i` join `categoria` `c` on((`i`.`CatID` = `c`.`CatID`))) WHERE ((`i`.`Stock_Actual` <= `i`.`Stock_Minimo`) AND (`i`.`Estado` = 'Activo')) ORDER BY (`i`.`Stock_Minimo` - `i`.`Stock_Actual`) AS `DESCdesc` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `v_ventas_dia`
--
DROP TABLE IF EXISTS `v_ventas_dia`;

DROP VIEW IF EXISTS `v_ventas_dia`;
CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_ventas_dia`  AS SELECT `mp`.`Nombre` AS `Metodo_Pago`, count(`cp`.`ComPagID`) AS `Total_Comprobantes`, sum(`cp`.`Total`) AS `Monto_Total` FROM (`comprobante_pago` `cp` join `metodo_pago` `mp` on((`cp`.`MetPagID` = `mp`.`MetPagID`))) WHERE (cast(`cp`.`Fecha_Emision` as date) = curdate()) GROUP BY `mp`.`MetPagID`, `mp`.`Nombre``Nombre`  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `asistencia`
--
ALTER TABLE `asistencia`
  ADD PRIMARY KEY (`AsistenciaID`),
  ADD KEY `fk_asis_emp` (`EmpleadoID`);

--
-- Indexes for table `caja`
--
ALTER TABLE `caja`
  ADD PRIMARY KEY (`CajaID`),
  ADD KEY `fk_caja_emp` (`EmpleadoID`);

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`CatID`);

--
-- Indexes for table `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`ClienteID`),
  ADD UNIQUE KEY `Correo` (`Correo`),
  ADD UNIQUE KEY `UsuarioID` (`UsuarioID`);

--
-- Indexes for table `comprobante_pago`
--
ALTER TABLE `comprobante_pago`
  ADD PRIMARY KEY (`ComPagID`),
  ADD KEY `fk_comp_ped` (`PedidoID`),
  ADD KEY `fk_comp_cli` (`ClienteID`),
  ADD KEY `fk_comp_metpag` (`MetPagID`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`DeliveryID`),
  ADD KEY `fk_del_ped` (`PedidoID`),
  ADD KEY `fk_del_rep` (`RepartidorID`),
  ADD KEY `fk_del_zona` (`ZonaID`);

--
-- Indexes for table `detalle_modificador`
--
ALTER TABLE `detalle_modificador`
  ADD PRIMARY KEY (`DetModID`),
  ADD KEY `fk_detmod_det` (`DetPedID`),
  ADD KEY `fk_detmod_op` (`OpcionID`);

--
-- Indexes for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`DetPedID`),
  ADD KEY `fk_detped_ped` (`PedidoID`),
  ADD KEY `fk_detped_var` (`VarianteID`);

--
-- Indexes for table `empleado`
--
ALTER TABLE `empleado`
  ADD PRIMARY KEY (`EmpleadoID`),
  ADD UNIQUE KEY `DNI` (`DNI`),
  ADD UNIQUE KEY `UsuarioID` (`UsuarioID`),
  ADD KEY `fk_emp_rol` (`RolID`),
  ADD KEY `fk_emp_turno` (`TurnoID`);

--
-- Indexes for table `insumo`
--
ALTER TABLE `insumo`
  ADD PRIMARY KEY (`InsumoID`),
  ADD KEY `fk_ins_cat` (`CatID`);

--
-- Indexes for table `integracion_externa`
--
ALTER TABLE `integracion_externa`
  ADD PRIMARY KEY (`IntExtID`),
  ADD KEY `fk_int_ped` (`PedidoID`);

--
-- Indexes for table `kardex`
--
ALTER TABLE `kardex`
  ADD PRIMARY KEY (`KardexID`),
  ADD KEY `fk_kar_ins` (`InsumoID`),
  ADD KEY `fk_kar_prov` (`ProveedorID`),
  ADD KEY `fk_kar_emp` (`EmpleadoID`);

--
-- Indexes for table `mesa`
--
ALTER TABLE `mesa`
  ADD PRIMARY KEY (`MesaID`),
  ADD UNIQUE KEY `Codigo_Mesa` (`Codigo_Mesa`);

--
-- Indexes for table `metodo_pago`
--
ALTER TABLE `metodo_pago`
  ADD PRIMARY KEY (`MetPagID`);

--
-- Indexes for table `modificador_grupo`
--
ALTER TABLE `modificador_grupo`
  ADD PRIMARY KEY (`GrupoID`),
  ADD KEY `fk_grupo_plato` (`PlatoID`);

--
-- Indexes for table `modificador_opcion`
--
ALTER TABLE `modificador_opcion`
  ADD PRIMARY KEY (`OpcionID`),
  ADD KEY `fk_opcion_grupo` (`GrupoID`);

--
-- Indexes for table `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`PedidoID`),
  ADD KEY `fk_ped_emp` (`EmpleadoID`),
  ADD KEY `fk_ped_mesa` (`MesaID`),
  ADD KEY `fk_ped_cli` (`ClienteID`);

--
-- Indexes for table `plato`
--
ALTER TABLE `plato`
  ADD PRIMARY KEY (`PlatoID`),
  ADD KEY `fk_plato_cat` (`CatID`),
  ADD KEY `idx_plato_top` (`Es_Top`,`Estado`,`Orden`),
  ADD KEY `idx_plato_promo` (`Es_Promo`,`Estado`,`Orden`),
  ADD KEY `idx_plato_web` (`Estado`,`Es_Top`,`Es_Promo`,`Orden`);

--
-- Indexes for table `plato_seccion`
--
ALTER TABLE `plato_seccion`
  ADD PRIMARY KEY (`PlatoSeccionID`),
  ADD UNIQUE KEY `unique_plato_seccion` (`SeccionID`,`PlatoID`),
  ADD KEY `PlatoID` (`PlatoID`),
  ADD KEY `idx_seccion_orden` (`SeccionID`,`Orden`);

--
-- Indexes for table `plato_variante`
--
ALTER TABLE `plato_variante`
  ADD PRIMARY KEY (`VarianteID`),
  ADD KEY `fk_var_plato` (`PlatoID`);

--
-- Indexes for table `proveedor`
--
ALTER TABLE `proveedor`
  ADD PRIMARY KEY (`ProveedorID`),
  ADD UNIQUE KEY `Ruc` (`Ruc`);

--
-- Indexes for table `receta`
--
ALTER TABLE `receta`
  ADD PRIMARY KEY (`RecetaID`),
  ADD UNIQUE KEY `uq_receta` (`VarianteID`,`InsumoID`),
  ADD KEY `fk_rec_ins` (`InsumoID`);

--
-- Indexes for table `repartidor`
--
ALTER TABLE `repartidor`
  ADD PRIMARY KEY (`RepartidorID`),
  ADD KEY `fk_rep_emp` (`EmpleadoID`);

--
-- Indexes for table `reserva`
--
ALTER TABLE `reserva`
  ADD PRIMARY KEY (`ReservaID`),
  ADD UNIQUE KEY `Token_Cancelacion` (`Token_Cancelacion`),
  ADD KEY `idx_reserva_fecha` (`Fecha_Reserva`,`Hora_Reserva`),
  ADD KEY `idx_reserva_estado` (`Estado`),
  ADD KEY `idx_reserva_cliente` (`ClienteID`),
  ADD KEY `fk_res_mesa` (`MesaID`),
  ADD KEY `fk_res_pedido` (`PedidoID`),
  ADD KEY `fk_res_empleado` (`EmpleadoID`);

--
-- Indexes for table `reserva_config`
--
ALTER TABLE `reserva_config`
  ADD PRIMARY KEY (`ConfigID`);

--
-- Indexes for table `reserva_historial`
--
ALTER TABLE `reserva_historial`
  ADD PRIMARY KEY (`HistorialID`),
  ADD KEY `idx_rh_reserva` (`ReservaID`),
  ADD KEY `fk_rh_empleado` (`EmpleadoID`);

--
-- Indexes for table `seccion_web`
--
ALTER TABLE `seccion_web`
  ADD PRIMARY KEY (`SeccionID`),
  ADD UNIQUE KEY `Slug` (`Slug`),
  ADD KEY `Categoria_Filtro` (`Categoria_Filtro`),
  ADD KEY `idx_seccion_activa` (`Estado`,`Orden`),
  ADD KEY `idx_seccion_slug` (`Slug`);

--
-- Indexes for table `tipo_rol`
--
ALTER TABLE `tipo_rol`
  ADD PRIMARY KEY (`RolID`);

--
-- Indexes for table `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  ADD PRIMARY KEY (`TipUsuID`);

--
-- Indexes for table `turno`
--
ALTER TABLE `turno`
  ADD PRIMARY KEY (`TurnoID`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`UsuarioID`),
  ADD UNIQUE KEY `Login` (`Login`),
  ADD KEY `fk_usu_tipo` (`TipUsuID`);

--
-- Indexes for table `zona_cobertura`
--
ALTER TABLE `zona_cobertura`
  ADD PRIMARY KEY (`ZonaID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `asistencia`
--
ALTER TABLE `asistencia`
  MODIFY `AsistenciaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `caja`
--
ALTER TABLE `caja`
  MODIFY `CajaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `CatID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `cliente`
--
ALTER TABLE `cliente`
  MODIFY `ClienteID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `comprobante_pago`
--
ALTER TABLE `comprobante_pago`
  MODIFY `ComPagID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `DeliveryID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detalle_modificador`
--
ALTER TABLE `detalle_modificador`
  MODIFY `DetModID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `DetPedID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `empleado`
--
ALTER TABLE `empleado`
  MODIFY `EmpleadoID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `insumo`
--
ALTER TABLE `insumo`
  MODIFY `InsumoID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `integracion_externa`
--
ALTER TABLE `integracion_externa`
  MODIFY `IntExtID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `kardex`
--
ALTER TABLE `kardex`
  MODIFY `KardexID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `mesa`
--
ALTER TABLE `mesa`
  MODIFY `MesaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `metodo_pago`
--
ALTER TABLE `metodo_pago`
  MODIFY `MetPagID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modificador_grupo`
--
ALTER TABLE `modificador_grupo`
  MODIFY `GrupoID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `modificador_opcion`
--
ALTER TABLE `modificador_opcion`
  MODIFY `OpcionID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `pedido`
--
ALTER TABLE `pedido`
  MODIFY `PedidoID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `plato`
--
ALTER TABLE `plato`
  MODIFY `PlatoID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `plato_seccion`
--
ALTER TABLE `plato_seccion`
  MODIFY `PlatoSeccionID` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `plato_variante`
--
ALTER TABLE `plato_variante`
  MODIFY `VarianteID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `proveedor`
--
ALTER TABLE `proveedor`
  MODIFY `ProveedorID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `receta`
--
ALTER TABLE `receta`
  MODIFY `RecetaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `repartidor`
--
ALTER TABLE `repartidor`
  MODIFY `RepartidorID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `reserva`
--
ALTER TABLE `reserva`
  MODIFY `ReservaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `reserva_config`
--
ALTER TABLE `reserva_config`
  MODIFY `ConfigID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reserva_historial`
--
ALTER TABLE `reserva_historial`
  MODIFY `HistorialID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `seccion_web`
--
ALTER TABLE `seccion_web`
  MODIFY `SeccionID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tipo_rol`
--
ALTER TABLE `tipo_rol`
  MODIFY `RolID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tipo_usuario`
--
ALTER TABLE `tipo_usuario`
  MODIFY `TipUsuID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `turno`
--
ALTER TABLE `turno`
  MODIFY `TurnoID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `UsuarioID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `zona_cobertura`
--
ALTER TABLE `zona_cobertura`
  MODIFY `ZonaID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `asistencia`
--
ALTER TABLE `asistencia`
  ADD CONSTRAINT `fk_asis_emp` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`);

--
-- Constraints for table `caja`
--
ALTER TABLE `caja`
  ADD CONSTRAINT `fk_caja_emp` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`);

--
-- Constraints for table `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cli_usu` FOREIGN KEY (`UsuarioID`) REFERENCES `usuario` (`UsuarioID`);

--
-- Constraints for table `comprobante_pago`
--
ALTER TABLE `comprobante_pago`
  ADD CONSTRAINT `fk_comp_cli` FOREIGN KEY (`ClienteID`) REFERENCES `cliente` (`ClienteID`),
  ADD CONSTRAINT `fk_comp_metpag` FOREIGN KEY (`MetPagID`) REFERENCES `metodo_pago` (`MetPagID`),
  ADD CONSTRAINT `fk_comp_ped` FOREIGN KEY (`PedidoID`) REFERENCES `pedido` (`PedidoID`);

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `fk_del_ped` FOREIGN KEY (`PedidoID`) REFERENCES `pedido` (`PedidoID`),
  ADD CONSTRAINT `fk_del_rep` FOREIGN KEY (`RepartidorID`) REFERENCES `repartidor` (`RepartidorID`),
  ADD CONSTRAINT `fk_del_zona` FOREIGN KEY (`ZonaID`) REFERENCES `zona_cobertura` (`ZonaID`);

--
-- Constraints for table `detalle_modificador`
--
ALTER TABLE `detalle_modificador`
  ADD CONSTRAINT `fk_detmod_det` FOREIGN KEY (`DetPedID`) REFERENCES `detalle_pedido` (`DetPedID`),
  ADD CONSTRAINT `fk_detmod_op` FOREIGN KEY (`OpcionID`) REFERENCES `modificador_opcion` (`OpcionID`);

--
-- Constraints for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_detped_ped` FOREIGN KEY (`PedidoID`) REFERENCES `pedido` (`PedidoID`),
  ADD CONSTRAINT `fk_detped_var` FOREIGN KEY (`VarianteID`) REFERENCES `plato_variante` (`VarianteID`);

--
-- Constraints for table `empleado`
--
ALTER TABLE `empleado`
  ADD CONSTRAINT `fk_emp_rol` FOREIGN KEY (`RolID`) REFERENCES `tipo_rol` (`RolID`),
  ADD CONSTRAINT `fk_emp_turno` FOREIGN KEY (`TurnoID`) REFERENCES `turno` (`TurnoID`),
  ADD CONSTRAINT `fk_emp_usu` FOREIGN KEY (`UsuarioID`) REFERENCES `usuario` (`UsuarioID`);

--
-- Constraints for table `insumo`
--
ALTER TABLE `insumo`
  ADD CONSTRAINT `fk_ins_cat` FOREIGN KEY (`CatID`) REFERENCES `categoria` (`CatID`);

--
-- Constraints for table `integracion_externa`
--
ALTER TABLE `integracion_externa`
  ADD CONSTRAINT `fk_int_ped` FOREIGN KEY (`PedidoID`) REFERENCES `pedido` (`PedidoID`);

--
-- Constraints for table `kardex`
--
ALTER TABLE `kardex`
  ADD CONSTRAINT `fk_kar_emp` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`),
  ADD CONSTRAINT `fk_kar_ins` FOREIGN KEY (`InsumoID`) REFERENCES `insumo` (`InsumoID`),
  ADD CONSTRAINT `fk_kar_prov` FOREIGN KEY (`ProveedorID`) REFERENCES `proveedor` (`ProveedorID`);

--
-- Constraints for table `modificador_grupo`
--
ALTER TABLE `modificador_grupo`
  ADD CONSTRAINT `fk_grupo_plato` FOREIGN KEY (`PlatoID`) REFERENCES `plato` (`PlatoID`);

--
-- Constraints for table `modificador_opcion`
--
ALTER TABLE `modificador_opcion`
  ADD CONSTRAINT `fk_opcion_grupo` FOREIGN KEY (`GrupoID`) REFERENCES `modificador_grupo` (`GrupoID`);

--
-- Constraints for table `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_ped_cli` FOREIGN KEY (`ClienteID`) REFERENCES `cliente` (`ClienteID`),
  ADD CONSTRAINT `fk_ped_emp` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`),
  ADD CONSTRAINT `fk_ped_mesa` FOREIGN KEY (`MesaID`) REFERENCES `mesa` (`MesaID`);

--
-- Constraints for table `plato`
--
ALTER TABLE `plato`
  ADD CONSTRAINT `fk_plato_cat` FOREIGN KEY (`CatID`) REFERENCES `categoria` (`CatID`);

--
-- Constraints for table `plato_seccion`
--
ALTER TABLE `plato_seccion`
  ADD CONSTRAINT `plato_seccion_ibfk_1` FOREIGN KEY (`SeccionID`) REFERENCES `seccion_web` (`SeccionID`) ON DELETE CASCADE,
  ADD CONSTRAINT `plato_seccion_ibfk_2` FOREIGN KEY (`PlatoID`) REFERENCES `plato` (`PlatoID`) ON DELETE CASCADE;

--
-- Constraints for table `plato_variante`
--
ALTER TABLE `plato_variante`
  ADD CONSTRAINT `fk_var_plato` FOREIGN KEY (`PlatoID`) REFERENCES `plato` (`PlatoID`);

--
-- Constraints for table `receta`
--
ALTER TABLE `receta`
  ADD CONSTRAINT `fk_rec_ins` FOREIGN KEY (`InsumoID`) REFERENCES `insumo` (`InsumoID`),
  ADD CONSTRAINT `fk_rec_var` FOREIGN KEY (`VarianteID`) REFERENCES `plato_variante` (`VarianteID`);

--
-- Constraints for table `repartidor`
--
ALTER TABLE `repartidor`
  ADD CONSTRAINT `fk_rep_emp` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`);

--
-- Constraints for table `reserva`
--
ALTER TABLE `reserva`
  ADD CONSTRAINT `fk_res_cliente` FOREIGN KEY (`ClienteID`) REFERENCES `cliente` (`ClienteID`),
  ADD CONSTRAINT `fk_res_empleado` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`),
  ADD CONSTRAINT `fk_res_mesa` FOREIGN KEY (`MesaID`) REFERENCES `mesa` (`MesaID`),
  ADD CONSTRAINT `fk_res_pedido` FOREIGN KEY (`PedidoID`) REFERENCES `pedido` (`PedidoID`);

--
-- Constraints for table `reserva_historial`
--
ALTER TABLE `reserva_historial`
  ADD CONSTRAINT `fk_rh_empleado` FOREIGN KEY (`EmpleadoID`) REFERENCES `empleado` (`EmpleadoID`),
  ADD CONSTRAINT `fk_rh_reserva` FOREIGN KEY (`ReservaID`) REFERENCES `reserva` (`ReservaID`);

--
-- Constraints for table `seccion_web`
--
ALTER TABLE `seccion_web`
  ADD CONSTRAINT `seccion_web_ibfk_1` FOREIGN KEY (`Categoria_Filtro`) REFERENCES `categoria` (`CatID`) ON DELETE SET NULL;

--
-- Constraints for table `usuario`
--
ALTER TABLE `usuario`
  ADD CONSTRAINT `fk_usu_tipo` FOREIGN KEY (`TipUsuID`) REFERENCES `tipo_usuario` (`TipUsuID`);
SET FOREIGN_KEY_CHECKS=1;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
