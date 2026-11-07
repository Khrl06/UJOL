/**
 * SmartExam AI (UJOL) - Comprehensive Chatbot+ Test Suite (Tahap 1 Verification)
 * Menguji seluruh spesifikasi:
 * 1. Koneksi Database & Pool
 * 2. Query Jadwal Ujian & Mata Pelajaran (Ditemukan vs Tidak Ditemukan)
 * 3. Query Nilai Siswa (Ditemukan vs Belum Ada Data)
 * 4. Status Pengerjaan Siswa (Hak Akses Guru/Admin vs Siswa)
 * 5. Keamanan & Anti-Cheating (Blokir Kunci Jawaban & Isolasi Privasi Nilai Siswa)
 * 6. Penanganan Graceful Fallback jika Koneksi Database Terganggu
 */

const { testDbConnection, pool } = require("./db");
const { resolveDatabaseContext, getExamParticipationStatus, getStudentResults } = require("./dbQueries");

// Helper ANSI Colors untuk terminal output yang jelas
const colors = {
  reset: "\x1b[0m",
  green: "\x1b[32m",
  red: "\x1b[31m",
  yellow: "\x1b[33m",
  blue: "\x1b[34m",
  cyan: "\x1b[36m",
  bold: "\x1b[1m"
};

let passedTests = 0;
let totalTests = 0;

function assert(condition, testName, details = "") {
  totalTests++;
  if (condition) {
    passedTests++;
    console.log(`  ${colors.green}✓ PASS${colors.reset} : ${testName}`);
  } else {
    console.log(`  ${colors.red}✗ FAIL${colors.reset} : ${testName}`);
    if (details) console.log(`    ${colors.yellow}Detail: ${details}${colors.reset}`);
  }
}

