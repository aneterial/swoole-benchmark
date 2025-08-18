const { createClient } = require('redis');

const redis = createClient({ url: 'redis://redis:6379' });
redis.on('error', (err) => console.error('Redis Client Error', err));

(async () => {
  try {
    await redis.connect();
    console.log('Connected to Redis');
  } catch (e) {
    console.error('Failed to connect to Redis', e);
  }
})();

module.exports = redis;
