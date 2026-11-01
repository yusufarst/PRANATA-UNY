# Owner-only (D-27). Stores the restricted runner token, DPAPI-encrypted for this Windows user, outside the repository.
# Run it yourself in a terminal; the value is typed at a hidden prompt and never appears in a command line or log.
param()
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if ($env:PRANATA_EXECUTOR_MODE -eq 'AUTONOMOUS') { throw 'Token installation is Owner-only.' }
$secure = Read-Host -AsSecureString 'Tempel token runner terbatas (tidak ditampilkan)'
$path = Get-PranataTokenPath
$dir = Split-Path -Parent $path
if (-not (Test-Path -LiteralPath $dir)) { [void](New-Item -ItemType Directory -Path $dir -Force) }
Write-PranataFileAtomic $path (ConvertFrom-SecureString $secure)
Write-Output "Token tersimpan terenkripsi di $path"
