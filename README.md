# 🎓 SMARTEXAM AI (UJOL)
### *Sistem Ujian Online Berbasis AI dan Pengawasan Dual Camera untuk Standarisasi Asesmen Akademik*

[![CI/CD Pipeline](https://github.com/Khrl06/UJOL/actions/workflows/ci.yml/badge.svg)](https://github.com/Khrl06/UJOL/actions/workflows/ci.yml)
[![PHP Version](https://img.shields.io/badge/PHP-8.2%20%7C%208.3-777BB4?logo=php&logoColor=white)](https://www.php.net/)
[![Node.js](https://img.shields.io/badge/Node.js-v20%20LTS-339933?logo=node.js&logoColor=white)](https://nodejs.org/)
[![Database](https://img.shields.io/badge/MySQL-8.0%20%7C%20MariaDB-4479A1?logo=mysql&logoColor=white)](https://www.mysql.com/)
[![License](https://img.shields.io/badge/License-Academic%20Project-blue.svg)](#)

---

## 👤 1. Identitas Mahasiswa & Kelompok

Laporan dan repositori proyek ini disusun untuk memenuhi persyaratan **Ujian Akhir Semester (UAS) Rekayasa Perangkat Lunak**:

- **Nama Mahasiswa** : Khaerul Umam
- **NIM** : 41257172
- **Nama Kelompok** : tiba tiba talun
- **Mata Kuliah** : Rekayasa Perangkat Lunak (RPL)
- **Instansi / Program Studi** : Teknik Informatika / Sistem Informasi

---

## 🎯 2. Tema & Topik Proyek

> **Judul Tema Proyek:**  
> **"Pembangunan Sistem Ujian Online Terintegrasi (SmartExam AI) dengan Pengawasan Dual Camera Berbasis AI Proctoring dan Asisten Cerdas Grounded Database untuk Menegakkan Integritas Akademik"**

---

## 📖 3. Deskripsi Aplikasi

### A. Penjelasan Aplikasi
**SmartExam AI (UJOL)** adalah platform asesmen daring (*Computer-Based Test / CBT*) modern yang dirancang khusus untuk jenjang Sekolah Menengah Atas (SMA/SMK/MA). Aplikasi ini dibangun menggunakan arsitektur **PHP Native MVC (Model-View-Controller)** berorientasi objek yang modular, dipadukan dengan **Microservice Node.js** untuk layanan pemrosesan kecerdasan buatan (*AI assistant*), serta basis data relasional **MySQL**.

### B. Tujuan Pembuatan Aplikasi
1. Menyediakan sistem ujian daring yang **adil, kredibel, dan transparan** bagi seluruh peserta didik.
2. Memfasilitasi guru dalam menyusun bank soal, mengelola jadwal ujian, memantau pengerjaan secara langsung (*real-time*), dan memperoleh rekapitulasi penilaian otomatis.
3. Memberikan kebebasan infrastruktur teknologi bagi pihak sekolah dalam menggunakan beragam penyedia model AI tanpa terikat pada satu vendor tertentu (*no vendor lock-in*).

### C. Permasalahan yang Diselesaikan
| Permasalahan Ujian Konvensional | Solusi Inovasi SmartExam AI |
| :--- | :--- |
| **Kecurangan Membuka Tab / Split Screen** | **AI Tab-Switch Guard:** Hitung mundur peringatan otomatis dan penguncian lembar ujian jika siswa meninggalkan tab pengerjaan. |
| **Titik Buta (*Blind Spot*) Kamera Tunggal Laptop** | **Dual Camera Proctoring:** Pengawasan 360° menggunakan kombinasi Webcam Laptop (wajah depan) + Kamera Smartphone samping melalui pemindaian QR Code (memantau meja dan tangan). |
| **Ketergantungan Satu Vendor AI / Biaya Mahal** | **Universal AI Studio:** Administrator bebas beralih ke provider Google Gemini, Anthropic Claude, OpenAI, DeepSeek, Groq, atau Custom API URL secara instan. |
| **Chatbot Sekolah Sering Halusinasi & Basa-Basi** | **Smarty AI Database Grounding:** Microservice Node.js membaca fakta mutlak dari database MySQL secara real-time disertai guardrail anti-bocor kunci ujian. |
| **Kehilangan Data Jawaban Saat Koneksi Putus** | **Local Autosave & Debounced Sync:** Lembar jawaban disimpan otomatis ke IndexedDB/LocalStorage sebelum disinkronkan ke server secara berkala. |

---

## ✨ 4. Fitur-Fitur Utama Aplikasi

Sistem SmartExam AI memiliki pembagian hak akses ketat berbasis **Role-Based Access Control (RBAC)** untuk 3 aktor utama:

### 1. Modul Super Admin (Administrator Utama)
- **Dasbor Statistik Real-Time:** 8 kartu metrik pemantauan data total guru, siswa, kelas, mata pelajaran, ujian aktif, dan status server.
- **Master Data Sekolah:** Pengelolaan penuh (CRUD, Pencarian Cepat, Paginasi) untuk data Guru, Siswa, Rombongan Belajar (Kelas), dan Mata Pelajaran.
- **Universal AI Studio (`/admin/settings?tab=ai`):** Konfigurasi integrasi API LLM dinamis, penyimpanan daftar provider kustom (*AI Provider List*), dan tombol aksi 1-klik aktifkan provider.
- **Audit Logs & Keamanan:** Pencatatan otomatis setiap aktivitas penting, termasuk percobaan pelanggaran akses lintas-role (HTTP 403 Forbidden).

### 2. Modul Guru (Tenaga Pendidik / Pengawas)
- **Manajemen Bank Soal:** Pembuatan soal pilihan ganda (bobot nilai, opsi acak A-E, kunci jawaban) dan soal esai.
- **Manajemen Ujian & Penjadwalan:** Pengaturan durasi pengerjaan, generator token acak, acak butir soal, acak pilihan jawaban, serta status publikasi.
- **Live Exam Monitoring Dashboard:** Pemantauan langsung peserta ujian melalui matriks video kamera, status durasi pengerjaan, dan progress butir soal.
- **Review Insiden & Pelanggaran:** Peninjauan snapshot bukti pelanggaran yang ditangkap oleh AI (wajah hilang, multi-wajah, tab-switch).
- **Rekapitulasi Nilai & Laporan:** Evaluasi otomatis ketuntasan nilai KKM dan ekspor rekap nilai ke format CSV/Excel dan cetak laporan.

### 3. Modul Peserta Didik (Siswa)
- **Autentikasi Aman & 2FA OTP:** Login terproteksi dengan verifikasi OTP melalui Email / WhatsApp Gateway.
- **Exam Workspace Bebas Distraksi:** Tampilan lembar ujian fokus dengan timer hitung mundur anti-manipulasi, navigasi status soal (Dijawab, Ragu-Ragu, Belum), dan autosave.
- **Dual Camera Stream HUD:** Indikator overlay status aktif kamera depan laptop dan kamera ponsel samping.

---

## 🤖 5. Fitur Kecerdasan Buatan (AI) & Cara Kerjanya

SmartExam AI mengintegrasikan 3 pilar teknologi kecerdasan buatan:

```
                                  [ SMARTEXAM AI ECOSYSTEM ]
                                               │
             ┌─────────────────────────────────┼─────────────────────────────────┐
             ▼                                 ▼                                 ▼
   [ AI Dual Camera Proctoring ]     [ Universal Multi-Provider AI ]   [ Smarty AI Grounded Chatbot ]
   • Kamera 1: Laptop (Wajah)        • Google Gemini (2.5 & 3.5)       • Microservice Node.js (Port 3001)
   • Kamera 2: Ponsel via QR         • Anthropic Claude 3.5 Sonnet     • Database Grounding (MySQL)
   • Deteksi Wajah & Anomali         • DeepSeek V3 / Groq / OpenAI     • Guardrail Anti-Bocor Soal
   • Auto Flag: proctoring_events    • Custom API Endpoint (UMAM)      • Jawaban Cepat & To-The-Point
```

### A. AI Dual Camera Proctoring
- **Fungsi:** Memantau kejujuran peserta ujian dari dua sudut pandang berbeda tanpa membutuhkan pengawas fisik di setiap meja.
- **Teknologi:** Computer Vision Edge Detection, MediaStream API, WebRTC / WebSocket, dan QR Code Device Pairing.
- **Cara Kerja:** Siswa membuka lembar ujian di laptop (Kamera 1 memantau wajah), lalu memindai kode QR dari smartphone (Kamera 2 memantau meja dan tangan). Sistem secara berkala mendeteksi anomali:
  1. `face_missing`: Wajah peserta meninggalkan area pandang kamera.
  2. `multi_face`: Terdeteksi lebih dari satu orang di depan kamera.
  3. `face_blocked`: Kamera sengaja ditutup atau pandangan terhalang.
  4. `tab_switch`: Siswa memindahkan fokus peramban atau membuka tab contekan.
  Snapshot bukti pelanggaran langsung dikirim ke server dan dicatat pada tabel `proctoring_events`.

### B. Universal Multi-Provider AI Studio
- **Fungsi:** Fleksibilitas sekolah dalam memilih penyedia Large Language Model (LLM) sesuai anggaran dan ketersediaan kuota tanpa merusak arsitektur aplikasi (*no vendor lock-in*).
- **Teknologi:** Universal API Connector yang kompatibel dengan protokol Google Generative AI dan OpenAI-Compatible Endpoints.
- **Cara Kerja:** Admin menginput Base URL, Model Name, dan API Key pada menu `/admin/settings?tab=ai`. Konfigurasi disimpan ke tabel `system_settings` pada database. Server microservice langsung memuat model yang aktif seketika tanpa perlu merestart proses daemon.

### C. Smarty AI Chatbot Assistant (Chatbot+)
- **Fungsi:** Asisten interaktif yang membantu pengguna (Admin, Guru, Siswa) mendapatkan informasi akademik secara instan.
- **Teknologi:** Microservice Node.js (Port 3001) menggunakan `mysql2/promise` dan REST API LLM.
- **Cara Kerja (Database Grounding & Security Guardrail):**
  1. Pengguna mengetik pertanyaan pada floating widget chat.
  2. Server mengevaluasi filter keamanan (*Guardrail*): jika siswa meminta kunci jawaban ujian, sistem langsung menolak secara otomatis demi integritas akademik.
  3. Jika pertanyaan berkaitan dengan data sekolah (contoh: *"Berapa jumlah siswa yang terdaftar?"*), server menjalankan query SQL langsung ke database `smartexam_db`.
  4. Hasil data angka riil dilampirkan sebagai fakta mutlak (*grounding*) pada prompt AI sehingga jawaban yang dihasilkan 100% akurat, singkat, padat, dan bebas halusinasi.

---

## 📹 6. Link Video YouTube Presentasi & Demo UAS

> 🎥 **Tautan Video YouTube:**  
> **[Link Video YouTube Presentasi & Demo UAS - Akan Ditambahkan oleh Mahasiswa]**  
> *(Silakan edit bagian ini dan tempelkan URL video presentasi YouTube Anda)*

---

## 💻 7. Panduan Instalasi & Menjalankan Proyek Secara Lokal

### A. Persyaratan Perangkat & Software
- **Sistem Operasi:** Windows 10/11, Linux (Ubuntu/Debian), atau macOS.
- **Web Server:** XAMPP (Apache) dengan **PHP versi 8.2 atau 8.3**.
- **Basis Data:** MySQL versi 8.0 atau MariaDB (Port bawaan `3306`).
- **Node.js:** Versi 18 LTS atau 20 LTS (untuk background service Chatbot).
- **Composer:** Versi 2.x (untuk manajemen dependensi PHP).
- **Peramban:** Google Chrome / Microsoft Edge / Mozilla Firefox (diperlukan izin akses webcam).

---

### B. Langkah Instalasi Bertahap

#### 1. Clone Repositori
Buka terminal (Git Bash / PowerShell) dan clone repositori ini ke folder `htdocs` XAMPP Anda:
```bash
cd C:\xampp\htdocs
git clone https://github.com/Khrl06/UJOL.git
cd UJOL
```

#### 2. Konfigurasi Environment (`.env`)
Salin berkas template `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Pastikan pengaturan database di dalam berkas `.env` sesuai dengan konfigurasi XAMPP lokal Anda:
```env
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=smartexam_db
DB_USERNAME=root
DB_PASSWORD=
```

#### 3. Konfigurasi Basis Data (MySQL)
1. Buka aplikasi **XAMPP Control Panel**, lalu klik tombol **Start** pada modul **Apache** dan **MySQL**.
2. Buka peramban web dan akses: `http://localhost/phpmyadmin`
3. Buat database baru dengan nama: **`smartexam_db`**
4. Pilih database `smartexam_db`, klik tab **Import**, pilih berkas **`database/schema.sql`** yang ada di dalam proyek, lalu klik **Go / Import**.

#### 4. Instalasi Dependensi PHP & Node.js
Jalankan perintah berikut di terminal:
```bash
# Instal dependensi PHP (PHPUnit untuk Unit Testing)
composer install

# Instal dependensi Microservice Chatbot Node.js
cd chatbot-server
npm install
cd ..
```

#### 5. Menjalankan Chatbot Server (Opsional untuk Fitur AI Chatbot)
Buka terminal baru di direktori `chatbot-server`:
```bash
cd C:\xampp\htdocs\UJOL\chatbot-server
node server.js
```
*Server chatbot akan aktif di `http://localhost:3001` dan otomatis terhubung ke MySQL `smartexam_db`.*

---

### C. Cara Mengakses Aplikasi
Buka peramban web dan akses alamat berikut:
👉 **`http://localhost/UJOL/public`**

#### 🔑 Akun Demo Bawaan Sistem (Seeder):
| Peran (Role) | Username | Kata Sandi | Hak Akses Modul |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin` | `admin123` | Akses penuh seluruh master data & konfigurasi AI |
| **Guru** | `guru_matematika` | `guru123` | Bank soal, jadwal ujian, dan live monitoring |
| **Peserta Didik** | `siswa_rpl1` | `siswa123` | Mengikuti ujian, dual camera stream, dan hasil |

---

## 🧪 8. Pengujian Unit Testing (CI/CD Quality Assurance)

Proyek ini telah dilengkapi dengan pengujian otomatis (*Automated Unit Testing*) yang terintegrasi pada **GitHub Actions** (`.github/workflows/ci.yml`).

Untuk menjalankan pengujian secara lokal, jalankan perintah berikut di terminal:
```bash
# Opsi 1: Runner Mandiri Berwarna (Sangat Cepat & Tanpa Setup Eksternal)
php tests/run_tests.php

# Opsi 2: Menggunakan PHPUnit 11 Resmi
vendor/bin/phpunit --testdox

# Opsi 3: Menggunakan Composer Script
composer test
```
*Hasil Pengujian: 18 unit tests lolos 100% (Green Pipeline).*

---

## 🎨 9. Dokumentasi & Prototipe Interaktif
- **Dokumen Laporan PRD & UML Resmi:** [PRD_UML_SmartExamAI_Presentation.md](PRD_UML_SmartExamAI_Presentation.md)
- **Dokumen Laporan Word (.docx):** [Laporan_PRD_UML_SmartExamAI_Terbaru.docx](Laporan_PRD_UML_SmartExamAI_Terbaru.docx)
- **Simulator Prototipe Figma Interaktif:** Buka file `dokumentasi_uml_dan_prototipe/prototipe_figma_interaktif.html` di peramban web atau akses:
  `http://localhost/UJOL/dokumentasi_uml_dan_prototipe/prototipe_figma_interaktif.html`

---

*Repositori ini dikembangkan dan dipelihara secara resmi untuk pemenuhan tugas akademik UAS Rekayasa Perangkat Lunak 2026/2027.*
