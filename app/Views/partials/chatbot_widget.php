<?php
use App\Helpers\AuthMiddleware;
$chatUserContext = null;
if (class_exists('App\Helpers\AuthMiddleware') && AuthMiddleware::isLoggedIn()) {
    $chatUserData = AuthMiddleware::user();
    if ($chatUserData) {
        $chatUserContext = [
            'id' => (int)($chatUserData['id'] ?? 0),
            'name' => (string)($chatUserData['name'] ?? ''),
            'role' => (string)($chatUserData['role'] ?? 'siswa'),
            'username' => (string)($chatUserData['username'] ?? '')
        ];
    }
}
?>
<!-- ========================================================
     SmartExam AI (UJOL) - Floating Chatbot Widget ("Smarty AI")
     Backend Engine: Node.js (Port 3001) + Google Gemini API + MySQL
     ======================================================== -->

<div id="ujol-chatbot-wrapper">
    <!-- Floating Chat Trigger Button (Ikon Chat) -->
    <button type="button" 
            id="ujol-chatbot-toggle" 
            class="ujol-chat-btn shadow-lg" 
            aria-label="Buka Chatbot Asisten UJOL"
            title="Tanya Asisten AI UJOL">
        <span class="chat-icon-open">
            <i class="bi bi-chat-dots-fill"></i>
        </span>
        <span class="chat-icon-close d-none">
            <i class="bi bi-x-lg"></i>
        </span>
        <!-- Online Pulse Indicator -->
        <span class="chat-pulse-badge" title="Asisten AI Online"></span>
    </button>

    <!-- Floating Prompt Tooltip Banner (Muncul awal untuk menarik perhatian user) -->
    <div id="ujol-chatbot-tooltip" class="ujol-chat-tooltip shadow-sm">
        <div class="d-flex align-items-center gap-2">
            <div class="bot-avatar-mini">
                <i class="bi bi-robot"></i>
            </div>
            <div class="tooltip-text">
                <strong>Ada pertanyaan?</strong>
                <span class="d-block text-muted" style="font-size: 0.72rem;">Tanya Smarty AI seputar ujian</span>
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto" id="tooltip-close" style="font-size: 0.6rem;" aria-label="Tutup"></button>
        </div>
    </div>

    <!-- Chatbot Window Panel -->
    <div id="ujol-chatbot-window" class="ujol-chat-window shadow-lg d-none">
        <!-- Window Header -->
        <!-- Window Header -->
        <div class="ujol-chat-header d-flex align-items-center justify-content-between position-relative">
            <div class="d-flex align-items-center gap-2.5">
                <div class="chat-header-avatar">
                    <i class="bi bi-robot"></i>
                    <span class="status-dot"></span>
                </div>
                <div>
                    <h6 class="mb-0 font-bold text-white fs-6">Smarty AI</h6>
                    <!-- Clean Clickable Model Pill Switcher -->
                    <button type="button" id="btn-quick-model-select" class="ujol-model-pill-btn" title="Klik untuk mengganti model AI">
                        <span class="model-status-indicator"></span>
                        <span id="chat-header-model-name" class="text-truncate">Nemotron 3.5 Bolt</span>
                        <i class="bi bi-chevron-down ms-1" style="font-size: 0.62rem;"></i>
                    </button>
                </div>
            </div>
            <div class="d-flex align-items-center gap-1">
                <button type="button" id="btn-clear-chat" class="btn-chat-action" title="Hapus Riwayat Obrolan">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </button>
                <button type="button" id="btn-minimize-chat" class="btn-chat-action" title="Tutup Chat">
                    <i class="bi bi-dash-lg"></i>
                </button>
            </div>

            <!-- Sleek Popover / Dropdown Menu for AI Models -->
            <div id="ujol-quick-model-dropdown" class="ujol-quick-model-dropdown shadow-lg d-none">
                <div class="dropdown-header-title d-flex align-items-center justify-content-between">
                    <small class="text-uppercase fw-bold text-muted" style="font-size: 0.65rem; letter-spacing: 0.5px;">Ganti Model AI</small>
                    <span class="badge bg-light text-muted border" style="font-size: 0.6rem;">1-Klik Aktif</span>
                </div>
                <div class="model-options-list">
                    <!-- Option 1: Nvidia Nemotron -->
                    <div class="model-option-item active" data-provider="custom" data-model="nvidia/nemotron-3.5-bolt-e4b" data-url="https://integrate.api.nvidia.com/v1" data-label="Nemotron 3.5 Bolt">
                        <div class="option-icon bg-success-subtle text-success">
                            <i class="bi bi-lightning-charge-fill"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">Nvidia Nemotron 3.5 Bolt</div>
                            <div class="option-desc">Cepat, Cerdas & Akurat</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>

                    <!-- Option 2: Google Gemini 2.5 Flash -->
                    <div class="model-option-item" data-provider="gemini" data-model="gemini-2.5-flash" data-url="" data-label="Gemini 2.5 Flash">
                        <div class="option-icon bg-primary-subtle text-primary">
                            <i class="bi bi-stars"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">Google Gemini 2.5 Flash</div>
                            <div class="option-desc">Google AI &bull; Responsif & Cerdas</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>

                    <!-- Option 3: Google Gemini 1.5 Flash -->
                    <div class="model-option-item" data-provider="gemini" data-model="gemini-1.5-flash" data-url="" data-label="Gemini 1.5 Flash">
                        <div class="option-icon bg-info-subtle text-info">
                            <i class="bi bi-cpu"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">Google Gemini 1.5 Flash</div>
                            <div class="option-desc">Google AI &bull; Stabil & Ringan</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>

                    <!-- Option 4: Google Gemini 1.5 Pro -->
                    <div class="model-option-item" data-provider="gemini" data-model="gemini-1.5-pro" data-url="" data-label="Gemini 1.5 Pro">
                        <div class="option-icon bg-purple-subtle text-primary" style="background: rgba(147, 51, 234, 0.12); color: #9333ea !important;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">Google Gemini 1.5 Pro</div>
                            <div class="option-desc">Penalaran & Analisis Mendalam</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>

                    <!-- Option 5: OpenAI GPT-4o Mini -->
                    <div class="model-option-item" data-provider="openai" data-model="gpt-4o-mini" data-url="https://api.openai.com/v1" data-label="GPT-4o Mini">
                        <div class="option-icon bg-warning-subtle text-dark">
                            <i class="bi bi-robot"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">OpenAI GPT-4o Mini</div>
                            <div class="option-desc">OpenAI &bull; Hemat & Teruji</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>

                    <!-- Option 6: Groq Llama 3.3 -->
                    <div class="model-option-item" data-provider="groq" data-model="llama-3.3-70b-versatile" data-url="https://api.groq.com/openai/v1" data-label="Groq Llama 3.3">
                        <div class="option-icon bg-danger-subtle text-danger">
                            <i class="bi bi-speedometer2"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">Groq Llama 3.3 70B</div>
                            <div class="option-desc">Groq LPU &bull; Kecepatan Ekstrem</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>

                    <!-- Option 7: DeepSeek Chat -->
                    <div class="model-option-item" data-provider="deepseek" data-model="deepseek-chat" data-url="https://api.deepseek.com/v1" data-label="DeepSeek Chat">
                        <div class="option-icon bg-secondary-subtle text-secondary">
                            <i class="bi bi-chat-quote-fill"></i>
                        </div>
                        <div class="option-details">
                            <div class="option-title">DeepSeek Chat V3</div>
                            <div class="option-desc">DeepSeek &bull; Efisien & Logis</div>
                        </div>
                        <i class="bi bi-check-lg checkmark-active"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- Window Body (Messages) -->
        <div id="ujol-chat-messages" class="ujol-chat-messages">
            <!-- Pesan Pembuka Default -->
            <div class="chat-msg bot-msg">
                <div class="msg-avatar"><i class="bi bi-robot"></i></div>
                <div class="msg-bubble">
                    <div class="msg-text">
                        Halo! 👋 Saya <strong>Smarty AI</strong>, asisten pintar untuk <strong>SmartExam AI (UJOL)</strong>.
                        <br><br>
                        Ada yang bisa saya bantu terkait jadwal ujian, tata tertib asesmen, panduan kamera, atau fitur lainnya?
                    </div>
                    <span class="msg-time"><?= date('H:i') ?></span>
                </div>
            </div>

            <!-- Quick Suggestion Chips -->
            <div class="chat-suggestions mb-2" id="chat-suggestions">
                <span class="suggestions-label"><i class="bi bi-stars me-1 text-warning"></i>Pertanyaan Cepat & Database:</span>
                <div class="chips-container">
                    <button type="button" class="chip-btn" data-msg="Jadwal ujian apa saja yang aktif atau mendatang?">
                        📅 Jadwal Ujian Aktif?
                    </button>
                    <button type="button" class="chip-btn" data-msg="Bagaimana riwayat dan nilai ujian saya?">
                        📊 Cek Nilai Ujian Saya
                    </button>
                    <button type="button" class="chip-btn" data-msg="Siapa saja guru dan mata pelajaran yang tersedia?">
                        👨‍🏫 Info Guru & Mapel
                    </button>
                    <button type="button" class="chip-btn" data-msg="Kamera tidak terdeteksi saat uji perangkat, bagaimana solusinya?">
                        📹 Kamera tidak muncul?
                    </button>
                </div>
            </div>
        </div>

        <!-- Typing Indicator -->
        <div id="ujol-chat-typing" class="ujol-chat-typing d-none">
            <div class="msg-avatar-mini"><i class="bi bi-robot"></i></div>
            <div class="typing-bubble">
                <span class="dot"></span>
                <span class="dot"></span>
                <span class="dot"></span>
            </div>
        </div>

        <!-- Window Footer (Input Area) -->
        <div class="ujol-chat-footer">
            <form id="ujol-chat-form" class="d-flex align-items-center gap-2 m-0">
                <input type="text" 
                       id="ujol-chat-input" 
                       class="form-control ujol-input-sm" 
                       placeholder="Ketik pertanyaan Anda di sini..." 
                       autocomplete="off" 
                       maxlength="500">
                <button type="submit" id="ujol-chat-send" class="btn-chat-send" title="Kirim Pesan">
                    <i class="bi bi-send-fill"></i>
                </button>
            </form>
            <div class="chat-footer-badge text-center mt-1.5">
                <small class="text-muted" id="chat-footer-badge-text" style="font-size: 0.65rem;">
                    Didukung oleh <strong>Node.js</strong> &bull; Nvidia Nemotron 3.5 Bolt
                </small>
            </div>
        </div>
    </div>
