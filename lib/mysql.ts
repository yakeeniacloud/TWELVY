import mysql from 'mysql2/promise'

export async function getConnection() {
  const connection = await mysql.createConnection({
    host: process.env.MYSQL_HOST || 'khapmaitpsp.mysql.db',
    user: process.env.MYSQL_USER || 'khapmaitpsp',
    password: process.env.MYSQL_PASSWORD || 'Lretouiva1226',
    database: process.env.MYSQL_DATABASE || 'khapmaitpsp',
    waitForConnections: true,
    connectionLimit: 10,
    queueLimit: 0,
  })

  return connection
}

export async function querySite(sql: string, values?: any[]) {
  const connection = await getConnection()
  try {
    const [results] = await connection.execute(sql, values)
    return results
  } finally {
    await connection.end()
  }
}
