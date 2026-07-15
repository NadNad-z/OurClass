# Setup script for Laragon local development

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host 'Setting up OurClass for Laragon local development...' -ForegroundColor Green

Push-Location $projectRoot

if (-Not (Test-Path '.env')) {
    Copy-Item '.env.example' '.env'
    Write-Host '.env file created from .env.example' -ForegroundColor Green
}

Write-Host 'Installing Composer dependencies...' -ForegroundColor Green
composer install

Write-Host 'Installing NPM dependencies...' -ForegroundColor Green
npm install

Write-Host 'Generating application key...' -ForegroundColor Green
php artisan key:generate

Write-Host 'Creating storage link...' -ForegroundColor Green
php artisan storage:link

Write-Host 'Creating SQLite database file if missing...' -ForegroundColor Green
if (-Not (Test-Path 'database\database.sqlite')) {
    New-Item -Path 'database\database.sqlite' -ItemType File | Out-Null
    Write-Host 'Created database\database.sqlite' -ForegroundColor Green
}

Write-Host 'Running migrations and seeders...' -ForegroundColor Green
php artisan migrate:fresh --seed

Write-Host 'Laragon setup complete. Run the app from Laragon or use php artisan serve.' -ForegroundColor Green
Pop-Location
