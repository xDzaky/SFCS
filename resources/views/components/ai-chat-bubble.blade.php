{{-- ═══════════════════════════════════════════════════════════════════
     SFCS AI Chat Assistant — Modern Halodoc HILDA Style
     Mobile Full-Screen & Desktop Popup, Clean Typography & Robust Layout
     ═══════════════════════════════════════════════════════════════════ --}}

<style>
/* ─── Variables ──────────────────────────────────────────────────────── */
:root {
    --cb-primary:       #4f46e5;
    --cb-primary-dark:  #4338ca;
    --cb-primary-light: #ede9fe;
    --cb-user-text:     #312e81;
    --cb-bg-body:       #f8fafc;
    --cb-border:        #e2e8f0;
    --cb-text-dark:     #0f172a;
    --cb-text-body:     #334155;
    --cb-text-muted:    #94a3b8;
    --cb-radius-box:    20px;
}

/* ─── FAB Button ─────────────────────────────────────────────────────── */
.cb-fab {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border: none;
    color: #fff;
    font-size: 24px;
    cursor: pointer;
    box-shadow: 0 8px 24px rgba(79, 70, 229, 0.38);
    z-index: 9990;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.2s ease, box-shadow 0.25s ease;
    outline: none;
}
.cb-fab:hover {
    transform: scale(1.08);
    box-shadow: 0 10px 28px rgba(79, 70, 229, 0.48);
}
.cb-fab:active {
    transform: scale(0.94);
}
/* When chat window is open, hide FAB completely so it doesn't overlap input/send buttons */
.cb-fab.open {
    transform: scale(0) !important;
    opacity: 0 !important;
    pointer-events: none !important;
}

/* Pulse animation on FAB */
.cb-fab::after {
    content: '';
    position: absolute;
    inset: -3px;
    border-radius: 50%;
    border: 2px solid rgba(99, 102, 241, 0.45);
    animation: cb-pulse-ring 2.8s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
    pointer-events: none;
}
@keyframes cb-pulse-ring {
    0%   { transform: scale(0.95); opacity: 0.9; }
    50%  { transform: scale(1.28); opacity: 0; }
    100% { transform: scale(1.28); opacity: 0; }
}

@media (max-width: 600px) {
    .cb-fab {
        bottom: 18px;
        right: 18px;
        width: 52px;
        height: 52px;
        font-size: 21px;
    }
}

/* ─── Chat Window (Desktop Floating Modal) ───────────────────────────── */
.cb-win {
    position: fixed;
    bottom: 24px;
    right: 24px;
    width: 390px;
    height: 600px;
    max-height: calc(100vh - 48px);
    background: #ffffff;
    border-radius: var(--cb-radius-box);
    box-shadow: 0 16px 48px rgba(15, 23, 42, 0.16), 0 0 0 1px rgba(0, 0, 0, 0.04);
    z-index: 9999;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    transform: scale(0.9) translateY(18px);
    opacity: 0;
    pointer-events: none;
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.24s ease;
    transform-origin: bottom right;
}
.cb-win.open {
    transform: scale(1) translateY(0);
    opacity: 1;
    pointer-events: all;
}

/* ─── Mobile Fullscreen Mode ─────────────────────────────────────────── */
@media (max-width: 768px) {
    .cb-win {
        position: fixed !important;
        top: 0 !important;
        bottom: 0 !important;
        left: 0 !important;
        right: 0 !important;
        width: 100vw !important;
        height: 100dvh !important;
        max-height: 100dvh !important;
        border-radius: 0 !important;
        margin: 0 !important;
        transform: translateY(100%) !important;
        transform-origin: bottom center !important;
        box-shadow: none !important;
        z-index: 100000 !important;
    }
    .cb-win.open {
        transform: translateY(0) !important;
    }
}

