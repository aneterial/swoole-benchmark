const { Pool } = require('pg');

const pgPool = new Pool({
  host: 'db',
  port: 5432,
  user: 'test',
  password: 'test',
  database: 'test',
  max: 100,
  idleTimeoutMillis: 30000,
  connectionTimeoutMillis: 10000,
});

// Создаем одно соединение для воркера при инициализации
let workerClient = null;

async function initializeWorker() {
  if (!workerClient) {
    workerClient = await pgPool.connect();
    console.log(`Worker ${process.pid} connected to database`);
  }
  return workerClient;
}

// Функция для получения соединения воркера
function getWorkerClient() {
  if (!workerClient) {
    throw new Error('Database not initialized. Call initializeWorker() first.');
  }
  return workerClient;
}

// Функция для закрытия соединения при завершении воркера
async function closeWorker() {
  if (workerClient) {
    await workerClient.release();
    workerClient = null;
    console.log(`Worker ${process.pid} disconnected from database`);
  }
}

module.exports = {
  initializeWorker,
  getWorkerClient,
  closeWorker,
  pgPool
};
