#!/bin/bash
# =============================================================================
# Security Script: Remove Malicious PHP Files
# =============================================================================
# Runs every second to delete potentially malicious PHP files uploaded
# to the storage and public directories.
# =============================================================================

echo "[Security] Starting PHP file cleanup daemon..."

while true; do
    # -------------------------------------------------------------------------
    # 1. Clean Storage (Safe Mode)
    # Excludes 'framework' and 'logs' which contain valid PHP/text files
    # -------------------------------------------------------------------------
    dir="/var/www/html/storage"
    if [ -d "$dir" ]; then
        find "$dir" -mindepth 2 -type f -iname "*.html*" ! -path "$dir/framework/*" ! -path "$dir/logs/*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.xml*"  ! -path "$dir/framework/*" ! -path "$dir/logs/*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.txt*"  ! -path "$dir/framework/*" ! -path "$dir/logs/*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.php*"  ! -path "$dir/framework/*" ! -path "$dir/logs/*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.pha*"  ! -path "$dir/framework/*" ! -path "$dir/logs/*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.pht*"  ! -path "$dir/framework/*" ! -path "$dir/logs/*" -exec rm -v {} \; 2>/dev/null

        find "$dir" -maxdepth 1 -type f -iname "*.html*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.xml*"  -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.txt*"  -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.php*"  -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.pha*"  -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.pht*"  -exec rm -v {} \; 2>/dev/null
    fi

    # -------------------------------------------------------------------------
    # 2. Clean Public (Aggressive Mode)
    # Deletes ALL PHP files except index.php
    # -------------------------------------------------------------------------
    dir="/var/www/html/public"
    if [ -d "$dir" ]; then
        find "$dir" -mindepth 2 -type f -iname "*.php*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.pha*" -exec rm -v {} \; 2>/dev/null
        find "$dir" -mindepth 2 -type f -iname "*.pht*" -exec rm -v {} \; 2>/dev/null

        find "$dir" -maxdepth 1 -type f -iname "*.php*" ! -iname "index.php" -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.pha*" ! -iname "index.php" -exec rm -v {} \; 2>/dev/null
        find "$dir" -maxdepth 1 -type f -iname "*.pht*" ! -iname "index.php" -exec rm -v {} \; 2>/dev/null
    fi

    sleep 1
done
