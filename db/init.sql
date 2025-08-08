CREATE EXTENSION IF NOT EXISTS pg_trgm;

CREATE TABLE IF NOT EXISTS users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    age INTEGER,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE INDEX IF NOT EXISTS idx_users_name ON users USING gin (name gin_trgm_ops);

TRUNCATE TABLE users;

INSERT INTO users (name, email, age)
SELECT
    'User' || generate_series % 5000,
    'user' || generate_series || '@test.com',
    18 + (generate_series % 50)
FROM generate_series(1, 1000000);

ANALYZE users;
