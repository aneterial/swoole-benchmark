const pgPool = require('./database');
const { v7: uuidv7 } = require('uuid');

async function getUsers(searchName) {
  const client = await pgPool.connect();
  try {
    const usersPromise = client.query(
      'SELECT id, name, email, age, created_at, updated_at FROM users WHERE name LIKE $1 LIMIT 100',
      [`%${searchName}%`]
    );
    const totalPromise = client.query(
      'SELECT COUNT(*)::bigint AS total FROM users WHERE name LIKE $1',
      [`%${searchName}%`]
    );

    const [usersResult, totalResult] = await Promise.all([usersPromise, totalPromise]);

    const users = usersResult.rows;
    const total = Number(totalResult.rows[0]?.total || 0);

    return { users, total };
  } finally {
    client.release();
  }
}

async function getUsersV2(searchName) {
    const client = await pgPool.connect();
    try {
      const usersPromise = client.query(
        'SELECT id, name, email, age, created_at, updated_at FROM users WHERE name LIKE $1 LIMIT 100',
        [`%${searchName}%`]
      );
      const totalPromise = client.query(
        'SELECT COUNT(*)::bigint AS total FROM users WHERE name LIKE $1',
        [`%${searchName}%`]
      );

      const uuidsPromise = (async () => {
        const uuids = [];
        for (let i = 0; i < 1000; i++) {
          uuids.push(uuidv7());
        }
        return uuids;
      })();

      const [usersResult, totalResult, uuids] = await Promise.all([usersPromise, totalPromise, uuidsPromise]);

      const users = usersResult.rows;
      const total = Number(totalResult.rows[0]?.total || 0);

      const usersWithUuids = {};
      users.forEach((user, index) => {
          usersWithUuids[uuids[index]] = user;
      });

      return { users: usersWithUuids, total };
    } finally {
      client.release();
    }
  }

module.exports = {
  getUsers,
  getUsersV2
};