/* ─── Chat Header ────────────────────────────────────────────────────── */
.cb-hdr {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    padding: 14px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    color: #ffffff;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(79, 70, 229, 0.2);
}
.cb-hdr-avatar {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(4px);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    flex-shrink: 0;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
}
.cb-hdr-info {
    flex: 1;
    min-width: 0;
}
.cb-hdr-name {
    font-weight: 700;
    font-size: 15px;
    line-height: 1.2;
    letter-spacing: -0.01em;
}
.cb-hdr-status {
    font-size: 11.5px;
    opacity: 0.9;
    display: flex;
    align-items: center;
    gap: 6px;
    margin-top: 2px;
}
.cb-hdr-status::before {
    content: '';
    width: 7px;
    height: 7px;
    background: #34d399;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 6px #34d399;
}
.cb-hdr-close {
    background: rgba(255, 255, 255, 0.18);
    border: none;
    color: #ffffff;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    font-size: 14px;
    transition: background 0.18s, transform 0.18s;
    outline: none;
}
.cb-hdr-close:hover {
    background: rgba(255, 255, 255, 0.32);
    transform: scale(1.08);
}

/* ─── Chat Body / Conversation Area ──────────────────────────────────── */
.cb-body {
    flex: 1;
    overflow-y: auto;
    padding: 16px 16px 12px;
    display: flex;
    flex-direction: column;
    gap: 14px;
    background: var(--cb-bg-body);
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}
.cb-body::-webkit-scrollbar { width: 4px; }
.cb-body::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 4px; }