</div>

<style>
/* ========================================================
   SmartExam AI Chatbot Styles
   ======================================================== */
#ujol-chatbot-wrapper {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 99999;
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
}

/* Floating Trigger Button */
.ujol-chat-btn {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    border: none;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.65rem;
    position: relative;
    box-shadow: 0 8px 24px rgba(37, 99, 235, 0.4);
    transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
}

.ujol-chat-btn:hover {
    transform: scale(1.08) translateY(-2px);
    box-shadow: 0 12px 28px rgba(37, 99, 235, 0.55);
    background: linear-gradient(135deg, #1d4ed8, #1e40af);
}

.ujol-chat-btn:active {
    transform: scale(0.95);
}

/* Pulse Badge */
.chat-pulse-badge {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 14px;
    height: 14px;
    background-color: #10b981;
    border: 2.5px solid #ffffff;
    border-radius: 50%;
    animation: pulseGlow 2s infinite;
}

@keyframes pulseGlow {
    0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
    70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); }
    100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
}

/* Prompt Tooltip */
.ujol-chat-tooltip {
    position: absolute;
    bottom: 74px;
    right: 0;
    background: #1e293b;
    color: #ffffff;
    padding: 10px 14px;
    border-radius: 12px;
    width: 250px;
    border: 1px solid rgba(255, 255, 255, 0.1);
    animation: bounceIn 0.5s ease;
    transition: opacity 0.3s ease;
}

