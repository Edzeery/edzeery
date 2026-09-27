$ErrorActionPreference = "Stop"
$php83 = "C:\laragon\bin\php\php-8.3.28-Win32-vs16-x64\php.exe"
$dir   = "tmp_isolated_run_" + (Get-Date -Format "HHmmss")
New-Item -ItemType Directory -Path $dir -Force | Out-Null
$junit = Join-Path $dir "junit.xml"
$log   = Join-Path $dir "output.txt"

Push-Location "C:\laragon\www\edzeery"
try {
    & $php83 "vendor\bin\pest" `
        "tests\Feature\Merchant" `
        "tests\Feature\Merchant\StoreSubscriptionExpiryGatingTest.php" `
        "--log-junit=$junit" 2>&1 | Tee-Object -FilePath $log | Select-Object -Last 12
    Write-Output ("exit:{0}" -f $LASTEXITCODE)
} finally {
    Pop-Location
}
Write-Output ("junit at: {0}" -f (Resolve-Path $junit))
Write-Output ("log  at: {0}" -f (Resolve-Path $log))