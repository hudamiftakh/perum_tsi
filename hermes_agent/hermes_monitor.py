#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Hermes Agent - Python Bug Monitor & AI DevOps Assistant
Perum TSI (CodeIgniter 3 + cPanel)

Fitur:
- Membaca error_log server dan application/logs/ CodeIgniter secara berkala (incremental offset)
- Menganalisis error baru secara cerdas menggunakan Google Gemini Flash (Free Tier)
- Mengirimkan laporan bug rapi + solusi perbaikan ke Telegram (@waapps_bot)
- Zero external dependencies (hanya memakai library standar Python 3)
- Anti-spam / rate-limiting untuk error berulang
"""

import os
import sys
import json
import time
import re
import glob
import urllib.request
import urllib.parse
import urllib.error
from datetime import datetime

# Path direktori script
BASE_DIR = os.path.dirname(os.path.abspath(__file__))
CONFIG_FILE = os.path.join(BASE_DIR, "config.json")
STATE_FILE = os.path.join(BASE_DIR, "state.json")

# Default Konfigurasi
DEFAULT_CONFIG = {
    "telegram_token": "8918199149:AAFQOhXSdctb2G6yTmN9NRCkTIa-al4CQH8",
    "gemini_api_key": "AIzaSyC1e_cMdjNglsfO-JYhzsR9-kWQRvyllWg",
    "gemini_model": "gemini-2.5-flash",
    "chat_ids": [],
    "web_root": "/home/wabotweb/perumtsi.wabot.web.id",
    "throttle_seconds": 600,
    "max_lines_per_error": 30
}


def load_config():
    if not os.path.exists(CONFIG_FILE):
        with open(CONFIG_FILE, "w", encoding="utf-8") as f:
            json.dump(DEFAULT_CONFIG, f, indent=4)
        return DEFAULT_CONFIG
    try:
        with open(CONFIG_FILE, "r", encoding="utf-8") as f:
            cfg = json.load(f)
            # Merge defaults
            for k, v in DEFAULT_CONFIG.items():
                if k not in cfg:
                    cfg[k] = v
            return cfg
    except Exception as e:
        print(f"[!] Gagal membaca config.json: {e}")
        return DEFAULT_CONFIG


def save_config(cfg):
    with open(CONFIG_FILE, "w", encoding="utf-8") as f:
        json.dump(cfg, f, indent=4)


def load_state():
    if not os.path.exists(STATE_FILE):
        return {"offsets": {}, "alerted_hashes": {}}
    try:
        with open(STATE_FILE, "r", encoding="utf-8") as f:
            return json.load(f)
    except Exception:
        return {"offsets": {}, "alerted_hashes": {}}


def save_state(state):
    with open(STATE_FILE, "w", encoding="utf-8") as f:
        json.dump(state, f, indent=4)


# ==========================================
# TELEGRAM API FUNCTIONS
# ==========================================

def get_telegram_updates(token):
    """Mengambil pesan masuk dari bot untuk mendeteksi Chat ID pengguna"""
    url = f"https://api.telegram.org/bot{token}/getUpdates"
    try:
        req = urllib.request.Request(url, headers={"User-Agent": "HermesAgent/1.0"})
        with urllib.request.urlopen(req, timeout=10) as resp:
            data = json.loads(resp.read().decode("utf-8"))
            if data.get("ok"):
                return data.get("result", [])
    except Exception as e:
        print(f"[!] Gagal mengambil getUpdates: {e}")
    return []


def sync_subscribers(cfg):
    """Mendeteksi chat_id dari pengguna yang chat /start ke bot"""
    token = cfg["telegram_token"]
    updates = get_telegram_updates(token)
    new_chats = set(cfg.get("chat_ids", []))
    added = False

    for item in updates:
        msg = item.get("message") or item.get("edited_message")
        if msg and "chat" in msg:
            cid = str(msg["chat"]["id"])
            if cid not in new_chats:
                new_chats.add(cid)
                added = True
                print(f"[+] Ditemukan Subscriber Baru: Chat ID {cid} ({msg['chat'].get('first_name', 'User')})")

    if added:
        cfg["chat_ids"] = list(new_chats)
        save_config(cfg)
        print(f"[*] Total subscriber aktif: {len(cfg['chat_ids'])}")
    return cfg["chat_ids"]


def send_telegram(token, chat_id, text, parse_mode="HTML"):
    """Mengirim pesan ke Telegram"""
    url = f"https://api.telegram.org/bot{token}/sendMessage"
    payload = {
        "chat_id": chat_id,
        "text": text,
        "parse_mode": parse_mode,
        "disable_web_page_preview": True
    }
    data = urllib.parse.urlencode(payload).encode("utf-8")
    try:
        req = urllib.request.Request(url, data=data, headers={"User-Agent": "HermesAgent/1.0"})
        with urllib.request.urlopen(req, timeout=10) as resp:
            return json.loads(resp.read().decode("utf-8"))
    except Exception as e:
        print(f"[!] Gagal kirim Telegram ke {chat_id}: {e}")
        return None


def broadcast_telegram(cfg, text):
    """Kirim pesan ke seluruh Chat ID terdaftar"""
    chat_ids = cfg.get("chat_ids", [])
    if not chat_ids:
        # Coba sync dari bot updates dulu
        chat_ids = sync_subscribers(cfg)

    if not chat_ids:
        print("[!] Belum ada chat_id terdaftar. Silakan kirim /start ke bot di Telegram terlebih dahulu!")
        return False

    success = False
    for cid in chat_ids:
        res = send_telegram(cfg["telegram_token"], cid, text)
        if res and res.get("ok"):
            success = True
    return success


# ==========================================
# GEMINI AI FUNCTIONS (FREE TIER)
# ==========================================

def ask_gemini(api_key, model, error_snippet, prompt_context=""):
    """Memanggil Gemini AI (Free Tier gemini-1.5-flash) untuk analisa bug"""
    url = f"https://generativelanguage.googleapis.com/v1beta/models/{model}:generateContent?key={api_key}"
    
    system_instruction = (
        "Anda adalah 'Hermes Agent', asisten DevOps & Code Auditor ahli untuk aplikasi "
        "Manajemen Perum TSI (PHP CodeIgniter 3 + MySQL di server Linux/cPanel).\n"
        "Tugas Anda: Menganalisa potongan error_log berikut secara ringkas, to-the-point, dan berikan solusi kode yang jelas.\n\n"
        "Format Respon Wajib:\n"
        "📌 Masalah Utama: (1-2 kalimat)\n"
        "📁 File & Baris: (lokasi file dan baris penyebab)\n"
        "🔍 Penyebab: (mengapa error ini terjadi)\n"
        "💡 Solusi Konkret: (langkah atau potongan kode PHP/SQL untuk memperbaikinya)"
    )

    full_prompt = f"{system_instruction}\n\nPotongan Error Log:\n{error_snippet}"
    if prompt_context:
        full_prompt += f"\n\nKonteks Tambahan:\n{prompt_context}"

    payload = {
        "contents": [
            {
                "parts": [{"text": full_prompt}]
            }
        ],
        "generationConfig": {
            "temperature": 0.2,
            "maxOutputTokens": 600
        }
    }

    try:
        data = json.dumps(payload).encode("utf-8")
        req = urllib.request.Request(
            url,
            data=data,
            headers={"Content-Type": "application/json", "User-Agent": "HermesAgent/1.0"}
        )
        with urllib.request.urlopen(req, timeout=15) as resp:
            res_json = json.loads(resp.read().decode("utf-8"))
            candidates = res_json.get("candidates", [])
            if candidates:
                parts = candidates[0].get("content", {}).get("parts", [])
                if parts:
                    return parts[0].get("text", "").strip()
            if "error" in res_json:
                return f"[Gemini API Error] {res_json['error'].get('message', '')}"
    except urllib.error.HTTPError as e:
        err_msg = e.read().decode("utf-8", errors="ignore")
        return f"[HTTP {e.code}] {err_msg[:200]}"
    except Exception as e:
        return f"[Error Koneksi AI] {e}"
    
    return "Tidak dapat menghasilkan analisa dari Gemini AI."


# ==========================================
# LOG PARSING & MONITORING
# ==========================================

ERROR_PATTERNS = [
    re.compile(r"Fatal error:", re.IGNORECASE),
    re.compile(r"Parse error:", re.IGNORECASE),
    re.compile(r"Uncaught Exception", re.IGNORECASE),
    re.compile(r"Uncaught Error", re.IGNORECASE),
    re.compile(r"Severity:\s*(?:Error|Warning|Fatal)", re.IGNORECASE),
    re.compile(r"Database Error:", re.IGNORECASE),
    re.compile(r"A Database Error Occurred", re.IGNORECASE),
    re.compile(r"Maximum execution time of \d+ seconds exceeded", re.IGNORECASE),
    re.compile(r"Allowed memory size of \d+ bytes exhausted", re.IGNORECASE)
]


def is_error_line(line):
    for pattern in ERROR_PATTERNS:
        if pattern.search(line):
            return True
    return False


def get_log_files(web_root):
    """Mencari file error_log dan CI log terbaru"""
    files = []
    
    # 1. Root error_log
    root_log = os.path.join(web_root, "error_log")
    if os.path.exists(root_log):
        files.append(("apache_php", root_log))

    # 2. Public_html / perumtsi error_log
    public_log = os.path.join(web_root, "public_html", "error_log")
    if os.path.exists(public_log):
        files.append(("apache_php", public_log))

    # 3. CI logs: application/logs/log-YYYY-MM-DD.php
    ci_log_pattern = os.path.join(web_root, "application", "logs", "log-*.php")
    ci_files = sorted(glob.glob(ci_log_pattern))
    if ci_files:
        # Ambil log hari ini / paling baru
        files.append(("codeigniter", ci_files[-1]))

    return files


def scan_file_incremental(filepath, last_offset):
    """Membaca isi file hanya dari offset terakhir"""
    if not os.path.exists(filepath):
        return [], 0

    current_size = os.path.getsize(filepath)
    if current_size < last_offset:
        # File di-truncate atau dirotasi
        last_offset = 0

    if current_size == last_offset:
        return [], current_size

    new_lines = []
    with open(filepath, "r", encoding="utf-8", errors="ignore") as f:
        f.seek(last_offset)
        new_lines = f.readlines()
        new_offset = f.tell()

    return new_lines, new_offset


def group_error_blocks(lines, max_lines=25):
    """Mengelompokkan baris error menjadi blok utuh beserta stack trace"""
    blocks = []
    current_block = []
    capturing = False

    for line in lines:
        if is_error_line(line):
            if current_block:
                blocks.append("".join(current_block[:max_lines]))
                current_block = []
            capturing = True
            current_block.append(line)
        elif capturing:
            # Lanjutkan capture jika ini adalah stack trace (#0, Stack trace, dll)
            if re.match(r"^\s*(#\d+|Stack trace|Thrown in|in /|--|An uncaught Exception)", line) or line.strip() == "":
                current_block.append(line)
                if len(current_block) >= max_lines:
                    blocks.append("".join(current_block))
                    current_block = []
                    capturing = False
            else:
                # Akhir dari blok error
                blocks.append("".join(current_block[:max_lines]))
                current_block = []
                capturing = False

    if current_block:
        blocks.append("".join(current_block[:max_lines]))

    return blocks


def clean_html(text):
    """Membersihkan karakter HTML agar aman dikirim ke Telegram"""
    return text.replace("&", "&amp;").replace("<", "&lt;").replace(">", "&gt;")


def process_monitoring(cfg):
    """Menjalankan satu siklus monitoring bug"""
    state = load_state()
    offsets = state.get("offsets", {})
    alerted = state.get("alerted_hashes", {})
    now = time.time()
    throttle = cfg.get("throttle_seconds", 600)

    # Bersihkan hash yang sudah kadaluarsa
    alerted = {h: ts for h, ts in alerted.items() if (now - ts) < throttle}

    log_files = get_log_files(cfg["web_root"])
    total_new_bugs = 0

    for log_type, filepath in log_files:
        last_offset = offsets.get(filepath, 0)
        lines, new_offset = scan_file_incremental(filepath, last_offset)
        offsets[filepath] = new_offset

        if not lines:
            continue

        error_blocks = group_error_blocks(lines, cfg.get("max_lines_per_error", 25))

        for block in error_blocks:
            # Hitung hash unik error untuk anti-spam
            error_hash = re.sub(r"\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}", "", block)
            error_hash = re.sub(r"\[.*?\]", "", error_hash)
            h = f"{log_type}:{hash(error_hash.strip()[:150])}"

            if h in alerted:
                continue # Skip karena baru saja dilaporkan

            alerted[h] = now
            total_new_bugs += 1

            print(f"[*] Terdeteksi Bug Baru pada {os.path.basename(filepath)}! Meminta analisa Gemini...")
            ai_analysis = ask_gemini(
                cfg["gemini_api_key"],
                cfg["gemini_model"],
                block[:1500],
                f"Sumber: {log_type} ({os.path.basename(filepath)})"
            )

            # Format Pesan Telegram
            waktu = datetime.now().strftime("%d-%m-%Y %H:%M:%S WIB")
            msg  = "🚨 <b>[HERMES BUG ALERT - PERUM TSI]</b> 🚨\n"
            msg += "━━━━━━━━━━━━━━━━━━━━\n"
            msg += f"⏰ <b>Waktu:</b> <code>{waktu}</code>\n"
            msg += f"📁 <b>Sumber Log:</b> <code>{clean_html(os.path.basename(filepath))}</code>\n\n"
            msg += "💥 <b>Cuplikan Error:</b>\n"
            msg += f"<pre>{clean_html(block[:450])}</pre>\n\n"
            msg += "🤖 <b>Analisa & Solusi Hermes AI (Gemini Flash):</b>\n"
            msg += f"{clean_html(ai_analysis)}\n"
            msg += "━━━━━━━━━━━━━━━━━━━━\n"
            msg += "<i>Hermes Agent memantau log secara real-time via Cron.</i>"

            broadcast_telegram(cfg, msg)

    state["offsets"] = offsets
    state["alerted_hashes"] = alerted
    save_state(state)
    return total_new_bugs


# ==========================================
# CLI ENTRY POINT
# ==========================================

def main():
    cfg = load_config()

    if len(sys.argv) > 1:
        cmd = sys.argv[1].lower()

        if cmd in ("--init", "init"):
            print("=== Inisialisasi Hermes Agent ===")
            sync_subscribers(cfg)
            # Inisialisasi offset ke akhir file agar tidak spam log lama
            state = load_state()
            offsets = {}
            for _, fpath in get_log_files(cfg["web_root"]):
                if os.path.exists(fpath):
                    offsets[fpath] = os.path.getsize(fpath)
                    print(f"[*] Log disinkronkan: {fpath} (Offset: {offsets[fpath]} bytes)")
            state["offsets"] = offsets
            save_state(state)
            print("[✓] Inisialisasi selesai. Hermes siap memantau bug baru.")
            return

        elif cmd in ("--test", "test"):
            print("=== Menguji Koneksi Telegram & Gemini AI ===")
            sync_subscribers(cfg)
            dummy_error = (
                "PHP Fatal error: Uncaught Error: Call to undefined function ensure_log_tables_exist() "
                "in /home/wabotweb/perumtsi.wabot.web.id/application/views/dashboard/log_login.php:1\n"
                "Stack trace:\n#0 /home/wabotweb/perumtsi.wabot.web.id/system/core/Loader.php(871): include()\n"
                "#1 /home/wabotweb/perumtsi.wabot.web.id/application/controllers/Dashboard.php(75): CI_Loader->view()"
            )
            print("[*] Mengirim simulasi error ke Gemini Flash...")
            ai_res = ask_gemini(cfg["gemini_api_key"], cfg["gemini_model"], dummy_error)
            print("[*] Respon Gemini:")
            print(ai_res)
            print("[*] Mengirimkan notifikasi ke Telegram...")
            waktu = datetime.now().strftime("%d-%m-%Y %H:%M:%S WIB")
            test_msg = (
                "🧪 <b>[UJI COBA HERMES MONITORING AKTIF]</b> 🧪\n"
                "━━━━━━━━━━━━━━━━━━━━\n"
                f"⏰ <b>Waktu:</b> <code>{waktu}</code>\n"
                "🤖 <b>Status:</b> Hermes Agent Python berhasil terhubung!\n"
                f"🧠 <b>Model AI:</b> <code>{cfg['gemini_model']} (Free Tier)</code>\n\n"
                "💥 <b>Simulasi Error:</b>\n"
                f"<pre>{clean_html(dummy_error[:250])}...</pre>\n\n"
                "🤖 <b>Contoh Diagnosa AI:</b>\n"
                f"{clean_html(ai_res)}\n"
                "━━━━━━━━━━━━━━━━━━━━\n"
                "✅ <i>Jika pesan ini masuk di Telegram, sistem pemantau bug otomatis telah 100% siap!</i>"
            )
            if broadcast_telegram(cfg, test_msg):
                print("[✓] Sukses! Pesan telah terkirim ke Telegram.")
            else:
                print("[!] Gagal mengirim pesan ke Telegram. Pastikan Anda sudah kirim /start ke bot @waapps_bot")
            return

        elif cmd in ("--sync", "sync"):
            print("=== Menyinkronkan Subscriber Telegram ===")
            subs = sync_subscribers(cfg)
            print(f"Chat IDs terdaftar: {subs}")
            return

        elif cmd in ("--add-chat", "add-chat") and len(sys.argv) > 2:
            cid = sys.argv[2]
            chats = set(cfg.get("chat_ids", []))
            chats.add(cid)
            cfg["chat_ids"] = list(chats)
            save_config(cfg)
            print(f"[✓] Chat ID {cid} berhasil ditambahkan!")
            return

    # Default action: run monitoring check (untuk cron)
    bugs = process_monitoring(cfg)
    if bugs > 0:
        print(f"[!] {bugs} bug baru terdeteksi dan dikirim ke Telegram.")
    else:
        # Silent pada cron jika tidak ada bug
        pass


if __name__ == "__main__":
    main()
