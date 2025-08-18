const pgPool = require('./database');

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

module.exports = {
  getUsers
};
