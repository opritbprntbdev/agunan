@echo off
title Cloudflare Tunnel - Auto Restart
color 0A
echo ================================================
echo   CLOUDFLARE TUNNEL - AUTO RESTART MODE
echo ================================================
echo.
echo Script ini akan menjaga tunnel tetap hidup.
echo Jika tunnel mati, otomatis restart.
echo.
echo Tekan Ctrl+C untuk stop tunnel.
echo ================================================
echo.

:start
echo [%date% %time%] Starting Cloudflare Tunnel...
C:\cloudflared.exe tunnel --no-autoupdate --url http://localhost:80

echo.
echo [%date% %time%] Tunnel stopped. Restarting in 3 seconds...
timeout /t 3 /nobreak >nul
goto start
