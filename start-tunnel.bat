@echo off
echo Starting Cloudflare Tunnel...
echo.
C:\cloudflared.exe tunnel --url http://localhost/agunan-capture
pause
