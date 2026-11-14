@echo off
cd /d "%~dp0public"
php -d extension=gd -d upload_max_filesize=4M -d post_max_size=8M -S 127.0.0.1:8000 ..\vendor\laravel\framework\src\Illuminate\Foundation\resources\server.php
