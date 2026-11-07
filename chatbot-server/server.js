/**
 * SmartExam AI (UJOL) - Intelligent Chatbot Server (Chatbot+)
 * Terintegrasi langsung dengan MySQL smartexam_db & Powered by Google Gemini Generative Language API
 * Dilengkapi Database Grounding, Anti-Cheating Guardrails, & Role-Based Access Control
 */

const http = require("http");
const { testDbConnection, pool } = require("./db");
const { resolveDatabaseContext } = require("./dbQueries");

const PORT = process.env.PORT || 3001;
const GEMINI_API_KEY = process.env.GEMINI_API_KEY || "AQ.Ab8RN6IdqRznTT2HZKQfBXfHKGj7m_V3pDiKmCjeOKk58LELfA";

// Daftar model prioritas dengan fallback otomatis
const PREFERRED_MODELS = [
  "gemini-3.5-flash-lite",
  "gemini-3.1-flash-lite",
  "gemini-3.6-flash",
  "gemini-3.5-flash",
  "gemini-flash-lite-latest",
  "gemini-3.8-flash"
];

// Status koneksi database runtime
let dbStatus = { connected: false, message: "Initializing..." };

// Instruksi Sistem Dasar (Konteks Domain UJOL / SmartExam AI dengan Integrasi Database Penuh)
const BASE_SYSTEM_INSTRUCTION = `Anda adalah "Smarty AI", asisten pintar resmi untuk aplikasi UJOL (SmartExam AI - Sistem Ujian Online Terintegrasi SMA/SMK/MA).

ATURAN UTAMA GAYA JAWABAN (WAJIB DIIKUTI):
1. JAWAB LANGSUNG, SINGKAT, PADAT, DAN TO-THE-POINT (DILARANG BERCERITA / BERTELE-TELE):
   - JANGAN membuat cerita panjang, jangan memberi kalimat pembuka basa-basi atau dongeng berlebihan.
   - LANGSUNG jawab pertanyaan yang sedang diajukan seputar website UJOL dan data yang ada di dataset/database.

2. ATURAN JIKA DITANYA "ADA BERAPA" / "BERAPA JUMLAH":
   - Jawab LANGSUNG JUMLAH / ANGKA-nya saja (Contoh: "Jumlah siswa ada 3 orang.", "Jumlah guru ada 2 orang.").
   - Jangan menyebutkan seluruh biodata atau riwayat jika pengguna hanya menanyakan jumlah/ada berapa.

3. ATURAN JIKA DITANYA "SAYA SIAPA" / "SIAPA SAYA" / "SAYA LOGIN SEBAGAI APA":
   - Jawab LANGSUNG nama dan peran akun yang digunakan untuk login (Contoh: "Anda login sebagai Ahmad Fauzi Pratama (Akun: Siswa).").
   - Jawab namanya saja dan peran akunnya, JANGAN bercerita panjang lebar.
   - Jika pengguna belum login (Guest/Tamu): Jawab "Anda belum login (Tamu / Guest)."

4. FOKUS DATASET & FITUR WEBSITE UJOL:
   - Jawab hanya pertanyaan yang berkaitan dengan data dan fitur sistem SmartExam AI / UJOL (Siswa, Guru, Kelas, Mapel, Ujian, Nilai, Jadwal, Bank Soal).
   - Selalu berpedoman pada dataset resmi yang dilampirkan sebagai sumber fakta mutlak.

TUGAS UTAMA & INTEGRASI BASIS DATA (DATABASE):
1. ANDA TERHUBUNG LANGSUNG KE DATABASE SISTEM UJOL (smartexam_db).
2. Data Siswa, Guru, Mapel, Ujian, Kelas, dan Nilai terhubung secara dinamis.

BATASAN & GUARDRAIL PENTING:
1. DILARANG KERAS MENULIS KODE / PROGRAMMING: Tolak jika diminta membuat kode program.
2. DILARANG MEMBANTU KECURANGAN UJIAN: Tolak tegas permintaan kunci jawaban atau pembocoran butir soal.
3. PRIVASI: Jangan bocorkan nomor telepon atau alamat siswa ke siswa lain.
4. DILARANG MENJAWAB DI LUAR EKOSISTEM UJOL / SEKOLAH.`;

