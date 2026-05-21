@echo off
setlocal

set "MYSQL_EXE=C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe"
set "DB_NAME=pinjol_db"
set "ROOT_DIR=%~dp0"
set "SCHEMA_FILE=%ROOT_DIR%database\schema\init.sql"
set "SEED_FILE=%ROOT_DIR%database\seeders\seed.sql"
set "SEED_ARTIKEL_FILE=%ROOT_DIR%database\seeders\seed_artikel.sql"

if not exist "%MYSQL_EXE%" (
  echo [ERROR] mysql.exe not found: %MYSQL_EXE%
  exit /b 1
)

if not exist "%SCHEMA_FILE%" (
  echo [ERROR] Schema file not found: %SCHEMA_FILE%
  exit /b 1
)

if not exist "%SEED_FILE%" (
  echo [ERROR] Seed file not found: %SEED_FILE%
  exit /b 1
)

if not exist "%SEED_ARTIKEL_FILE%" (
  echo [ERROR] Article seed file not found: %SEED_ARTIKEL_FILE%
  exit /b 1
)

echo.
echo ========================================
echo Resetting database %DB_NAME%
echo ========================================
"%MYSQL_EXE%" -u root -p -e "DROP DATABASE IF EXISTS %DB_NAME%; CREATE DATABASE %DB_NAME% CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;"
if errorlevel 1 goto :fail

echo.
echo ========================================
echo Importing schema
echo ========================================
"%MYSQL_EXE%" -u root -p %DB_NAME% < "%SCHEMA_FILE%"
if errorlevel 1 goto :fail

echo.
echo ========================================
echo Importing main seed
echo ========================================
"%MYSQL_EXE%" -u root -p %DB_NAME% < "%SEED_FILE%"
if errorlevel 1 goto :fail

echo.
echo ========================================
echo Importing article seed
echo ========================================
"%MYSQL_EXE%" -u root -p %DB_NAME% < "%SEED_ARTIKEL_FILE%"
if errorlevel 1 goto :fail

echo.
echo ========================================
echo Database seed completed successfully
echo ========================================
exit /b 0

:fail
echo.
echo [ERROR] Database seed failed.
exit /b 1
