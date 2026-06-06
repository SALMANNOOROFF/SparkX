@echo off
title SparkX Cron Automator
color 0B
echo =================================================================
echo             SPARKX AUTOMATED CRON RUNNER (EVERY 60 SECONDS)
echo =================================================================
echo.
echo [INFO] Is window ko minimized/open rakhein taake profit automatically har 1 minute baad generate hota rahe.
echo [INFO] Close krne pr automatic profit ruk jayega.
echo.

:loop
echo [%time%] ⚡ Running cron.php...
powershell -Command "$resp = Invoke-WebRequest -Uri 'http://localhost/sparkx1/cron.php' -UseBasicParsing; $resp.Content"
echo.
echo [%time%] ✓ Success. Waiting 60 seconds before next run...
echo -----------------------------------------------------------------
timeout /t 60 /nobreak > nul
goto loop