/**
 * Ambil konfigurasi AI dinamis langsung dari tabel system_settings di basis data
 */
async function getAiConfig() {
  try {
    const [rows] = await pool.query(
      "SELECT setting_key, setting_value FROM system_settings WHERE setting_group = 'ai'"
    );
    const config = {};
    for (const r of rows) {
      config[r.setting_key] = r.setting_value;
    }
    return {
      provider: (config.ai_provider || "gemini").toLowerCase().trim(),
      modelName: (config.ai_model_name || "").trim(),
      apiKey: (config.ai_api_key || "").trim() || GEMINI_API_KEY,
      baseUrl: (config.ai_base_url || "").trim()
    };
  } catch (err) {
    console.warn("[AI Config] Gagal membaca konfigurasi AI dari DB:", err.message);
    return {
      provider: "gemini",
      modelName: "gemini-1.5-flash",
      apiKey: GEMINI_API_KEY,
      baseUrl: ""
    };
  }
}

/**
 * Engine pemanggil Google Gemini
 */
async function callGeminiEngine(contents, systemInstruction, preferredModel, customKey) {
  const apiKey = customKey || GEMINI_API_KEY;
  const modelsToTry = preferredModel
    ? [preferredModel, ...PREFERRED_MODELS.filter(m => m !== preferredModel)]
    : PREFERRED_MODELS;

  let lastError = null;
  for (const model of modelsToTry) {
    try {
      const url = `https://generativelanguage.googleapis.com/v1beta/models/${model}:generateContent?key=${apiKey}`;
      const res = await fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          systemInstruction: {
            parts: [{ text: systemInstruction || BASE_SYSTEM_INSTRUCTION }]
          },
          contents: contents,
          generationConfig: {
            temperature: 0.6,
            maxOutputTokens: 1200
          }
        }),
        signal: AbortSignal.timeout(15000)
      });

      const raw = await res.text();
      let data = {};
      try { data = JSON.parse(raw); } catch (_) { }

      if (res.ok && data.candidates && data.candidates.length > 0) {
        const replyText = data.candidates[0]?.content?.parts?.[0]?.text;
        if (replyText) {
          return { text: replyText, model: model, provider: "Google Gemini" };
        }
      }
      lastError = new Error(data.error?.message || `HTTP ${res.status}`);
    } catch (err) {
      lastError = err;
    }
  }
  throw lastError || new Error("Semua model Gemini tidak dapat merespons saat ini.");
}

/**
 * Panggil Universal AI Provider (Google Gemini, OpenAI, DeepSeek, Groq, OpenRouter, Claude, Mistral, Together, Ollama, atau Custom API URL)
 */
