@echo off
powershell.exe -NoProfile -ExecutionPolicy Bypass -File "%~dp0setup-aws-prep.ps1" %*
echo.
pause
