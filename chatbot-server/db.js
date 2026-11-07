/**
 * SmartExam AI (UJOL) - Database Connection Pool Module
 * Menggunakan mysql2/promise untuk koneksi asinkron ke MySQL smartexam_db
 */

const mysql = require("mysql2");

// Konfigurasi Database (selaras dengan app/Config/Database.php)
const dbConfig = {
  host: process.env.DB_HOST || "127.0.0.1",
  port: parseInt(process.env.DB_PORT || "3306", 10),
  user: process.env.DB_USER || "root",
  password: process.env.DB_PASSWORD || "",
  database: process.env.DB_NAME || "smartexam_db",
  waitForConnections: true,
  connectionLimit: 15,
  maxIdle: 8,
  idleTimeout: 60000,
  queueLimit: 0,
  charset: "utf8mb4",
  connectTimeout: 10000,
  enableKeepAlive: true,
  keepAliveInitialDelay: 10000
};

// Buat pool koneksi promise
const pool = mysql.createPool(dbConfig).promise();

/**
 * Uji koneksi basis data
 * @returns {Promise<{connected: boolean, message: string}>}
 */
async function testDbConnection() {
  try {
    const [rows] = await pool.query("SELECT 1 AS status, DATABASE() AS db_name");
    const dbName = rows[0]?.db_name || dbConfig.database;
    console.log(`[Database] Terhubung ke MySQL "${dbName}" di ${dbConfig.host}:${dbConfig.port}`);
    return { connected: true, message: `Connected to ${dbName}` };
  } catch (err) {
    console.error(`[Database Error] Gagal terhubung ke MySQL:`, err.message);
    return { connected: false, message: err.message };
  }
}

module.exports = {
  pool,
  dbConfig,
  testDbConnection
};
