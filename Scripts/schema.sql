-- DDL para el Sistema de Gestión Médica "MediControl"
-- 5 Tablas Relacionales según Documento de Diseño de Software v1.0

CREATE DATABASE IF NOT EXISTS medicontrol_db;
USE medicontrol_db;

-- 1. Usuarios
CREATE TABLE IF NOT EXISTS Usuarios (
    id_usuario INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    telefono VARCHAR(20) NULL,
    rol ENUM('paciente', 'medico', 'admin') NOT NULL DEFAULT 'paciente',
    firebase_uid VARCHAR(128) NOT NULL UNIQUE,
    fecha_registro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Citas
CREATE TABLE IF NOT EXISTS Citas (
    id_cita INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT UNSIGNED NOT NULL,
    id_medico INT UNSIGNED NOT NULL,
    fecha_hora DATETIME NOT NULL,
    estado ENUM('solicitada', 'confirmada', 'atendida', 'cancelada') NOT NULL DEFAULT 'solicitada',
    motivo VARCHAR(255) NULL,
    CONSTRAINT fk_citas_paciente FOREIGN KEY (id_paciente) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_citas_medico FOREIGN KEY (id_medico) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 3. Consultas
CREATE TABLE IF NOT EXISTS Consultas (
    id_consulta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_cita INT UNSIGNED NOT NULL UNIQUE,
    diagnostico TEXT NOT NULL,
    tratamiento TEXT NULL,
    anotaciones_canvas VARCHAR(255) NULL,
    fecha_atencion DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_consultas_cita FOREIGN KEY (id_cita) REFERENCES Citas(id_cita) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 4. Recetas
CREATE TABLE IF NOT EXISTS Recetas (
    id_receta INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_consulta INT UNSIGNED NOT NULL,
    medicamento VARCHAR(150) NOT NULL,
    dosis VARCHAR(100) NOT NULL,
    indicaciones TEXT NULL,
    CONSTRAINT fk_recetas_consulta FOREIGN KEY (id_consulta) REFERENCES Consultas(id_consulta) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 5. Entregables_Estudios
CREATE TABLE IF NOT EXISTS Entregables_Estudios (
    id_estudio INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_paciente INT UNSIGNED NOT NULL,
    id_cita INT UNSIGNED NULL,
    nombre_archivo VARCHAR(255) NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    tipo_archivo ENUM('pdf', 'jpg', 'png') NOT NULL,
    fecha_subida DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_estudios_paciente FOREIGN KEY (id_paciente) REFERENCES Usuarios(id_usuario) ON DELETE RESTRICT ON UPDATE CASCADE,
    CONSTRAINT fk_estudios_cita FOREIGN KEY (id_cita) REFERENCES Citas(id_cita) ON DELETE RESTRICT ON UPDATE CASCADE
);

-- 6. Cobros (Registro financiero por consulta)
CREATE TABLE IF NOT EXISTS Cobros (
    id_cobro INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    id_consulta INT UNSIGNED NOT NULL,
    monto DECIMAL(10,2) NOT NULL,
    metodo_pago ENUM('efectivo', 'tarjeta', 'transferencia') NOT NULL DEFAULT 'efectivo',
    notas VARCHAR(255) NULL,
    fecha_cobro DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_cobros_consulta FOREIGN KEY (id_consulta) REFERENCES Consultas(id_consulta) ON DELETE RESTRICT ON UPDATE CASCADE
);
