<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Hermes Agent Configuration
 * AI Bug Monitor & Telegram DevOps Assistant
 */
$config['hermes_telegram_token'] = '8918199149:AAFQOhXSdctb2G6yTmN9NRCkTIa-al4CQH8';
$config['hermes_bot_username']   = 'waapps_bot';

// Google Gemini API Configuration (Model Free Tier)
$config['hermes_gemini_api_key'] = 'AIzaSyC1e_cMdjNglsfO-JYhzsR9-kWQRvyllWg';
// Model rekomendasi gratis Google AI Studio: gemini-2.5-flash
$config['hermes_gemini_model']   = 'gemini-2.5-flash';

// Rate limit alert bug identik (dalam detik, misal 600 = 10 menit agar tidak spam)
$config['hermes_alert_throttle'] = 600;

// Path file penyimpanan Chat ID Admin penerima alert
$config['hermes_subscribers_file'] = APPPATH . 'cache/hermes_subscribers.json';