/* ─── Message Row Container ──────────────────────────────────────────── */
.cb-row {
    display: flex;
    width: 100%;
    animation: cb-fade-up 0.22s ease-out;
}
@keyframes cb-fade-up {
    from { opacity: 0; transform: translateY(6px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* BOT ROW: Aligned Left, Avatar at Top-Left */
.cb-row.bot {
    justify-content: flex-start;
    align-items: flex-start;
    gap: 9px;
}
.cb-row.bot .cb-bot-av {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #4f46e5;
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    flex-shrink: 0;
    margin-top: 2px;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
}

/* USER ROW: Aligned Right, Clean Lavender Bubble (Halodoc Style) */
.cb-row.usr {
    justify-content: flex-end;
    align-items: flex-end;
}

/* ─── Bubble Wrap & Bubbles ──────────────────────────────────────────── */
.cb-msg-box {
    display: flex;
    flex-direction: column;
    max-width: 84%;
    min-width: 0;
}
.cb-row.usr .cb-msg-box {
    align-items: flex-end;
}
.cb-row.bot .cb-msg-box {
    align-items: flex-start;
}

/* User Bubble: Soft Lavender Pill */
.cb-bbl-usr {
    background: var(--cb-primary-light);
    color: var(--cb-user-text);
    font-size: 14px;
    font-weight: 500;
    line-height: 1.5;
    padding: 10px 16px;
    border-radius: 18px 18px 4px 18px;
    box-shadow: 0 1px 3px rgba(79, 70, 229, 0.08);
    word-break: normal;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    display: inline-block;
    width: fit-content;
    max-width: 100%;
}

/* Bot Bubble: Clean White Card */
.cb-bbl-bot {
    background: #ffffff;
    color: var(--cb-text-body);
    font-size: 13.5px;
    line-height: 1.6;
    padding: 13px 16px;
    border-radius: 18px 18px 18px 4px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(15, 23, 42, 0.04);
    word-break: normal;
    overflow-wrap: anywhere;
    white-space: pre-wrap;
    display: inline-block;
    width: fit-content;
    max-width: 100%;
}
.cb-bbl-bot strong {
    color: var(--cb-text-dark);
    font-weight: 600;
}

/* Timestamp */
.cb-ts {
    font-size: 10.5px;
    color: var(--cb-text-muted);
    margin-top: 4px;
    line-height: 1;
}
.cb-row.usr .cb-ts {
    text-align: right;
    margin-right: 4px;
}
.cb-row.bot .cb-ts {
    text-align: left;
    margin-left: 4px;
}

/* ─── Bot Action Buttons ─────────────────────────────────────────────── */
.cb-actions {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 10px;
    width: 100%;
}
.cb-action-btn {
    display: block;
    padding: 8px 14px;
    background: #f5f3ff;
    border: 1px solid #ddd6fe;
    border-radius: 12px;
    color: #4f46e5;
    font-size: 12.5px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    transition: all 0.16s ease;
    cursor: pointer;
}
.cb-action-btn:hover {
    background: #4f46e5;
    color: #ffffff;
    border-color: #4f46e5;
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);
}

/* ─── Typing Indicator ───────────────────────────────────────────────── */
.cb-typing-wrap {
    display: flex;
    align-items: flex-start;
    gap: 9px;
    animation: cb-fade-up 0.2s ease-out;
}
.cb-typing-dots {
    background: #ffffff;
    padding: 12px 18px;
    border-radius: 18px 18px 18px 4px;
    border: 1px solid #e2e8f0;
    display: flex;
    gap: 5px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04);
}
.cb-typing-dots span {
    width: 7px;
    height: 7px;
    background: #94a3b8;
    border-radius: 50%;
    animation: cb-bounce 1.3s ease-in-out infinite;
}
.cb-typing-dots span:nth-child(2) { animation-delay: 0.16s; }
.cb-typing-dots span:nth-child(3) { animation-delay: 0.32s; }
@keyframes cb-bounce {
    0%, 60%, 100% { transform: translateY(0); }
    30%           { transform: translateY(-5px); background: #4f46e5; }
}

/* ─── Quick Reply Chips ──────────────────────────────────────────────── */
.cb-chips-area {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 8px 14px 10px;
    background: #ffffff;
    border-top: 1px solid var(--cb-border);
    flex-shrink: 0;
}
.cb-chip-btn {
    background: #f8fafc;
    border: 1px solid #cbd5e1;
    border-radius: 16px;
    padding: 6px 12px;
    font-size: 12px;
    font-weight: 500;
    color: #475569;
    cursor: pointer;
    transition: all 0.16s ease;
    white-space: nowrap;
    user-select: none;
}
.cb-chip-btn:hover {
    background: #4f46e5;
    color: #ffffff;
    border-color: #4f46e5;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(79, 70, 229, 0.2);
}

/* ─── Input Bar ──────────────────────────────────────────────────────── */
.cb-input-wrap {
    padding: 10px 14px;
    background: #ffffff;
    border-top: 1px solid var(--cb-border);
    display: flex;
    gap: 9px;
    align-items: center;
    flex-shrink: 0;
}
.cb-input-field {
    flex: 1;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    border-radius: 24px;
    padding: 10px 16px;
    font-size: 13.5px;
    outline: none;
    color: #0f172a;
    font-family: inherit;
    transition: all 0.2s ease;
    resize: none;
    max-height: 80px;
    line-height: 1.45;
}
.cb-input-field:focus {
    background: #ffffff;
    border-color: #6366f1;
    box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.15);
}
.cb-input-field::placeholder {
    color: #94a3b8;
}
.cb-send-btn {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
    border: none;
    color: #ffffff;
    font-size: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
    transition: all 0.18s ease;
    opacity: 0.45;
    flex-shrink: 0;
    outline: none;
}
.cb-send-btn.active {
    opacity: 1;
    box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);
}
.cb-send-btn.active:hover {
    transform: scale(1.06);
}
.cb-send-btn.active:active {
    transform: scale(0.94);
}

/* ─── Footer & Character Counter ─────────────────────────────────────── */
.cb-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 2px 16px 6px;
    background: #ffffff;
    flex-shrink: 0;
}
.cb-footer-brand {
    font-size: 10px;
    color: #94a3b8;
    letter-spacing: 0.02em;
}
.cb-char-counter {
    font-size: 10px;
    color: #94a3b8;
}

@media (max-width: 600px) {
    .cb-input-wrap {
        padding: 12px 14px calc(12px + env(safe-area-inset-bottom, 0px));
    }
    .cb-input-field {
        font-size: 14.5px;
        padding: 11px 16px;
    }
    .cb-send-btn {
        width: 42px;
        height: 42px;
        font-size: 16px;
    }
}
</style>

