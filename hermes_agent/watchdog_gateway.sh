#!/bin/bash
export RAYON_NUM_THREADS=1
export UV_CONCURRENT_DOWNLOADS=1
export GOMAXPROCS=1
export PATH=/home/wabotweb/.local/bin:/usr/local/bin:/usr/bin:$PATH

if ! /home/wabotweb/.local/bin/hermes gateway status 2>/dev/null | grep -q "Gateway is running"; then
    echo "$(date): Gateway not running, restarting..." >> /home/wabotweb/.hermes/logs/watchdog.log
    nohup /home/wabotweb/.local/bin/hermes gateway run >> /home/wabotweb/.hermes/logs/gateway.log 2>&1 &
fi
