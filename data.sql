-- Estrutura e credenciais de demonstração para testes locais.
-- Em produção, a senha deve ser armazenada usando hash, nunca em texto puro.
CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(120) NOT NULL,
    role VARCHAR(20) NOT NULL CHECK (role IN ('admin', 'user')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Cria um administrador e um usuário comum para validar os dois perfis.
INSERT INTO users (id, name, email, password, role) VALUES
    (1, 'Administrador', 'admin@escolaagricola.com', 'Admin@123', 'admin'),
    (2, 'Usuário de Teste', 'usuario@escolaagricola.com', 'User@123', 'user');
