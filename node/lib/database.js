const { Pool } = require('pg');

const pgPool = new Pool({
  host: 'db',
  port: 5432,
  user: 'test',
  password: 'test',
  database: 'test',
  max: 100,
  idleTimeoutMillis: 10000,
  connectionTimeoutMillis: 10000,
});

module.exports = pgPool;
