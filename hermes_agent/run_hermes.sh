#!/bin/bash
# Runner script untuk Cron Job cPanel
cd /home/wabotweb/hermes_agent || exit 1
/opt/alt/python311/bin/python3 hermes_monitor.py
