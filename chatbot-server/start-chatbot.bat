@echo off
title UJOL SmartExam AI Chatbot Server (Node.js)
echo ========================================================
echo   SmartExam AI (UJOL) Chatbot Server
echo   Engine: Universal AI (Nvidia NIM / Google Gemini / OpenAI)
echo   Port: 3001
echo ========================================================
echo.
cd /d "%~dp0"
node server.js
pause