{{-- ── Floating Action Button (FAB) ──────────────────────────────────── --}}
<button class="cb-fab" id="cbFab" title="Tanya SFCS Assistant">
    <i class="fas fa-robot"></i>
</button>

{{-- ── Chat Window Container ─────────────────────────────────────────── --}}
<div class="cb-win" id="cbWin">

    {{-- 1. Header --}}
    <div class="cb-hdr">
        <div class="cb-hdr-avatar">🤖</div>
        <div class="cb-hdr-info">
            <div class="cb-hdr-name">SFCS Assistant</div>
            <div class="cb-hdr-status">Online • Siap membantu</div>
        </div>
        <button class="cb-hdr-close" id="cbClose" title="Tutup Chat">
            <i class="fas fa-xmark"></i>
        </button>
    </div>

    {{-- 2. Message Body --}}
    <div class="cb-body" id="cbBody">
        {{-- Welcome message from Bot --}}
        <div class="cb-row bot" id="cbWelcome">
            <div class="cb-bot-av">🤖</div>
            <div class="cb-msg-box">
                <div class="cb-bbl-bot">👋 Halo, <strong>{{ Auth::user()->name ?? 'User' }}</strong>!

Saya asisten virtual <strong>SFCS</strong>. Saya bisa bantu kamu dengan informasi seputar:
📋 Pengaduan fasilitas sekolah
📦 Peminjaman barang
📊 Status dan tracking laporan

Silakan tanyakan apa saja! 😊</div>
                <div class="cb-ts">Baru saja</div>
            </div>
        </div>
    </div>

    {{-- 3. Quick Reply Chips --}}
    <div class="cb-chips-area" id="cbChips">
        <span class="cb-chip-btn" data-msg="Cara membuat pengaduan">📋 Cara Lapor</span>
        <span class="cb-chip-btn" data-msg="Status pengaduan saya">📊 Status Pengaduan</span>
        <span class="cb-chip-btn" data-msg="Cara pinjam barang">📦 Cara Pinjam</span>
        <span class="cb-chip-btn" data-msg="Cek stok barang">📦 Stok Barang</span>
        <span class="cb-chip-btn" data-msg="Kategori kerusakan">📂 Kategori</span>
        <span class="cb-chip-btn" data-msg="Bantuan">ℹ️ Bantuan</span>
    </div>

    {{-- 4. Input Field & Send Button --}}
    <div class="cb-input-wrap">
        <textarea class="cb-input-field" id="cbInp" rows="1" placeholder="Tanyakan apa saja..." maxlength="500"></textarea>
        <button class="cb-send-btn" id="cbSend" title="Kirim Pesan">
            <i class="fas fa-paper-plane"></i>
        </button>
    </div>

    {{-- 5. Footer & Counter --}}
    <div class="cb-footer">
        <span class="cb-footer-brand">Powered by SFCS AI Assistant</span>
        <span class="cb-char-counter"><span id="cbCount">0</span>/500</span>
    </div>
</div>

