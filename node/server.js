const http = require('http');
const Router = require('find-my-way');
const setupRoutes = require('./routes');

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
server.listen(PORT, HOST, () => {
  console.log(`Worker ${process.pid} listening on http://${HOST}:${PORT}`);
});
