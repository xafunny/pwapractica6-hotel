-- Hotel Mirador - Sistema de reservas de hotel (Práctica 6, Programación Web Avanzada)
-- Crear la base `hotel` (utf8mb4_unicode_ci) en phpMyAdmin e importar este archivo.
-- Todos los usuarios de prueba tienen la clave 123456.
--
-- Cambios sobre la base del enunciado: nombres en español, `necesidades` y `ofertas`
-- para que el proveedor vea lo que hace falta y oferte; `suministros` guarda lo adjudicado.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS suministros;
DROP TABLE IF EXISTS ofertas;
DROP TABLE IF EXISTS necesidades;
DROP TABLE IF EXISTS reservas;
DROP TABLE IF EXISTS habitaciones;
DROP TABLE IF EXISTS usuarios;
DROP TABLE IF EXISTS roles;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE roles (
  id     INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(20) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO roles (id, nombre) VALUES
  (1, 'Administrador'), (2, 'Gerente'), (3, 'Recepcionista'), (4, 'Cliente'), (5, 'Proveedor');

CREATE TABLE usuarios (
  id         INT AUTO_INCREMENT PRIMARY KEY,
  nombre     VARCHAR(60)  NOT NULL,
  correo     VARCHAR(100) NOT NULL UNIQUE,
  contrasena VARCHAR(255) NOT NULL,
  id_rol     INT          NOT NULL,
  telefono   VARCHAR(15)  NULL,
  documento  VARCHAR(20)  NULL,   -- cédula o pasaporte (clientes)
  empresa    VARCHAR(80)  NULL,   -- solo proveedores
  activo     TINYINT(1)   NOT NULL DEFAULT 1,
  creado_en  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_rol) REFERENCES roles(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- la disponibilidad por fechas se calcula con las reservas; `estado` es para sacarla de servicio
CREATE TABLE habitaciones (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  numero      INT           NOT NULL UNIQUE,
  tipo        ENUM('Simple','Doble','Suite','Familiar') NOT NULL,
  capacidad   INT           NOT NULL,
  precio      DECIMAL(10,2) NOT NULL,   -- por noche
  descripcion VARCHAR(255)  NULL,
  estado      ENUM('Disponible','Mantenimiento') NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- registrada_por: el recepcionista que la hizo (NULL si la hizo el cliente)
CREATE TABLE reservas (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente     INT           NOT NULL,
  id_habitacion  INT           NOT NULL,
  registrada_por INT           NULL,
  fecha_reserva  DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  fecha_entrada  DATE          NOT NULL,
  fecha_salida   DATE          NOT NULL,
  huespedes      INT           NOT NULL DEFAULT 1,
  total          DECIMAL(10,2) NOT NULL,
  estado         ENUM('Confirmada','Hospedado','Finalizada','Cancelada') NOT NULL DEFAULT 'Confirmada',
  notas          VARCHAR(255)  NULL,
  INDEX (fecha_entrada, fecha_salida),
  FOREIGN KEY (id_cliente)     REFERENCES usuarios(id) ON DELETE CASCADE,
  FOREIGN KEY (id_habitacion)  REFERENCES habitaciones(id),
  FOREIGN KEY (registrada_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- lo que el hotel necesita comprar (lo publica el gerente)
CREATE TABLE necesidades (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  articulo     VARCHAR(80)  NOT NULL,
  categoria    ENUM('Alimentos','Limpieza','Lencería','Amenidades','Mantenimiento') NOT NULL,
  cantidad     INT          NOT NULL,
  unidad       VARCHAR(20)  NOT NULL DEFAULT 'unidades',
  fecha_limite DATE         NOT NULL,
  estado       ENUM('Abierta','Adjudicada','Cerrada') NOT NULL DEFAULT 'Abierta',
  creado_por   INT          NULL,
  creado_en    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (creado_por) REFERENCES usuarios(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ofertas (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  id_necesidad    INT           NOT NULL,
  id_proveedor    INT           NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  dias_entrega    INT           NOT NULL,
  comentario      VARCHAR(255)  NULL,
  estado          ENUM('Pendiente','Aceptada','Rechazada') NOT NULL DEFAULT 'Pendiente',
  creado_en       DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE (id_necesidad, id_proveedor),
  FOREIGN KEY (id_necesidad) REFERENCES necesidades(id) ON DELETE CASCADE,
  FOREIGN KEY (id_proveedor) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- suministros comprados (tabla `supplies` del enunciado): se crea al aceptar una oferta
CREATE TABLE suministros (
  id           INT AUTO_INCREMENT PRIMARY KEY,
  nombre       VARCHAR(80)   NOT NULL,
  cantidad     INT           NOT NULL,
  id_proveedor INT           NOT NULL,
  id_oferta    INT           NULL,
  costo        DECIMAL(10,2) NOT NULL,
  fecha        DATE          NOT NULL,
  FOREIGN KEY (id_proveedor) REFERENCES usuarios(id),
  FOREIGN KEY (id_oferta)    REFERENCES ofertas(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------- datos de prueba ----------
INSERT INTO usuarios (id, nombre, correo, contrasena, id_rol, telefono, documento, empresa) VALUES
  (1, 'Administrador',     'admin@hotel.com',         '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 1, '0950000001', NULL, NULL),
  (2, 'Ricardo Montalvo',  'gerente@hotel.com',       '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 2, '0950000002', NULL, NULL),
  (3, 'Daniela Paz',       'recepcion@hotel.com',     '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 3, '0950000003', NULL, NULL),
  (4, 'Sebastián Lara',    'cliente@hotel.com',       '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 4, '0950000004', '0912345678', NULL),
  (5, 'Distribuidora Sol', 'proveedor@hotel.com',     '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 5, '0950000005', NULL, 'Distribuidora Sol S.A.'),
  (6, 'Mariana Vélez',     'mariana@correo.com',      '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 4, '0950000006', '0923456789', NULL),
  (7, 'Limpiamax',         'ventas@limpiamax.com',    '$2y$12$vqrXfBTSX9LNMiohZ/Pk9.wCvYEubcrqefCdrdElFWM9s0s.ucqRi', 5, '0950000007', NULL, 'Limpiamax Cía. Ltda.');

INSERT INTO habitaciones (id, numero, tipo, capacidad, precio, descripcion, estado) VALUES
  (1, 101, 'Simple',   1,  45.00, 'Cama de 1 plaza y media, vista al jardín', 'Disponible'),
  (2, 102, 'Simple',   1,  45.00, 'Cama de 1 plaza y media', 'Disponible'),
  (3, 201, 'Doble',    2,  70.00, 'Cama matrimonial, balcón', 'Disponible'),
  (4, 202, 'Doble',    2,  70.00, 'Dos camas de 1 plaza y media', 'Disponible'),
  (5, 203, 'Doble',    2,  75.00, 'Cama matrimonial, vista al mar', 'Mantenimiento'),
  (6, 301, 'Familiar', 4, 110.00, 'Dos ambientes, 1 matrimonial y 2 individuales', 'Disponible'),
  (7, 401, 'Suite',    2, 160.00, 'Jacuzzi, sala y vista panorámica', 'Disponible');

INSERT INTO reservas (id_cliente, id_habitacion, registrada_por, fecha_reserva, fecha_entrada, fecha_salida, huespedes, total, estado, notas) VALUES
  (4, 3, NULL, NOW() - INTERVAL 10 DAY, CURDATE() - INTERVAL 7 DAY, CURDATE() - INTERVAL 5 DAY, 2, 140.00, 'Finalizada', NULL),
  (6, 1, 3,    NOW() - INTERVAL 3 DAY,  CURDATE() - INTERVAL 1 DAY, CURDATE() + INTERVAL 2 DAY, 1, 135.00, 'Hospedado',  'Llega tarde'),
  (4, 7, NULL, NOW() - INTERVAL 2 DAY,  CURDATE() + INTERVAL 5 DAY, CURDATE() + INTERVAL 8 DAY, 2, 480.00, 'Confirmada', 'Aniversario'),
  (6, 6, 3,    NOW() - INTERVAL 1 DAY,  CURDATE(),                  CURDATE() + INTERVAL 3 DAY, 3, 330.00, 'Confirmada', NULL);

INSERT INTO necesidades (id, articulo, categoria, cantidad, unidad, fecha_limite, estado, creado_por) VALUES
  (1, 'Toallas de baño blancas',   'Lencería',   60,  'unidades', CURDATE() + INTERVAL 10 DAY, 'Abierta',    2),
  (2, 'Café en grano',             'Alimentos',  20,  'kg',       CURDATE() + INTERVAL 7 DAY,  'Abierta',    2),
  (3, 'Desinfectante multiuso',    'Limpieza',   40,  'litros',   CURDATE() - INTERVAL 2 DAY,  'Adjudicada', 2),
  (4, 'Kits de amenidades',        'Amenidades', 200, 'unidades', CURDATE() + INTERVAL 15 DAY, 'Abierta',    2);

INSERT INTO ofertas (id, id_necesidad, id_proveedor, precio_unitario, dias_entrega, comentario, estado) VALUES
  (1, 3, 7, 2.40, 2, 'Galones de 4 litros', 'Aceptada'),
  (2, 3, 5, 2.90, 4, NULL,                  'Rechazada'),
  (3, 2, 5, 9.50, 3, 'Café de altura, Loja', 'Pendiente');

INSERT INTO suministros (nombre, cantidad, id_proveedor, id_oferta, costo, fecha) VALUES
  ('Desinfectante multiuso', 40, 7, 1, 96.00, CURDATE() - INTERVAL 3 DAY);
