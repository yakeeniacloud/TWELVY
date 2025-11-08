import mysql from 'mysql2/promise'

let connectionPool: mysql.Pool | null = null

function getPool() {
  if (!connectionPool) {
    connectionPool = mysql.createPool({
      host: process.env.MYSQL_HOST || 'khapmaitpsp.mysql.db',
      user: process.env.MYSQL_USER || 'khapmaitpsp',
      password: process.env.MYSQL_PASSWORD || 'Lretouiva1226',
      database: process.env.MYSQL_DATABASE || 'khapmaitpsp',
      waitForConnections: true,
      connectionLimit: 5,
      queueLimit: 0,
      enableKeepAlive: true,
      keepAliveInitialDelay: 0,
    })
  }
  return connectionPool
}

export async function querySite(sql: string, values?: any[]) {
  const pool = getPool()
  const connection = await pool.getConnection()
  try {
    const [results] = await connection.execute(sql, values)
    return results
  } finally {
    connection.release()
  }
}
