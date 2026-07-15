# PowerShell helper script to prepare local testing environment

$projectRoot = Split-Path -Parent $MyInvocation.MyCommand.Path

Write-Host 'Creating SQLite test database file if missing...' -ForegroundColor Green
$dbPath = Join-Path $projectRoot 'database\database.sqlite'
if (-Not (Test-Path $dbPath)) {
    New-Item -Path $dbPath -ItemType File | Out-Null
    Write-Host "Created $dbPath" -ForegroundColor Green
} else {
    Write-Host "SQLite file already exists: $dbPath" -ForegroundColor Yellow
}

Write-Host 'Running migrations and seeders...' -ForegroundColor Green
Push-Location $projectRoot
php artisan migrate:fresh --seed
Pop-Location

Write-Host 'Done. You can now run:' -ForegroundColor Green
Write-Host 'php artisan test --testsuite=Feature'