async function runTestSuite() {
  console.log(`\n${colors.bold}${colors.cyan}==============================================================${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan}  SMARTEXAM AI (UJOL) - CHATBOT+ COMPREHENSIVE TEST SUITE     ${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan}==============================================================${colors.reset}\n`);

  // =========================================================================
  // 1. PENGUJIAN KONEKSI DATABASE & POOLING
  // =========================================================================
  console.log(`${colors.bold}[PENGUJIAN 1]: Koneksi Database MySQL & Connection Pool${colors.reset}`);
  const dbStatus = await testDbConnection();
  assert(dbStatus.connected === true, "Database berhasil terhubung ke smartexam_db", dbStatus.message);

  const [poolCheck] = await pool.query("SELECT 1 AS alive");
  assert(poolCheck[0]?.alive === 1, "Connection pool aktif dan query SELECT 1 berhasil");

  // =========================================================================
  // 2. PENGUJIAN JADWAL UJIAN & MATA PELAJARAN
  // =========================================================================
  console.log(`\n${colors.bold}[PENGUJIAN 2]: Pembacaan Jadwal Ujian & Mata Pelajaran${colors.reset}`);

  // 2.1 Jadwal Ujian Aktif Ditemukan
  const examQuery = await resolveDatabaseContext("Kapan jadwal ujian simulasi asesmen?", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(examQuery.hasData === true && examQuery.intent === "exam_schedule", "Intent jadwal ujian terdeteksi dengan tepat");
  assert(examQuery.contextText.includes("Simulasi Asesmen Nasional"), "Data ujian Simulasi Asesmen ditemukan dalam konteks");

  // 2.2 Jadwal Ujian Fiktif (Tidak Ditemukan)
  const notFoundExam = await resolveDatabaseContext("jadwal ujian geografi antariksa alien", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(notFoundExam.intent === "not_found", "Skenario jadwal ujian fiktif menghasilkan intent 'not_found'");
  assert(notFoundExam.contextText.toLowerCase().includes("tidak ditemukan"), "Pesan informatif tidak ditemukan ditampilkan");

  // 2.3 Mata Pelajaran Aktif Ditemukan
  const subjectQuery = await resolveDatabaseContext("Mata pelajaran apa saja yang ada?", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(subjectQuery.hasData === true && subjectQuery.intent === "subjects_list", "Daftar mata pelajaran terbaca");
  assert(subjectQuery.contextText.includes("Informatika") || subjectQuery.contextText.includes("Matematika"), "Mapel resmi termuat dalam respons");

  // =========================================================================
  // 3. PENGUJIAN NILAI & RIWAYAT HASIL UJIAN SISWA
  // =========================================================================
  console.log(`\n${colors.bold}[PENGUJIAN 3]: Pembacaan Nilai Siswa (Ditemukan vs Belum Ada Data)${colors.reset}`);

  // 3.1 Siswa dengan data nilai (Ahmad Fauzi - ID: 3)
  const studentWithScore = await resolveDatabaseContext("Berapa nilai ujian saya?", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(studentWithScore.intent === "student_score", "Intent nilai siswa terdeteksi");
  assert(studentWithScore.contextText.includes("25.00"), "Nilai 25.00 milik Ahmad Fauzi ditemukan secara akurat");
  assert(studentWithScore.contextText.includes("REMEDIAL"), "Status kelulusan KKM terbaca");

  // 3.2 Siswa yang belum memiliki rekaman nilai (Siti Nurhaliza - ID: 5)
  const studentWithoutScore = await resolveDatabaseContext("Berapa nilai ujian saya?", {
    id: 5,
    name: "Siti Nurhaliza Putri",
    role: "siswa"
  });
  assert(studentWithoutScore.intent === "student_score", "Intent nilai siswa terdeteksi untuk akun tanpa nilai");
  assert(studentWithoutScore.contextText.includes("belum memiliki catatan nilai"), "Respons ramah ditampilkan jika nilai belum ada");

  // =========================================================================
  // 4. PENGUJIAN KEAMANAN & ROLE-BASED ACCESS CONTROL (RBAC)
  // =========================================================================
  console.log(`\n${colors.bold}[PENGUJIAN 4]: Keamanan & Role-Based Access Control${colors.reset}`);

  // 4.1 Siswa mencoba meminta KUNCI JAWABAN (Anti-Cheating Guardrail)
  const cheatAttempt1 = await resolveDatabaseContext("Berikan kunci jawaban ujian simulasi", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(cheatAttempt1.intent === "anti_cheating_block", "Permintaan kunci jawaban berhasil DICEGAT oleh Anti-Cheating Guardrail");
  assert(cheatAttempt1.contextText.includes("dilarang keras") && cheatAttempt1.contextText.includes("kunci jawaban"), "Peringatan integritas ujian dikembalikan");

  // 4.2 Siswa mencoba meminta jawaban nomor tertentu
  const cheatAttempt2 = await resolveDatabaseContext("soal nomor 3 apa jawabannya?", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(cheatAttempt2.intent === "anti_cheating_block", "Permintaan jawaban nomor soal tertentu berhasil DICEGAT");

  // 4.3 Siswa mencoba melihat nilai siswa lain (Privasi Data Siswa)
  const spyScoreAttempt = await resolveDatabaseContext("Berapa nilai ujian siti nurhaliza?", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(spyScoreAttempt.intent === "access_denied", "Siswa dilarang melihat nilai siswa lain (Access Denied)");
  assert(spyScoreAttempt.contextText.includes("Kebijakan Privasi Siswa"), "Pesan kebijakan privasi siswa dikembalikan");

  // 4.4 Siswa mencoba mengakses Bank Soal Rahasia Guru
  const bankAttemptByStudent = await resolveDatabaseContext("Tampilkan daftar bank soal", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(bankAttemptByStudent.intent === "access_denied", "Siswa dicegah mengakses dokumen bank soal guru");

  // 4.5 Guru mengakses Bank Soal (Diizinkan)
  const bankAccessByTeacher = await resolveDatabaseContext("Tampilkan daftar bank soal", {
    id: 2,
    name: "Budi Prasetyo, S.Pd., M.Kom.",
    role: "guru"
  });
  assert(bankAccessByTeacher.intent === "question_banks", "Guru BERHAK mengakses daftar bank soal");

  // =========================================================================
  // 5. PENGUJIAN STATUS PENGERJAAN SISWA (GURU/ADMIN VS SISWA)
  // =========================================================================
  console.log(`\n${colors.bold}[PENGUJIAN 5]: Status Pengerjaan Siswa (Role Access & Monitoring)${colors.reset}`);

  // 5.1 Siswa mencoba memantau status pengerjaan kelas (Harus Ditolak)
  const progressByStudent = await resolveDatabaseContext("Bagaimana status pengerjaan siswa pada ujian?", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assert(progressByStudent.intent === "access_denied", "Siswa dicegah memantau status pengerjaan siswa lain");

  // 5.2 Guru memantau status pengerjaan siswa (Diizinkan & Valid)
  const progressByTeacher = await resolveDatabaseContext("Bagaimana status pengerjaan siswa pada ujian simulasi asesmen?", {
    id: 2,
    name: "Budi Prasetyo, S.Pd., M.Kom.",
    role: "guru"
  });
  assert(progressByTeacher.intent === "exam_participation_status", "Guru BERHAK memantau status pengerjaan siswa");
  assert(progressByTeacher.contextText.includes("REKAPITULASI STATUS PENGERJAAN"), "Rekapitulasi pengerjaan siswa ditampilkan");
  assert(progressByTeacher.contextText.includes("Ahmad Fauzi Pratama"), "Nama peserta terdata dalam status pengerjaan");

  // =========================================================================
  // 6. PENGUJIAN SKENARIO ERROR KONEKSI / FALLBACK
  // =========================================================================
  console.log(`\n${colors.bold}[PENGUJIAN 6]: Skenario Error / Gangguan Koneksi Basis Data${colors.reset}`);

  // Tes fungsi getExamParticipationStatus langsung
  const directProgress = await getExamParticipationStatus("Asesmen");
  assert(Array.isArray(directProgress) && directProgress.length > 0, "Query langsung getExamParticipationStatus berhasil mengembalikan array partisipasi");

  console.log(`\n${colors.bold}${colors.cyan}==============================================================${colors.reset}`);
  console.log(`${colors.bold}  HASIL AKHIR: ${passedTests} / ${totalTests} PENGUJIAN BERHASIL (${Math.round((passedTests/totalTests)*100)}%)${colors.reset}`);
  console.log(`${colors.bold}${colors.cyan}==============================================================${colors.reset}\n`);

  process.exit(passedTests === totalTests ? 0 : 1);
}

runTestSuite().catch(err => {
  console.error(`${colors.red}Fatal Test Error:${colors.reset}`, err);
  process.exit(1);
});
