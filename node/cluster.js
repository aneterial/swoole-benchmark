const cluster = require('cluster');
const os = require('os');
const { spawn } = require('child_process');

const WORKER_COUNT = process.env.WORKER_COUNT || os.cpus().length;

if (cluster.isMaster) {
  console.log(`Master ${process.pid} is running`);
  console.log(`Starting ${WORKER_COUNT} workers...`);

  for (let i = 0; i < WORKER_COUNT; i++) {
    cluster.fork();
  }

  cluster.on('exit', (worker, code, signal) => {
    console.log(`Worker ${worker.process.pid} died. Restarting...`);
    cluster.fork();
  });

  process.on('SIGINT', () => {
    console.log('Received SIGINT. Shutting down gracefully...');
    for (const id in cluster.workers) {
      cluster.workers[id].kill();
    }
    process.exit(0);
  });

  process.on('SIGTERM', () => {
    console.log('Received SIGTERM. Shutting down gracefully...');
    for (const id in cluster.workers) {
      cluster.workers[id].kill();
    }
    process.exit(0);
  });

} else {
  require('./server.js');
}