async function callUniversalAi(contents, systemInstruction) {
  const config = await getAiConfig();
  const provider = config.provider || "gemini";
  const apiKey = config.apiKey;

  // 1. Google Gemini Native API
  if (provider === "gemini") {
    return await callGeminiEngine(contents, systemInstruction, config.modelName, apiKey);
  }

  // 2. Anthropic Claude Native Messages API
  if (provider === "claude") {
    try {
      const endpoint = config.baseUrl || "https://api.anthropic.com/v1/messages";
      const model = config.modelName || "claude-3-5-sonnet-20241022";

      const claudeMessages = [];
      for (const item of contents) {
        const role = item.role === "model" ? "assistant" : "user";
        const text = item.parts?.[0]?.text || "";
        if (text) {
          claudeMessages.push({ role, content: text });
        }
      }

      const res = await fetch(endpoint, {
        method: "POST",
        headers: {
          "x-api-key": apiKey,
          "anthropic-version": "2023-06-01",
          "content-type": "application/json"
        },
        body: JSON.stringify({
          model: model,
          max_tokens: 1200,
          system: systemInstruction || BASE_SYSTEM_INSTRUCTION,
          messages: claudeMessages.length > 0 ? claudeMessages : [{ role: "user", content: "Halo" }]
        }),
        signal: AbortSignal.timeout(20000)
      });

      const raw = await res.text();
      let data = {};
      try { data = JSON.parse(raw); } catch (_) { }

      if (res.ok && data.content?.[0]?.text) {
        return { text: data.content[0].text, model: model, provider: "Anthropic Claude" };
      }
      throw new Error(data.error?.message || `Claude HTTP ${res.status}`);
    } catch (claudeErr) {
      console.warn(`[Claude Fallback]: ${claudeErr.message}. Beralih ke Google Gemini...`);
      return await callGeminiEngine(contents, systemInstruction);
    }
  }

  // 3. Standar OpenAI-Compatible API (OpenAI, DeepSeek, Groq, OpenRouter, Mistral, Together, Ollama, Custom URL)
  try {
    let endpoint = config.baseUrl;
    if (!endpoint) {
      if (provider === "nvidia") endpoint = "https://integrate.api.nvidia.com/v1/chat/completions";
      else if (provider === "deepseek") endpoint = "https://api.deepseek.com/v1/chat/completions";
      else if (provider === "groq") endpoint = "https://api.groq.com/openai/v1/chat/completions";
      else if (provider === "openrouter") endpoint = "https://openrouter.ai/api/v1/chat/completions";
      else if (provider === "mistral") endpoint = "https://api.mistral.ai/v1/chat/completions";
      else if (provider === "together") endpoint = "https://api.together.xyz/v1/chat/completions";
      else if (provider === "perplexity") endpoint = "https://api.perplexity.ai/chat/completions";
      else if (provider === "xai") endpoint = "https://api.x.ai/v1/chat/completions";
      else if (provider === "ollama" || provider === "local") endpoint = "http://localhost:11434/v1/chat/completions";
      else if (provider === "lmstudio") endpoint = "http://localhost:1234/v1/chat/completions";
      else endpoint = "https://api.openai.com/v1/chat/completions";
    } else {
      endpoint = endpoint.replace(/\/+$/, "");
      if (!endpoint.endsWith("/chat/completions")) {
        endpoint += "/chat/completions";
      }
    }

    const model = config.modelName || (
      provider === "nvidia" ? "nvidia/nemotron-3.5-bolt-e4b" :
        provider === "deepseek" ? "deepseek-chat" :
          provider === "groq" ? "llama-3.3-70b-versatile" :
            provider === "openrouter" ? "deepseek/deepseek-chat" :
              provider === "mistral" ? "mistral-large-latest" :
                provider === "together" ? "meta-llama/Llama-3-70b-chat-hf" :
                  provider === "perplexity" ? "sonar" :
                    provider === "xai" ? "grok-2-latest" :
                      provider === "ollama" || provider === "local" ? "llama3" :
                        provider === "lmstudio" ? "local-model" :
                          "gpt-4o-mini"
    );

    const openAiMessages = [
      { role: "system", content: systemInstruction || BASE_SYSTEM_INSTRUCTION }
    ];
    for (const item of contents) {
      const role = item.role === "model" ? "assistant" : "user";
      const text = item.parts?.[0]?.text || "";
      if (text) {
        openAiMessages.push({ role, content: text });
      }
    }

    const headers = {
      "Content-Type": "application/json"
    };
    if (apiKey) {
      headers["Authorization"] = `Bearer ${apiKey}`;
    }
    if (provider === "openrouter") {
      headers["HTTP-Referer"] = "http://localhost/UJOL";
      headers["X-Title"] = "SmartExam AI";
    }

    const res = await fetch(endpoint, {
      method: "POST",
      headers: headers,
      body: JSON.stringify({
        model: model,
        messages: openAiMessages,
        temperature: 0.6,
        max_tokens: 1200
      }),
      signal: AbortSignal.timeout(20000)
    });

    const rawText = await res.text();
    let data = {};
    try {
      data = JSON.parse(rawText);
    } catch (_) {
      throw new Error(`AI Provider (${provider}) HTTP ${res.status}: ${rawText.slice(0, 100)}`);
    }

    if (res.ok && data.choices?.[0]?.message?.content) {
      return { text: data.choices[0].message.content, model: model, provider: provider };
    }

    throw new Error(data.error?.message || `AI Provider (${provider}) HTTP ${res.status}`);
  } catch (customErr) {
    console.warn(`[AI Engine (${provider}) Fallback]: ${customErr.message}. Beralih ke Google Gemini...`);
    try {
      return await callGeminiEngine(contents, systemInstruction);
    } catch (geminiErr) {
      throw new Error(`${customErr.message} (Fallback Gemini: ${geminiErr.message})`);
    }
  }
}

