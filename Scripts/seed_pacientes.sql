USE medicontrol_db;
SET NAMES utf8mb4;

-- 1 médico de prueba
INSERT INTO Usuarios (nombre, correo, telefono, rol, firebase_uid) VALUES
('Dr. Carlos Ramírez', 'dr.ramirez@medicontrol.test', '6141234567', 'medico', 'test_medico_001');

-- 10 pacientes de prueba
INSERT INTO Usuarios (nombre, correo, telefono, rol, firebase_uid) VALUES
('Ana López García',       'ana.lopez@test.com',       '6141000001', 'paciente', 'test_paciente_001'),
('Roberto Martínez Díaz',  'roberto.martinez@test.com','6141000002', 'paciente', 'test_paciente_002'),
('María Fernanda Torres',  'maria.torres@test.com',    '6141000003', 'paciente', 'test_paciente_003'),
('Juan Carlos Hernández',  'juan.hernandez@test.com',  '6141000004', 'paciente', 'test_paciente_004'),
('Sofía Rodríguez Peña',   'sofia.rodriguez@test.com', '6141000005', 'paciente', 'test_paciente_005'),
('Luis Ángel Morales',     'luis.morales@test.com',     '6141000006', 'paciente', 'test_paciente_006'),
('Daniela Vargas Cruz',    'daniela.vargas@test.com',  '6141000007', 'paciente', 'test_paciente_007'),
('Fernando Ruiz Castillo', 'fernando.ruiz@test.com',   '6141000008', 'paciente', 'test_paciente_008'),
('Gabriela Sánchez Mora',  'gabriela.sanchez@test.com','6141000009', 'paciente', 'test_paciente_009'),
('Pedro Navarro Ríos',     'pedro.navarro@test.com',   '6141000010', 'paciente', 'test_paciente_010');
