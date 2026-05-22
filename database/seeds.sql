INSERT INTO roles (name, description, is_admin, created_at, updated_at) VALUES
('Super Admin', 'Acesso total', 1, datetime('now'), datetime('now')),
('Administrador', 'Gestão geral', 1, datetime('now'), datetime('now')),
('Responsável Departamento', 'Gestão por departamento', 0, datetime('now'), datetime('now')),
('Formador', 'Criação de formações', 0, datetime('now'), datetime('now')),
('Colaborador', 'Visualização de conteúdos', 0, datetime('now'), datetime('now'));
INSERT INTO departments (name, status, created_at, updated_at) VALUES ('Produção','active',datetime('now'),datetime('now')),('Controlo','active',datetime('now'),datetime('now')),('Qualidade','active',datetime('now'),datetime('now')),('RH','active',datetime('now'),datetime('now')),('Logística','active',datetime('now'),datetime('now')),('Administração','active',datetime('now'),datetime('now')),('IT','active',datetime('now'),datetime('now'));
INSERT INTO programs (name, status, created_at, updated_at) VALUES ('Solune','active',datetime('now'),datetime('now')),('ERP Interno','active',datetime('now'),datetime('now')),('Google Sheets','active',datetime('now'),datetime('now')),('Excel','active',datetime('now'),datetime('now')),('GAP Qualidade','active',datetime('now'),datetime('now')),('TaskForce','active',datetime('now'),datetime('now'));