const OUT_OF_CONTEXT_REPLY = `Maaf, saya adalah **Smarty AI**, asisten khusus aplikasi **UJOL (SmartExam AI)**.

Saya tidak dapat membuatkan kode pemrograman atau menjawab pertanyaan di luar sistem ujian UJOL.

Silakan tanyakan hal-hal seputar:
- 📋 Panduan & Jadwal Ujian Siswa
- 📹 Uji Perangkat Dual Camera & Token
- 📝 Pembuatan Soal & Pengawasan Guru
- ⚙️ Solusi Kendala Teknis Aplikasi UJOL`;

const ANTI_CHEATING_REPLY = `⚠️ **Peringatan Integritas Ujian**:
Sebagai asisten resmi **SmartExam AI (UJOL)**, saya **dilarang keras** membagikan kunci jawaban, membocorkan butir soal, atau membantu kecurangan dalam ujian bentuk apapun.

Silakan pelajari materi silabus mata pelajaran dan ikuti ujian dengan jujur, mandiri, dan sportif.`;

// Pre-filter Guardrail: Deteksi langsung permintaan coding / anti-cheating
function checkPreGuardrail(message) {
  const lower = (message || "").toLowerCase().trim();

  // 1. Anti-Cheating Guardrail
  const cheatingTriggers = [
    /\b(kunci\s*jawaban|bocoran\s*soal|bocoran\s*jawaban|jawaban\s*soal|jawaban\s*ujian|kunci\s*soal)\b/i,
    /\b(kunci\s*(pas|pat|uts|uas|kuis|asesmen))\b/i,
    /\b(jawaban\s*nomor\s*\d+|soal\s*nomor\s*\d+\s*(apa|berikan|kunci|jawabannya))\b/i,
    /\b(apa\s*kunci|berikan\s*kunci|bagi\s*kunci)\b/i
  ];
  for (const regex of cheatingTriggers) {
    if (regex.test(lower)) {
      return ANTI_CHEATING_REPLY;
    }
  }

  // 2. Anti-Coding Guardrail
  const codingTriggers = [
    /\b(buatkan|bikin|tuliskan|tulis|buat|generate|berikan)\s+(coding|kode|skrip|script|program|source\s*code)\b/i,
    /\b(coding|kode|code|skrip|script)\s+(python|php|javascript|js|java|c\+\+|cpp|c\#|html|css|sql|ruby|go|rust|bash|pascal)\b/i,
    /\b(python|javascript|php|java|c\+\+|cpp)\s+(script|code|kode|program)\b/i,
    /\b(ajarkan|belajar|tutorial)\s+(coding|pemrograman|codingan)\b/i
  ];
  for (const regex of codingTriggers) {
    if (regex.test(lower)) {
      return OUT_OF_CONTEXT_REPLY;
    }
  }

  return null;
}

// Post-filter Guardrail: Mencegah keluaran kode dari model AI
function sanitizeAiResponse(replyText) {
  if (!replyText) return replyText;
  const codeBlockRegex = /```(python|php|javascript|js|java|cpp|c\+\+|csharp|c#|html|css|bash|sh|sql|ruby|go|rust)/i;
  if (codeBlockRegex.test(replyText)) {
    console.warn("[Guardrail] Terdeteksi respon AI memuat blok kode. Menolak otomatis.");
    return OUT_OF_CONTEXT_REPLY;
  }
  return replyText;
}

/**
 * Format fallback respons jika AI Gemini gagal namun database memiliki data relevan
 */
function buildDirectDbReply(dbContext) {
  if (!dbContext || !dbContext.hasData) return null;

  if (dbContext.intent === "user_identity" || dbContext.intent === "count_summary" || dbContext.intent === "anti_cheating_block" || dbContext.intent === "access_denied" || dbContext.intent === "not_found") {
    return dbContext.contextText;
  }
  if (dbContext.intent === "exam_participation_status") {
    return `Berikut adalah status pengerjaan ujian siswa dari database **UJOL**:\n\n${dbContext.contextText}`;
  }
  if (dbContext.intent === "students_list") {
    return `Berikut adalah daftar siswa resmi yang terdaftar di database **UJOL**:\n\n${dbContext.contextText}`;
  }
  if (dbContext.intent === "teachers_list") {
    return `Berikut adalah daftar guru dan tenaga pengajar resmi di sistem **UJOL**:\n\n${dbContext.contextText}`;
  }
  if (dbContext.intent === "subjects_list") {
    return `Berikut adalah daftar mata pelajaran dan pengampu di sistem **UJOL**:\n\n${dbContext.contextText}`;
  }
  if (dbContext.intent === "exam_schedule" || dbContext.intent === "exam_search") {
    return `Berikut adalah informasi paket dan jadwal ujian resmi dari database **UJOL**:\n\n${dbContext.contextText}\n\n*Catatan: Anda dapat mengakses ruang ujian melalui menu Jadwal di dasbor sesuai waktu yang ditentukan.*`;
  }
  if (dbContext.intent === "student_score") {
    return `Berikut adalah riwayat nilai ujian yang terekam di sistem **UJOL**:\n\n${dbContext.contextText}\n\n*Catatan: Nilai resmi telah disahkan oleh guru pengampu dan sistem penilaian otomatis.*`;
  }
  if (dbContext.intent === "classes_list") {
    return `Berikut adalah informasi kelas terdaftar di sistem **UJOL**:\n\n${dbContext.contextText}`;
  }
  if (dbContext.intent === "question_banks") {
    return `Berikut adalah informasi bank soal di sistem **UJOL**:\n\n${dbContext.contextText}`;
  }
  if (dbContext.intent === "db_error") {
    return dbContext.contextText;
  }
  if (dbContext.intent === "comprehensive_overview" || dbContext.intent === "system_stats" || dbContext.intent === "general_with_master_context") {
    return `Berikut adalah ringkasan data resmi dari database **UJOL**:\n\n${dbContext.contextText}`;
  }
  return null;
}

/**
 * HTTP Server
 */
const server = http.createServer(async (req, res) => {
  // CORS Headers
  res.setHeader("Access-Control-Allow-Origin", "*");
  res.setHeader("Access-Control-Allow-Methods", "GET, POST, OPTIONS");
  res.setHeader("Access-Control-Allow-Headers", "Content-Type");

  if (req.method === "OPTIONS") {
    res.writeHead(204);
    res.end();
    return;
  }

  const url = new URL(req.url, `http://${req.headers.host || "localhost"}`);

  // Health check endpoint (termasuk status database dan konfigurasi AI aktif)
  if (req.method === "GET" && (url.pathname === "/" || url.pathname === "/api/health")) {
    const currentDbCheck = await testDbConnection();
    dbStatus = currentDbCheck;
    const aiConfig = await getAiConfig();

    res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
    res.end(JSON.stringify({
      status: "ok",
      service: "SmartExam AI (UJOL) Universal Chatbot+ Server",
      database: dbStatus.connected ? "connected" : "disconnected",
      database_details: dbStatus.message,
      ai_provider: aiConfig.provider,
      ai_model: aiConfig.modelName || "default",
      ai_base_url: aiConfig.baseUrl || "(default official)",
      timestamp: new Date().toISOString()
    }));
    return;
  }

  // Get AI Provider & Model Configuration (presets + current DB state)
  if (req.method === "GET" && url.pathname === "/api/model-config") {
    try {
      const aiConfig = await getAiConfig();
      let maskedKey = "";
      if (aiConfig.apiKey) {
        if (aiConfig.apiKey.length > 8) {
          maskedKey = aiConfig.apiKey.slice(0, 4) + "••••••••" + aiConfig.apiKey.slice(-4);
        } else {
          maskedKey = "••••••••";
        }
      }

      const presets = [
        {
          id: "custom",
          name: "Nvidia NIM (Open Models)",
          badge: "Nvidia AI",
          baseUrl: "https://integrate.api.nvidia.com/v1",
          defaultModel: "nvidia/nemotron-3.5-bolt-e4b",
          models: [
            { id: "nvidia/nemotron-3.5-bolt-e4b", name: "Nvidia Nemotron 3.5 Bolt E4B" },
            { id: "meta/llama-3.1-70b-instruct", name: "Llama 3.1 70B Instruct" },
            { id: "mistralai/mistral-large-2-instruct", name: "Mistral Large 2 Instruct" },
            { id: "meta/llama-3.1-8b-instruct", name: "Llama 3.1 8B Instruct" }
          ]
        },
        {
          id: "gemini",
          name: "Google Gemini",
          badge: "Google AI",
          baseUrl: "",
          defaultModel: "gemini-2.5-flash",
          models: [
            { id: "gemini-2.5-flash", name: "Gemini 2.5 Flash (Sangat Cepat & Pintar)" },
            { id: "gemini-1.5-flash", name: "Gemini 1.5 Flash (Stabil & Ringan)" },
            { id: "gemini-1.5-pro", name: "Gemini 1.5 Pro (Penalaran Kompleks)" },
            { id: "gemini-flash-lite-latest", name: "Gemini Flash Lite (Hemat Kuota)" }
          ]
        },
        {
          id: "openai",
          name: "OpenAI ChatGPT",
          badge: "OpenAI",
          baseUrl: "https://api.openai.com/v1",
          defaultModel: "gpt-4o-mini",
          models: [
            { id: "gpt-4o-mini", name: "GPT-4o Mini (Hemat & Cepat)" },
            { id: "gpt-4o", name: "GPT-4o (Model Flagship)" },
            { id: "gpt-3.5-turbo", name: "GPT-3.5 Turbo" }
          ]
        },
        {
          id: "groq",
          name: "Groq LPU",
          badge: "Groq Ultra Fast",
          baseUrl: "https://api.groq.com/openai/v1",
          defaultModel: "llama-3.3-70b-versatile",
          models: [
            { id: "llama-3.3-70b-versatile", name: "Llama 3.3 70B Versatile" },
            { id: "llama-3.1-8b-instant", name: "Llama 3.1 8B Instant" },
            { id: "mixtral-8x7b-32768", name: "Mixtral 8x7B" }
          ]
        },
        {
          id: "deepseek",
          name: "DeepSeek AI",
          badge: "DeepSeek",
          baseUrl: "https://api.deepseek.com/v1",
          defaultModel: "deepseek-chat",
          models: [
            { id: "deepseek-chat", name: "DeepSeek Chat (V3)" },
            { id: "deepseek-reasoner", name: "DeepSeek Reasoner (R1)" }
          ]
        },
        {
          id: "claude",
          name: "Anthropic Claude",
          badge: "Claude",
          baseUrl: "https://api.anthropic.com/v1",
          defaultModel: "claude-3-5-sonnet-20241022",
          models: [
            { id: "claude-3-5-sonnet-20241022", name: "Claude 3.5 Sonnet" },
            { id: "claude-3-5-haiku-20241022", name: "Claude 3.5 Haiku" }
          ]
        },
        {
          id: "local",
          name: "Local LLM / Ollama (Offline)",
          badge: "Local",
          baseUrl: "http://localhost:11434/v1",
          defaultModel: "llama3",
          models: [
            { id: "llama3", name: "Ollama: Llama 3" },
            { id: "mistral", name: "Ollama: Mistral" },
            { id: "qwen2.5", name: "Ollama: Qwen 2.5" },
            { id: "custom", name: "Model Slug Kustom..." }
          ]
        }
      ];

      res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
      res.end(JSON.stringify({
        success: true,
        current: {
          provider: aiConfig.provider,
          modelName: aiConfig.modelName,
          baseUrl: aiConfig.baseUrl,
          hasApiKey: Boolean(aiConfig.apiKey),
          maskedKey: maskedKey
        },
        presets: presets
      }));
    } catch (err) {
      console.error("[Get AI Config Error]:", err.message);
      res.writeHead(500, { "Content-Type": "application/json; charset=utf-8" });
      res.end(JSON.stringify({ success: false, error: err.message }));
    }
    return;
  }

  // Update AI Provider & Model Configuration
  if (req.method === "POST" && url.pathname === "/api/model-config") {
    let body = "";
    req.on("data", chunk => { body += chunk; });
    req.on("end", async () => {
      try {
        const payload = JSON.parse(body || "{}");
        const provider = (payload.provider || "gemini").toLowerCase().trim();
        const modelName = (payload.modelName || "").trim();
        const baseUrl = (payload.baseUrl || "").trim();
        const apiKey = (payload.apiKey || "").trim();

        if (!modelName) {
          res.writeHead(400, { "Content-Type": "application/json; charset=utf-8" });
          res.end(JSON.stringify({ success: false, error: "Nama model tidak boleh kosong." }));
          return;
        }

        // Simpan ke tabel system_settings di MySQL
        await pool.query(
          "UPDATE system_settings SET setting_value = ? WHERE setting_key = 'ai_provider'",
          [provider]
        );
        await pool.query(
          "UPDATE system_settings SET setting_value = ? WHERE setting_key = 'ai_model_name'",
          [modelName]
        );
        if (baseUrl !== undefined) {
          await pool.query(
            "UPDATE system_settings SET setting_value = ? WHERE setting_key = 'ai_base_url'",
            [baseUrl]
          );
        }
        if (apiKey && !apiKey.includes("••••")) {
          await pool.query(
            "UPDATE system_settings SET setting_value = ? WHERE setting_key = 'ai_api_key'",
            [apiKey]
          );
        }

        console.log(`[AI Config Diperbarui]: Provider=${provider}, Model=${modelName}, BaseUrl=${baseUrl || '(default)'}`);

        res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
        res.end(JSON.stringify({
          success: true,
          message: `Model AI berhasil diubah ke ${modelName}`,
          provider: provider,
          modelName: modelName,
          baseUrl: baseUrl
        }));
      } catch (err) {
        console.error("[Update AI Config Error]:", err.message);
        res.writeHead(500, { "Content-Type": "application/json; charset=utf-8" });
        res.end(JSON.stringify({ success: false, error: err.message }));
      }
    });
    return;
  }

  // Chat endpoint
  if (req.method === "POST" && url.pathname === "/api/chat") {
    let body = "";
    req.on("data", chunk => {
      body += chunk;
      if (body.length > 1048576) {
        req.destroy();
      }
    });

    req.on("end", async () => {
      try {
        const payload = JSON.parse(body || "{}");
        const userMessage = (payload.message || "").trim();
        const userContext = payload.user || null; // { id, name, role }

        if (!userMessage) {
          res.writeHead(400, { "Content-Type": "application/json; charset=utf-8" });
          res.end(JSON.stringify({ success: false, error: "Pesan tidak boleh kosong." }));
          return;
        }

        console.log(`[Chat] Pesan diterima: "${userMessage.slice(0, 60)}..." (User: ${userContext?.name || 'Guest'}, Role: ${userContext?.role || 'siswa'})`);

        // 1. Jalankan Pre-guardrail (Anti-cheating & Coding block)
        const guardrailBlock = checkPreGuardrail(userMessage);
        if (guardrailBlock) {
          console.log(`[Guardrail Intercept]: Memblokir pesan terlarang.`);
          res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
          res.end(JSON.stringify({
            success: true,
            reply: guardrailBlock,
            model: "guardrail-shield",
            intent: "guardrail_block",
            db_grounded: false,
            responseTimeMs: 5
          }));
          return;
        }

        // 2. Query Database Intent Resolver (Database Grounding & RBAC)
        const startTime = Date.now();
        let dbContext = { hasData: false, contextText: "", intent: "general" };

        try {
          dbContext = await resolveDatabaseContext(userMessage, userContext);
          if (dbContext.hasData) {
            console.log(`[DB Context Resolved] Intent: ${dbContext.intent}`);
          }
        } catch (dbErr) {
          console.error(`[DB Query Error]:`, dbErr.message);
          dbContext = {
            hasData: true,
            intent: "db_error",
            contextText: `⚠️ **Gangguan Koneksi Basis Data**: Terjadi kendala saat membaca data dari basis data UJOL (${dbErr.message}). Mohon maaf atas ketidaknyamanan ini.`
          };
        }

        // 3. Jika intent adalah direct response (user_identity, count_summary, anti_cheating, access_denied)
        // Kembalikan langsung secara to-the-point tanpa menunggu pemanggilan model AI eksternal
        if (dbContext.directReply || dbContext.intent === "user_identity" || dbContext.intent === "count_summary" || dbContext.intent === "anti_cheating_block" || dbContext.intent === "access_denied") {
          const duration = Date.now() - startTime;
          res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
          res.end(JSON.stringify({
            success: true,
            reply: dbContext.contextText,
            model: "direct-dataset-resolver",
            intent: dbContext.intent,
            db_grounded: true,
            responseTimeMs: duration
          }));
          return;
        }

        // 4. Susun instruksi dinamis dengan konteks database aktual
        let dynamicSystemInstruction = BASE_SYSTEM_INSTRUCTION;
        if (dbContext.hasData && dbContext.contextText) {
          dynamicSystemInstruction += `\n\n======================================================
[INFORMASI RESMI & AKURAT DARI DATABASE SISTEM UJOL SAAT INI]:
${dbContext.contextText}
======================================================
PENTING: Gunakan fakta dari database resmi di atas sebagai dasar utama jawaban Anda kepada pengguna. Jawab langsung secara padat, to-the-point, dan dilarang bercerita panjang lebar.`;
        }

        if (userContext && userContext.name) {
          const roleLabel = userContext.role === 'admin' ? 'Admin' : (userContext.role === 'guru' ? 'Guru' : 'Siswa');
          dynamicSystemInstruction += `\nKonteks Pengguna Saat Ini: Nama: "${userContext.name}", Akun: "${roleLabel}", User ID: ${userContext.id}. Jika pengguna menanyakan siapa dirinya atau login pakai akun apa, jawab namanya saja: Anda login sebagai ${userContext.name} (Akun: ${roleLabel}). JANGAN bercerita panjang.`;
        }

        // 5. Bangun riwayat percakapan untuk Gemini
        const contents = [];
        if (Array.isArray(payload.history)) {
          for (const item of payload.history.slice(-6)) {
            const role = item.role === "assistant" || item.role === "model" ? "model" : "user";
            const text = (item.text || item.content || "").trim();
            if (text) {
              contents.push({ role, parts: [{ text }] });
            }
          }
        }

        // Tambahkan pesan terkini
        contents.push({
          role: "user",
          parts: [{ text: userMessage }]
        });

        // 6. Panggil Universal AI Engine (dengan fallback respons direct DB jika API AI tidak tersedia)
        let reply = "";
        let usedModel = "unknown";

        try {
          const result = await callUniversalAi(contents, dynamicSystemInstruction);
          reply = sanitizeAiResponse(result.text);
          usedModel = result.model;
        } catch (aiErr) {
          console.warn(`[AI Engine Fallback]: Provider AI gagal merespons (${aiErr.message}). Menggunakan direct DB response...`);

          const directDbReply = buildDirectDbReply(dbContext);
          if (directDbReply) {
            reply = directDbReply;
            usedModel = "database-direct-fallback";
          } else {
            throw aiErr;
          }
        }

        const duration = Date.now() - startTime;
        console.log(`[Chat] Selesai dalam ${duration}ms (model: ${usedModel}, intent: ${dbContext.intent})`);

        res.writeHead(200, { "Content-Type": "application/json; charset=utf-8" });
        res.end(JSON.stringify({
          success: true,
          reply: reply,
          model: usedModel,
          intent: dbContext.intent,
          db_grounded: dbContext.hasData,
          responseTimeMs: duration
        }));

      } catch (err) {
        console.error("[Chat Error]:", err.message);
        res.writeHead(500, { "Content-Type": "application/json; charset=utf-8" });
        res.end(JSON.stringify({
          success: false,
          error: "Maaf, terjadi gangguan saat menghubungi asisten AI: " + err.message
        }));
      }
    });

    return;
  }

  // Not Found
  res.writeHead(404, { "Content-Type": "application/json; charset=utf-8" });
  res.end(JSON.stringify({ error: "Endpoint tidak ditemukan." }));
});

// Jalankan inisialisasi koneksi DB dan listen server
testDbConnection().then(status => {
  dbStatus = status;
  server.listen(PORT, "0.0.0.0", () => {
    console.log(`=================================================`);
    console.log(`SmartExam AI (UJOL) Chatbot+ Server Berjalan!`);
    console.log(`Port: ${PORT}`);
    console.log(`Database: ${dbStatus.connected ? 'ONLINE (smartexam_db)' : 'OFFLINE - ' + dbStatus.message}`);
    console.log(`Health Check: http://localhost:${PORT}/api/health`);
    console.log(`Chat Endpoint: POST http://localhost:${PORT}/api/chat`);
    console.log(`=================================================`);
  });
}).catch(err => {
  console.error("Gagal start server:", err);
});
