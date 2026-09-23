@echo off
echo ========================================
echo MS Harmony - AI Recolor Service
echo Port: http://127.0.0.1:5000
echo ========================================
cd /d %~dp0
python python/recolor_service.py
pause
