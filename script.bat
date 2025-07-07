@echo off
:: Verificar si XAMPP está corriendo
tasklist /FI "IMAGENAME eq httpd.exe" | find /I "httpd.exe" >nul
if %ERRORLEVEL% equ 0 (
    echo XAMPP ya está corriendo.
    :: Ejecutar el script PHP solo si XAMPP ya está corriendo
    "C:\xampp\php\php.exe" "C:\xampp\htdocs\SERAMER-PLATFORM\script.php"
) else (
    echo Iniciando XAMPP...
    start "" "C:\xampp\xampp-control.exe"
    timeout /t 20 /nobreak >nul
    echo XAMPP ha sido iniciado, pero el script PHP no se ejecutará hasta que lo ejecutes manualmente.
)