.bot-avatar-mini {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #3b82f6;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.tooltip-text strong {
    font-size: 0.82rem;
    display: block;
}

/* Chat Window */
.ujol-chat-window {
    position: absolute;
    bottom: 75px;
    right: 0;
    width: 380px;
    max-width: calc(100vw - 32px);
    height: 540px;
    max-height: calc(100vh - 120px);
    background: #ffffff;
    border-radius: 18px;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    border: 1px solid rgba(0, 0, 0, 0.08);
    box-shadow: 0 20px 50px rgba(0, 0, 0, 0.22);
    animation: slideUpFade 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(20px) scale(0.96);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

/* Chat Header */
.ujol-chat-header {
    background: linear-gradient(135deg, #1e3a8a, #2563eb);
    padding: 14px 18px;
    color: #ffffff;
    flex-shrink: 0;
}

.chat-header-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.15rem;
    position: relative;
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.chat-header-avatar .status-dot {
    position: absolute;
    bottom: 0;
    right: 0;
    width: 10px;
    height: 10px;
    border-radius: 50%;
    background: #10b981;
    border: 2px solid #1e3a8a;
}

.btn-chat-action {
    background: transparent;
    border: none;
    color: rgba(255, 255, 255, 0.8);
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: background 0.2s;
    font-size: 0.95rem;
}

.btn-chat-action:hover {
    background: rgba(255, 255, 255, 0.18);
    color: #ffffff;
}

/* Quick Model Pill Selector in Header */
.ujol-model-pill-btn {
    background: rgba(255, 255, 255, 0.16);
    border: 1px solid rgba(255, 255, 255, 0.28);
    color: #ffffff;
    font-size: 0.72rem;
    padding: 2.5px 9px;
    border-radius: 12px;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
    transition: all 0.2s ease;
    max-width: 180px;
    outline: none;
}

.ujol-model-pill-btn:hover {
    background: rgba(255, 255, 255, 0.28);
    border-color: rgba(255, 255, 255, 0.45);
    transform: translateY(-1px);
}

.ujol-model-pill-btn:active {
    transform: scale(0.97);
}

.model-status-indicator {
    display: inline-block;
    width: 6.5px;
    height: 6.5px;
    border-radius: 50%;
    background: #10b981;
    box-shadow: 0 0 6px #10b981;
    flex-shrink: 0;
}

/* Quick Model Dropdown Popover */
.ujol-quick-model-dropdown {
    position: absolute;
    top: 60px;
    left: 14px;
    width: 295px;
    background: #ffffff;
    border-radius: 14px;
    box-shadow: 0 16px 36px rgba(15, 23, 42, 0.22), 0 4px 10px rgba(0, 0, 0, 0.08);
    border: 1px solid #e2e8f0;
    z-index: 100;
    padding: 6px 0;
    animation: quickDropdownFadeIn 0.18s cubic-bezier(0.16, 1, 0.3, 1);
}

@keyframes quickDropdownFadeIn {
    from {
        opacity: 0;
        transform: translateY(-8px) scale(0.97);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.dropdown-header-title {
    padding: 6px 14px 6px 14px;
    border-bottom: 1px solid #f1f5f9;
}

.model-options-list {
    max-height: 290px;
    overflow-y: auto;
    padding: 5px 6px;
    display: flex;
    flex-direction: column;
    gap: 2px;
}

.model-option-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 8px 10px;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.15s ease;
    color: #1e293b;
    position: relative;
    border: 1px solid transparent;
}

.model-option-item:hover {
    background: #f1f5f9;
}

.model-option-item.active {
    background: #eff6ff;
    border-color: #bfdbfe;
    color: #1d4ed8;
}

.option-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
}

.option-details {
    flex: 1;
    min-width: 0;
}

.option-title {
    font-size: 0.78rem;
    font-weight: 600;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.option-desc {
    font-size: 0.65rem;
    color: #64748b;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.checkmark-active {
    font-size: 1rem;
    color: #2563eb;
    display: none;
    flex-shrink: 0;
}

.model-option-item.active .checkmark-active {
    display: block;
}

/* Messages Area */
.ujol-chat-messages {
    flex: 1;
    padding: 16px;
    overflow-y: auto;
    background: #f8fafc;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.chat-msg {
    display: flex;
    gap: 8px;
    max-width: 88%;
}

.chat-msg.bot-msg {
    align-self: flex-start;
}

.chat-msg.user-msg {
    align-self: flex-end;
    flex-direction: row-reverse;
}

.msg-avatar {
    width: 28px;
    height: 28px;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    flex-shrink: 0;
    margin-top: 2px;
}

.chat-msg.user-msg .msg-avatar {
    background: #475569;
}

.msg-bubble {
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 0.85rem;
    line-height: 1.45;
    position: relative;
    word-break: break-word;
}

.bot-msg .msg-bubble {
    background: #ffffff;
    color: #1e293b;
    border: 1px solid #e2e8f0;
    border-top-left-radius: 4px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}

.user-msg .msg-bubble {
    background: linear-gradient(135deg, #2563eb, #1d4ed8);
    color: #ffffff;
    border-top-right-radius: 4px;
    box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
}

.msg-time {
    display: block;
    font-size: 0.65rem;
    color: #94a3b8;
    margin-top: 4px;
    text-align: right;
}

.user-msg .msg-time {
    color: rgba(255, 255, 255, 0.7);
}

/* Suggestion Chips */
.chat-suggestions {
    background: #ffffff;
    border: 1px dashed #cbd5e1;
    border-radius: 12px;
    padding: 10px 12px;
}

.suggestions-label {
    display: block;
    font-size: 0.72rem;
    font-weight: 600;
    color: #475569;
    margin-bottom: 6px;
}

.chips-container {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
}

.chip-btn {
    background: #f1f5f9;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    padding: 4px 10px;
    font-size: 0.74rem;
    color: #334155;
    cursor: pointer;
    transition: all 0.2s;
    text-align: left;
}

.chip-btn:hover {
    background: #e0f2fe;
    color: #0369a1;
    border-color: #bae6fd;
}

/* Typing Indicator */
.ujol-chat-typing {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0 16px 12px 16px;
    background: #f8fafc;
}

.msg-avatar-mini {
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.75rem;
}

.typing-bubble {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    padding: 8px 14px;
    border-radius: 14px;
    display: flex;
    gap: 4px;
    align-items: center;
}

.typing-bubble .dot {
    width: 6px;
    height: 6px;
    background: #94a3b8;
    border-radius: 50%;
    animation: typingBlink 1.4s infinite ease-in-out both;
}

.typing-bubble .dot:nth-child(1) { animation-delay: -0.32s; }
.typing-bubble .dot:nth-child(2) { animation-delay: -0.16s; }

@keyframes typingBlink {
    0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
    40% { transform: scale(1.1); opacity: 1; }
}

/* Chat Footer */
.ujol-chat-footer {
    padding: 12px 16px;
    background: #ffffff;
    border-top: 1px solid #e2e8f0;
    flex-shrink: 0;
}

.ujol-input-sm {
    border-radius: 20px;
    padding: 8px 16px;
    font-size: 0.85rem;
    border: 1px solid #cbd5e1;
    background: #f8fafc;
    transition: border-color 0.2s, box-shadow 0.2s;
}

.ujol-input-sm:focus {
    background: #ffffff;
    border-color: #2563eb;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15);
}

.btn-chat-send {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #2563eb;
    color: #ffffff;
    border: none;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    flex-shrink: 0;
    transition: transform 0.2s, background 0.2s;
}

.btn-chat-send:hover {
    background: #1d4ed8;
    transform: scale(1.05);
}

.btn-chat-send:disabled {
    background: #cbd5e1;
    cursor: not-allowed;
    transform: none;
}
</style>

<script>
(function() {
    // Endpoint Node.js Chatbot Server
    const NODE_SERVER_URL = "http://localhost:3001/api/chat";
    const CURRENT_CHAT_USER = <?= json_encode($chatUserContext) ?>;

    const wrapper = document.getElementById("ujol-chatbot-wrapper");
    const toggleBtn = document.getElementById("ujol-chatbot-toggle");
    const chatWindow = document.getElementById("ujol-chatbot-window");
    const tooltip = document.getElementById("ujol-chatbot-tooltip");
    const tooltipClose = document.getElementById("tooltip-close");
    const openIcon = toggleBtn.querySelector(".chat-icon-open");
    const closeIcon = toggleBtn.querySelector(".chat-icon-close");

    const messagesContainer = document.getElementById("ujol-chat-messages");
    const typingIndicator = document.getElementById("ujol-chat-typing");
    const chatForm = document.getElementById("ujol-chat-form");
    const chatInput = document.getElementById("ujol-chat-input");
    const sendBtn = document.getElementById("ujol-chat-send");
    const clearBtn = document.getElementById("btn-clear-chat");
    const minimizeBtn = document.getElementById("btn-minimize-chat");

    let isChatOpen = false;
    let chatHistory = [];

    // Muat riwayat dari localStorage jika ada
    try {
        const saved = localStorage.getItem("smartexam_chat_history");
        if (saved) {
            chatHistory = JSON.parse(saved);
            renderSavedHistory();
        }
    } catch (e) {}

    // Buka / Tutup Chat Window
    function toggleChat(forceOpen = null) {
        isChatOpen = (forceOpen !== null) ? forceOpen : !isChatOpen;

        if (isChatOpen) {
            chatWindow.classList.remove("d-none");
            openIcon.classList.add("d-none");
            closeIcon.classList.remove("d-none");
            if (tooltip) tooltip.classList.add("d-none");
            setTimeout(() => {
                chatInput.focus();
                scrollToBottom();
            }, 100);
        } else {
            chatWindow.classList.add("d-none");
            openIcon.classList.remove("d-none");
            closeIcon.classList.add("d-none");
        }
    }

    toggleBtn.addEventListener("click", () => toggleChat());
    if (minimizeBtn) minimizeBtn.addEventListener("click", () => toggleChat(false));
    if (tooltipClose) {
        tooltipClose.addEventListener("click", (e) => {
            e.stopPropagation();
            tooltip.classList.add("d-none");
        });
    }

    // Klik Chip Pertanyaan
    document.addEventListener("click", function(e) {
        if (e.target.classList.contains("chip-btn")) {
            const question = e.target.getAttribute("data-msg");
            if (question) {
                sendMessage(question);
            }
        }
    });

    // Submit Form Chat
    chatForm.addEventListener("submit", function(e) {
        e.preventDefault();
        const text = chatInput.value.trim();
        if (text) {
            sendMessage(text);
            chatInput.value = "";
        }
    });

    // Clear Chat
    clearBtn.addEventListener("click", function() {
        if (confirm("Hapus seluruh riwayat percakapan?")) {
            chatHistory = [];
            localStorage.removeItem("smartexam_chat_history");
            messagesContainer.innerHTML = `
                <div class="chat-msg bot-msg">
                    <div class="msg-avatar"><i class="bi bi-robot"></i></div>
                    <div class="msg-bubble">
                        <div class="msg-text">
                            Obrolan telah dibersihkan. Ada yang bisa saya bantu terkait aplikasi UJOL?
                        </div>
                        <span class="msg-time">${getCurrentTime()}</span>
                    </div>
                </div>
            `;
        }
    });

    // Format waktu
    function getCurrentTime() {
        const d = new Date();
        return ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
    }

    // Scroll ke bawah
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // Render pesan tersimpan
    function renderSavedHistory() {
        if (!chatHistory || chatHistory.length === 0) return;
        chatHistory.forEach(item => {
            appendMessage(item.role, item.text, item.time, false);
        });
    }

    // Tambah bubble pesan ke UI
    function appendMessage(role, text, time = null, saveToStorage = true) {
        const isBot = (role === "assistant" || role === "model");
        const msgTime = time || getCurrentTime();

        const msgDiv = document.createElement("div");
        msgDiv.className = `chat-msg ${isBot ? "bot-msg" : "user-msg"}`;

        const formattedText = isBot ? formatMarkdown(text) : escapeHtml(text);

        msgDiv.innerHTML = `
            <div class="msg-avatar">
                <i class="bi bi-${isBot ? 'robot' : 'person-fill'}"></i>
            </div>
            <div class="msg-bubble">
                <div class="msg-text">${formattedText}</div>
                <span class="msg-time">${msgTime}</span>
            </div>
        `;

        messagesContainer.appendChild(msgDiv);
        scrollToBottom();

        if (saveToStorage) {
            chatHistory.push({ role: isBot ? "assistant" : "user", text, time: msgTime });
            // Batasi simpan 20 pesan terakhir di localStorage
            if (chatHistory.length > 20) chatHistory = chatHistory.slice(-20);
            try {
                localStorage.setItem("smartexam_chat_history", JSON.stringify(chatHistory));
            } catch (e) {}
        }
    }

    // Pengurai Markdown Sederhana
    function formatMarkdown(str) {
        if (!str) return "";
        let text = escapeHtml(str);

        // Bold
        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Italic
        text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
        // Headers (### Header)
        text = text.replace(/^### (.*$)/gim, '<h6 class="font-bold text-dark mt-2 mb-1">$1</h6>');
        // Bullet points
        text = text.replace(/^\* (.*$)/gim, '<div class="ms-2 mb-1">&bull; $1</div>');
        text = text.replace(/^- (.*$)/gim, '<div class="ms-2 mb-1">&bull; $1</div>');
        // Numbered points
        text = text.replace(/^(\d+)\. (.*$)/gim, '<div class="ms-2 mb-1"><strong>$1.</strong> $2</div>');
        // Linebreaks
        text = text.replace(/\n/g, '<br>');

        return text;
    }

    function escapeHtml(text) {
        const div = document.createElement("div");
        div.textContent = text;
        return div.innerHTML;
    }

    // Kirim pesan ke Node.js server
    async function sendMessage(text) {
        // Tampilkan pesan user
        appendMessage("user", text);

        // Sembunyikan chip pertanyaan setelah interaksi
        const suggestions = document.getElementById("chat-suggestions");
        if (suggestions) suggestions.style.display = "none";

        // Tampilkan indikator mengetik
        typingIndicator.classList.remove("d-none");
        sendBtn.disabled = true;
        scrollToBottom();

        try {
            const response = await fetch(NODE_SERVER_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    message: text,
                    history: chatHistory.slice(-6),
                    user: CURRENT_CHAT_USER
                })
            });

            const data = await response.json();

            typingIndicator.classList.add("d-none");
            sendBtn.disabled = false;

            if (data.success && data.reply) {
                appendMessage("assistant", data.reply);
            } else {
                appendMessage("assistant", data.error || "Maaf, tidak ada balasan dari server chatbot.");
            }

        } catch (err) {
            console.error("[Chatbot Client Error]:", err);
            typingIndicator.classList.add("d-none");
            sendBtn.disabled = false;

            appendMessage("assistant", 
                "⚠️ Gagal terhubung ke Server Chatbot Node.js (" + NODE_SERVER_URL + "). Pastikan service Node.js telah aktif di background (Port 3001)."
            );
        }
    }

    // ========================================================
    // Ganti Opsi Model AI (1-Klik Dropdown Popover)
    // ========================================================
    const MODEL_CONFIG_URL = "http://localhost:3001/api/model-config";

    const btnQuickModelSelect = document.getElementById("btn-quick-model-select");
    const quickModelDropdown = document.getElementById("ujol-quick-model-dropdown");
    const headerModelName = document.getElementById("chat-header-model-name");
    const footerBadgeText = document.getElementById("chat-footer-badge-text");
    const modelOptionItems = document.querySelectorAll(".model-option-item");

    let currentModelSlug = "nvidia/nemotron-3.5-bolt-e4b";

    // Format nama pendek untuk badge header & footer
    function formatModelShortName(modelSlug) {
        if (!modelSlug) return "Nemotron 3.5 Bolt";
        if (modelSlug.includes("nemotron")) return "Nemotron 3.5 Bolt";
        if (modelSlug.includes("gemini-2.5-flash")) return "Gemini 2.5 Flash";
        if (modelSlug.includes("gemini-1.5-flash")) return "Gemini 1.5 Flash";
        if (modelSlug.includes("gemini-1.5-pro")) return "Gemini 1.5 Pro";
        if (modelSlug.includes("llama-3.3-70b")) return "Groq Llama 3.3";
        if (modelSlug.includes("gpt-4o-mini")) return "GPT-4o Mini";
        if (modelSlug.includes("deepseek-chat")) return "DeepSeek Chat";
        const parts = modelSlug.split("/");
        return parts[parts.length - 1];
    }

    // Toggle dropdown model
    function toggleQuickDropdown(show = null) {
        if (!quickModelDropdown) return;
        const willShow = (show !== null) ? show : quickModelDropdown.classList.contains("d-none");
        if (willShow) {
            quickModelDropdown.classList.remove("d-none");
        } else {
            quickModelDropdown.classList.add("d-none");
        }
    }

    if (btnQuickModelSelect) {
        btnQuickModelSelect.addEventListener("click", function(e) {
            e.stopPropagation();
            toggleQuickDropdown();
        });
    }

    // Tutup dropdown saat klik di luar
    document.addEventListener("click", function(e) {
        if (quickModelDropdown && !quickModelDropdown.contains(e.target) && e.target !== btnQuickModelSelect) {
            toggleQuickDropdown(false);
        }
    });

    // Tandai model aktif di list
    function highlightActiveModelOption(slug) {
        currentModelSlug = slug;
        const shortName = formatModelShortName(slug);
        if (headerModelName) headerModelName.textContent = shortName;
        if (footerBadgeText) footerBadgeText.innerHTML = `Didukung oleh <strong>Node.js</strong> &bull; ${shortName}`;

        modelOptionItems.forEach(item => {
            if (item.getAttribute("data-model") === slug) {
                item.classList.add("active");
            } else {
                item.classList.remove("active");
            }
        });
    }

    // Ambil status model awal dari server
    async function loadCurrentModel() {
        try {
            const res = await fetch(MODEL_CONFIG_URL);
            const data = await res.json();
            if (data.success && data.current) {
                highlightActiveModelOption(data.current.modelName);
            }
        } catch (e) {
            console.warn("[Chatbot] Gagal memuat info model aktif:", e);
        }
    }

    // Klik opsi model langsung ganti (1-Klik)
    modelOptionItems.forEach(item => {
        item.addEventListener("click", async function() {
            const provider = this.getAttribute("data-provider");
            const model = this.getAttribute("data-model");
            const baseUrl = this.getAttribute("data-url") || "";
            const label = this.getAttribute("data-label") || model;

            // Highlight langsung secara responsif
            highlightActiveModelOption(model);
            toggleQuickDropdown(false);

            try {
                const response = await fetch(MODEL_CONFIG_URL, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({
                        provider: provider,
                        modelName: model,
                        baseUrl: baseUrl
                    })
                });
                const result = await response.json();
                if (result.success) {
                    appendMessage("assistant", 
                        `⚡ Model AI dialihkan ke **${label}** (*${model}*). Ada yang bisa saya bantu?`
                    );
                } else {
                    console.error("Gagal ganti model:", result.error);
                }
            } catch (err) {
                console.error("Gagal ganti model:", err);
            }
        });
    });

    // Panggil saat widget dimuat
    loadCurrentModel();
})();
</script>
