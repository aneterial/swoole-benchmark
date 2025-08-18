const http = require('http');
const Router = require('find-my-way');
const setupRoutes = require('./routes');
const { initializeWorker, closeWorker } = require('./lib/database');

const router = Router({
  defaultRoute: (req, res) => {
    res.statusCode = 404;
    res.setHeader('Content-Type', 'application/json');
    res.end(JSON.stringify({ error: 'Not found' }));
  }
});

setupRoutes(router);

const server = http.createServer((req, res) => {
  router.lookup(req, res);
});

const PORT = 8085;
const HOST = '0.0.0.0';

// Инициализируем соединение с базой данных при старте воркера
async function startServer() {
  try {
    await initializeWorker();

    server.listen(PORT, HOST, () => {
      console.log(`Worker ${process.pid} listening on http://${HOST}:${PORT}`);
    });
  } catch (error) {
    console.error('Failed to initialize database connection:', error);
    process.exit(1);
  }
}

// Обработка сигналов для корректного завершения
process.on('SIGINT', async () => {
  console.log('Received SIGINT. Shutting down gracefully...');
  await closeWorker();
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});

process.on('SIGTERM', async () => {
  console.log('Received SIGTERM. Shutting down gracefully...');
  await closeWorker();
  server.close(() => {
    console.log('Server closed');
    process.exit(0);
  });
});

startServer();