<script>
(function () {
    const fab     = document.getElementById('cbFab');
    const win     = document.getElementById('cbWin');
    const close   = document.getElementById('cbClose');
    const body    = document.getElementById('cbBody');
    const inp     = document.getElementById('cbInp');
    const snd     = document.getElementById('cbSend');
    const chips   = document.getElementById('cbChips');
    const counter = document.getElementById('cbCount');

    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const CHAT_URL = '{{ route("chatbot.chat") }}';

    let isOpen   = false;
    let isBusy   = false;
    let hasTyped = false;

    /* ── Toggle Open / Close ─────────────────────────────────────────── */
    function openChat() {
        isOpen = true;
        fab.classList.add('open');
        win.classList.add('open');
        setTimeout(() => inp.focus(), 250);
    }

    function closeChat() {
        isOpen = false;
        fab.classList.remove('open');
        win.classList.remove('open');
    }

    fab.addEventListener('click', openChat);
    close.addEventListener('click', closeChat);

    /* ── Auto-grow Textarea & Counter ────────────────────────────────── */
    inp.addEventListener('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 80) + 'px';
        const len = this.value.length;
        counter.textContent = len;
        snd.classList.toggle('active', len > 0);
    });

    /* ── Format Markdown Text ────────────────────────────────────────── */
    function formatBotText(txt) {
        return txt
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>')
            .replace(/\*(.+?)\*/g, '<em>$1</em>')
            .replace(/\n/g, '<br>');
    }

    /* ── Append User Message (Clean Right-Aligned Bubble) ─────────────── */
    function appendUserMessage(text) {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        const row = document.createElement('div');
        row.className = 'cb-row usr';
        row.innerHTML = `
            <div class="cb-msg-box">
                <div class="cb-bbl-usr">${text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')}</div>
                <div class="cb-ts">${timeStr}</div>
            </div>`;
        body.appendChild(row);
        scrollToBottom();
    }

    /* ── Typing Dots Indicator ───────────────────────────────────────── */
    function showTypingIndicator() {
        removeTypingIndicator();
        const wrap = document.createElement('div');
        wrap.className = 'cb-typing-wrap';
        wrap.id = 'cbTyping';
        wrap.innerHTML = `
            <div class="cb-bot-av" style="width:30px;height:30px;border-radius:50%;background:#4f46e5;color:#fff;display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;">🤖</div>
            <div class="cb-typing-dots">
                <span></span><span></span><span></span>
            </div>`;
        body.appendChild(wrap);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        document.getElementById('cbTyping')?.remove();
    }

    /* ── Append Bot Message ──────────────────────────────────────────── */
    function appendBotMessage(reply, actions) {
        const now = new Date();
        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');

        let actionsHtml = '';
        if (actions && actions.length > 0) {
            actionsHtml = '<div class="cb-actions">';
            actions.forEach(act => {
                if (act.url) {
                    actionsHtml += `<a class="cb-action-btn" href="${act.url}">${act.label}</a>`;
                } else {
                    actionsHtml += `<span class="cb-action-btn">${act.label}</span>`;
                }
            });
            actionsHtml += '</div>';
        }

        const row = document.createElement('div');
        row.className = 'cb-row bot';
        row.innerHTML = `
            <div class="cb-bot-av">🤖</div>
            <div class="cb-msg-box">
                <div class="cb-bbl-bot">${formatBotText(reply)}${actionsHtml}</div>
                <div class="cb-ts">${timeStr}</div>
            </div>`;
        body.appendChild(row);
        scrollToBottom();
    }

    /* ── Scroll to Bottom ────────────────────────────────────────────── */
    function scrollToBottom() {
        body.scrollTo({ top: body.scrollHeight, behavior: 'smooth' });
    }

    /* ── Send Message Routine ────────────────────────────────────────── */
    function sendMessage() {
        const text = inp.value.trim();
        if (!text || isBusy) return;

        isBusy = true;
        appendUserMessage(text);

        inp.value = '';
        inp.style.height = 'auto';
        counter.textContent = '0';
        snd.classList.remove('active');

        // Sembunyikan chips setelah user mulai mengetik
        if (!hasTyped) {
            hasTyped = true;
            chips.style.display = 'none';
        }

        showTypingIndicator();

        fetch(CHAT_URL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': CSRF,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ message: text }),
        })
        .then(res => res.json())
        .then(data => {
            removeTypingIndicator();
            if (data.reply) {
                appendBotMessage(data.reply, data.actions || []);
            } else {
                appendBotMessage('Maaf, sistem tidak memberikan respon yang valid. Silakan coba lagi.', []);
            }
        })
        .catch(() => {
            removeTypingIndicator();
            appendBotMessage('Gagal terhubung ke server SFCS. Mohon periksa jaringanmu.', []);
        })
        .finally(() => {
            isBusy = false;
        });
    }

    snd.addEventListener('click', sendMessage);
    inp.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' && !e.shiftKey) {
            e.preventDefault();
            sendMessage();
        }
    });

    /* ── Quick Chips Click ───────────────────────────────────────────── */
    chips.querySelectorAll('.cb-chip-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            inp.value = this.dataset.msg;
            snd.classList.add('active');
            sendMessage();
        });
    });

})();
</script>
