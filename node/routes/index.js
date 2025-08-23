const { v7: uuidv7 } = require('uuid');
const { MetricsType, getMemoryUsage, saveMetric, getStats } = require('../lib/metrics');
const { getUsers, getUsersV2 } = require('../lib/users');

function setupRoutes(router) {
  router.on('GET', '/sample', (req, res) => {
    res.setHeader('Content-Type', 'application/json');
    res.end(JSON.stringify({ status: 'ok' }));
  });

  router.on('GET', '/metrics/:type', async (req, res, params) => {
    const type = params.type;

    if (!type) {
      res.statusCode = 400;
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({ error: 'Invalid metrics path' }));
      return;
    }

    try {
      const stats = await getStats(type);
      res.setHeader('Content-Type', 'application/json');
      res.statusCode = 200;
      res.end(JSON.stringify(stats));
    } catch (e) {
      console.error('Failed to handle metrics request:', e);
      res.statusCode = 500;
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({ error: 'Internal Server Error' }));
    }
  });

  router.on('GET', '/users/:name', async (req, res, params) => {
    const path = req.url;
    const method = req.method;
    const requestId = uuidv7();

    await saveMetric(MetricsType.MemoryStart, getMemoryUsage());
    console.log(`Start request [${requestId}]: ${method} ${path}`);

    const name = params.name || '';

    try {
      const { users, total } = await getUsers(name);

      await saveMetric(MetricsType.MemoryProcess, getMemoryUsage());

      res.setHeader('Content-Type', 'application/json');
      res.statusCode = 200;
      res.end(JSON.stringify({ total, data: users }));
    } catch (e) {
      console.error('Failed to handle /users:', e);
      res.statusCode = 500;
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({ error: 'Internal Server Error' }));
    } finally {
      await saveMetric(MetricsType.MemoryEnd, getMemoryUsage());
      console.log(`End request [${requestId}]: ${method} ${path}`);
    }
  });

  router.on('GET', '/v2/users/:name', async (req, res, params) => {
    const path = req.url;
    const method = req.method;
    const requestId = uuidv7();

    await saveMetric(MetricsType.MemoryStart, getMemoryUsage());
    console.log(`Start request [${requestId}]: ${method} ${path}`);

    const name = params.name || '';

    try {
      const { users, total } = await getUsersV2(name);

      await saveMetric(MetricsType.MemoryProcess, getMemoryUsage());

      res.setHeader('Content-Type', 'application/json');
      res.statusCode = 200;
      res.end(JSON.stringify({ total, data: users }));
    } catch (e) {
      console.error('Failed to handle /users:', e);
      res.statusCode = 500;
      res.setHeader('Content-Type', 'application/json');
      res.end(JSON.stringify({ error: 'Internal Server Error' }));
    } finally {
      await saveMetric(MetricsType.MemoryEnd, getMemoryUsage());
      console.log(`End request [${requestId}]: ${method} ${path}`);
    }
  });
}

module.exports = setupRoutes;
