@echo off
setlocal

cd /d "%~dp0"

if not exist ".env" (
    copy ".env.example" ".env" >nul
)

echo [1/6] Clearing Laravel caches...
php artisan optimize:clear
if errorlevel 1 goto :fail

echo [2/6] Ensuring app key exists...
php artisan key:generate --force
if errorlevel 1 goto :fail

echo [3/6] Running database migrations...
php artisan migrate --force
if errorlevel 1 goto :fail

echo [4/6] Ensuring storage link exists...
php artisan storage:link

echo [5/6] Installing frontend build if needed...
if not exist "public\build\manifest.json" (
    call npm install
    if errorlevel 1 goto :fail
    call npm run build
    if errorlevel 1 goto :fail
)

echo [6/6] Starting local server...
php artisan serve --host=0.0.0.0 --port=8000
goto :eof

:fail
echo.
echo Startup failed. Check PHP, Composer dependencies, Node.js, MySQL, and the .env database settings.
exit /b 1
