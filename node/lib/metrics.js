const redis = require('./redis');

const METRIC_PREFIX = 'node:memory:';
const MetricsType = {
  MemoryStart: 'start',
  MemoryProcess: 'process',
  MemoryEnd: 'end',
};

function getMemoryUsage() {
  return process.memoryUsage().heapUsed;
}

function formatBytes(bytes, precision = 2) {
  const units = ['B', 'KB', 'MB', 'GB', 'TB'];
  let value = bytes;
  let i = 0;

  while (value > 1024 && i < units.length - 1) {
    value /= 1024;
    i++;
  }

  return `${value.toFixed(precision)} ${units[i]}`;
}

async function saveMetric(type, value) {
  try {
    await redis.rPush(METRIC_PREFIX + type, String(value));
  } catch (e) {
    console.error('Failed to save metric', type, e.message);
  }
}

async function getStats(type) {
  try {
    const values = await redis.lRange(METRIC_PREFIX + type, 0, -1);

    if (!values || values.length === 0) {
      return {
        raw: { max: 0, min: 0, avg: 0, p95: 0 },
        formatted: { max: formatBytes(0), min: formatBytes(0), avg: formatBytes(0), p95: formatBytes(0) },
        values: [],
        count: 0
      };
    }

    const sorted = values.map(v => parseInt(v, 10)).sort((a, b) => a - b);
    const count = sorted.length;

    if (count === 0) {
      return {
        raw: { max: 0, min: 0, avg: 0, p95: 0 },
        formatted: { max: formatBytes(0), min: formatBytes(0), avg: formatBytes(0), p95: formatBytes(0) },
        values: [],
        count: 0
      };
    }

    const max = sorted[count - 1];
    const min = sorted[0];
    const sum = sorted.reduce((acc, val) => acc + val, 0);
    const avg = Math.floor(sum / count);

    const p95Index = Math.floor(count * 0.95);
    const p95 = sorted[Math.min(p95Index, count - 1)];

    const lastValues = sorted.length > 50 ? sorted.slice(-50) : sorted;

    return {
      raw: { max, min, avg, p95 },
      formatted: {
        max: formatBytes(max),
        min: formatBytes(min),
        avg: formatBytes(avg),
        p95: formatBytes(p95)
      },
      values: lastValues,
      count
    };
  } catch (e) {
    console.error('Failed to get stats:', e.message);
    return {
      raw: { max: 0, min: 0, avg: 0, p95: 0 },
      formatted: { max: formatBytes(0), min: formatBytes(0), avg: formatBytes(0), p95: formatBytes(0) },
      values: [],
      count: 0
    };
  }
}

module.exports = {
  MetricsType,
  getMemoryUsage,
  saveMetric,
  getStats
};
