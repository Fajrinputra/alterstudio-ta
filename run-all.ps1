# Jalankan backend (php artisan serve) dan frontend (npm run dev) dengan satu perintah.
# Gunakan dari root proyek: `.\run-all.ps1`

# Mulai Laravel backend
Start-Process -WindowStyle Minimized -WorkingDirectory $PSScriptRoot -FilePath "php" -ArgumentList "artisan serve"

# Tunggu sebentar sebelum start Vite
Start-Sleep -Seconds 2

# Mulai Vite dev server
Start-Process -WindowStyle Minimized -WorkingDirectory $PSScriptRoot -FilePath "npm" -ArgumentList "run dev"

Write-Host "Backend: http://127.0.0.1:8000  |  Frontend: http://localhost:5173/" -ForegroundColor Green
