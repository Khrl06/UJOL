/**
 * Test Suite: Chatbot Direct Intents (Identity & Count Queries)
 * Sesuai permintaan pengguna:
 * 1. "ada berapa" = jumlah angka pasti
 * 2. "saya siapa" / "login dengan apa" = nama & akun login, tanpa bercerita
 */

const { resolveDatabaseContext } = require("./dbQueries");

async function runTests() {
  console.log("==============================================================");
  console.log(" TEST: CHATBOT DIRECT INTENTS (COUNT & IDENTITY)");
  console.log("==============================================================\n");

  let pass = 0;
  let fail = 0;

  function assertTest(name, condition, text) {
    if (condition) {
      console.log(` [PASS] ${name}`);
      pass++;
    } else {
      console.log(` [FAIL] ${name}`);
      console.log(`        Output: ${text}`);
      fail++;
    }
  }

  // --- Test Group 1: Saya Siapa / Login Dengan Apa ---
  console.log("--- 1. Testing 'Saya Siapa' / 'Login Dengan Apa' ---");

  // Siswa
  const resSiswa = await resolveDatabaseContext("saya siapa", {
    id: 3,
    name: "Ahmad Fauzi Pratama",
    role: "siswa"
  });
  assertTest("Tanya 'saya siapa' (Siswa) -> Nama & Akun", 
    resSiswa.intent === "user_identity" && 
    resSiswa.contextText.includes("Ahmad Fauzi Pratama") && 
    resSiswa.contextText.includes("Siswa"),
    resSiswa.contextText
  );

  // Guru
  const resGuru = await resolveDatabaseContext("saya login dengan menggunakan akun apa", {
    id: 2,
    name: "Budi Prasetyo, S.Pd., M.Kom.",
    role: "guru"
  });
  assertTest("Tanya 'saya login dengan menggunakan akun apa' (Guru) -> Nama & Akun", 
    resGuru.intent === "user_identity" && 
    resGuru.contextText.includes("Budi Prasetyo") && 
    resGuru.contextText.includes("Guru"),
    resGuru.contextText
  );

  // Admin
  const resAdmin = await resolveDatabaseContext("siapa saya", {
    id: 1,
    name: "Administrator Utama",
    role: "admin"
  });
  assertTest("Tanya 'siapa saya' (Admin) -> Nama & Akun", 
    resAdmin.intent === "user_identity" && 
    resAdmin.contextText.includes("Administrator Utama") && 
    resAdmin.contextText.includes("Admin"),
    resAdmin.contextText
  );

  // Guest (Belum Login)
  const resGuest = await resolveDatabaseContext("saya siapa", null);
  assertTest("Tanya 'saya siapa' (Guest) -> Belum login", 
    resGuest.intent === "user_identity" && 
    resGuest.contextText.toLowerCase().includes("belum login"),
    resGuest.contextText
  );

  // --- Test Group 2: Ada Berapa (Jumlah) ---
  console.log("\n--- 2. Testing 'Ada Berapa' (Jumlah Angka Pasti) ---");

  // Siswa
  const countSiswa = await resolveDatabaseContext("ada berapa siswa", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa siswa' -> Jumlah siswa", 
    countSiswa.intent === "count_summary" && 
    countSiswa.contextText.includes("Jumlah siswa yang terdaftar ada"),
    countSiswa.contextText
  );

  // Murid (sinonim)
  const countMurid = await resolveDatabaseContext("berapa jumlah murid?", { id: 1, role: "admin" });
  assertTest("Tanya 'berapa jumlah murid?' -> Jumlah siswa", 
    countMurid.intent === "count_summary" && 
    countMurid.contextText.includes("Jumlah siswa yang terdaftar ada"),
    countMurid.contextText
  );

  // Guru
  const countGuru = await resolveDatabaseContext("ada berapa guru", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa guru' -> Jumlah guru", 
    countGuru.intent === "count_summary" && 
    countGuru.contextText.includes("Jumlah guru yang terdaftar ada"),
    countGuru.contextText
  );

  // Kelas
  const countKelas = await resolveDatabaseContext("ada berapa kelas?", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa kelas?' -> Jumlah kelas", 
    countKelas.intent === "count_summary" && 
    countKelas.contextText.includes("Jumlah kelas yang terdaftar ada"),
    countKelas.contextText
  );

  // Mapel
  const countMapel = await resolveDatabaseContext("ada berapa mata pelajaran", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa mata pelajaran' -> Jumlah mapel", 
    countMapel.intent === "count_summary" && 
    countMapel.contextText.includes("Jumlah mata pelajaran yang terdaftar ada"),
    countMapel.contextText
  );

  // Ujian
  const countUjian = await resolveDatabaseContext("ada berapa ujian", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa ujian' -> Jumlah paket ujian", 
    countUjian.intent === "count_summary" && 
    countUjian.contextText.includes("Jumlah paket ujian ada"),
    countUjian.contextText
  );

  // Bank Soal
  const countBank = await resolveDatabaseContext("ada berapa bank soal", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa bank soal' -> Jumlah bank soal", 
    countBank.intent === "count_summary" && 
    countBank.contextText.includes("Jumlah paket bank soal ada"),
    countBank.contextText
  );

  // Butir Soal
  const countSoal = await resolveDatabaseContext("ada berapa butir soal", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa butir soal' -> Jumlah butir soal", 
    countSoal.intent === "count_summary" && 
    countSoal.contextText.includes("Jumlah butir soal yang terdaftar ada"),
    countSoal.contextText
  );

  // Umum: "ada berapa semuanya"
  const countAll = await resolveDatabaseContext("ada berapa semuanya", { id: 1, role: "admin" });
  assertTest("Tanya 'ada berapa semuanya' -> Ringkasan jumlah data", 
    countAll.intent === "count_summary" && 
    countAll.contextText.includes("Jumlah data terdaftar di UJOL"),
    countAll.contextText
  );

  console.log("\n==============================================================");
  console.log(` HASIL: ${pass} PASSED, ${fail} FAILED`);
  console.log("==============================================================");

  process.exit(fail > 0 ? 1 : 0);
}

runTests();

