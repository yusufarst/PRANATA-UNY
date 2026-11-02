# Read-only source of one locked dependency for a step: the Owner's PC has no vendor/ or node_modules/, and gh is
# limited to this repository. Only the version in package-lock.json or composer.lock, from its locked URL, checked
# against the lock where the lock has a hash. The archive is extracted under .pranata/tools/src and never run.
param(
    [Parameter(Mandatory = $true)][string]$Package,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
if ($Package -notmatch '^(@[a-z0-9][a-z0-9._-]*/)?[a-z0-9][a-z0-9._-]*$' -and $Package -notmatch '^[a-z0-9][a-z0-9._-]*/[a-z0-9][a-z0-9._-]*$') {
    throw "Not a package name: $Package"
}

# PS 5.1 ConvertFrom-Json refuses the empty root key of package-lock.json; this parser does not.
Add-Type -AssemblyName System.Web.Extensions
function Read-Lock([string]$Path) {
    $parser = New-Object Web.Script.Serialization.JavaScriptSerializer
    $parser.MaxJsonLength = [int]::MaxValue
    return $parser.DeserializeObject([IO.File]::ReadAllText($Path))
}

$url = $null; $version = $null; $integrity = $null
$npmLock = Join-Path $Root 'package-lock.json'
if (Test-Path -LiteralPath $npmLock) {
    $packages = (Read-Lock $npmLock)['packages']
    if ($packages -and $packages.ContainsKey("node_modules/$Package")) {
        $entry = $packages["node_modules/$Package"]
        $url = $entry['resolved']; $version = $entry['version']; $integrity = $entry['integrity']
        if ($url -notlike 'https://registry.npmjs.org/*' -or $integrity -notmatch '^sha512-') { throw "Locked entry is not a registry tarball with sha512: $Package" }
    }
}
$composerLock = Join-Path $Root 'composer.lock'
if (-not $url -and (Test-Path -LiteralPath $composerLock)) {
    $lock = Read-Lock $composerLock
    $entry = @($lock['packages']) + @($lock['packages-dev']) | Where-Object { $_ -and $_['name'] -eq $Package } | Select-Object -First 1
    if ($entry) {
        $dist = $entry['dist']; $url = $dist['url']; $version = $entry['version']
        # GitHub dists carry no shasum; the locked commit in the URL pins the content instead.
        if ($url -notlike 'https://*' -or -not $dist['reference'] -or $url -notlike "*$($dist['reference'])*") { throw "Locked dist does not pin its commit: $Package" }
        if ($dist['shasum']) { $integrity = 'sha1-' + $dist['shasum'] }
    }
}
if (-not $url) { throw "Not a locked dependency of this repository: $Package" }

$dest = Join-Path $Root (".pranata\tools\src\" + ($Package -replace '/', '__') + "@$version")
if (Test-Path -LiteralPath $dest) { Write-Output $dest; exit 0 }
$part = "$dest.part"
if (Test-Path -LiteralPath $part) { Remove-Item -LiteralPath $part -Recurse -Force }
[void](New-Item -ItemType Directory -Path $part -Force)
$archive = Join-Path $part 'archive'
try {
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $ProgressPreference = 'SilentlyContinue'
    Invoke-WebRequest -Uri $url -OutFile $archive -UseBasicParsing -MaximumRedirection 5
    if ((Get-Item -LiteralPath $archive).Length -gt 50MB) { throw "Archive over 50 MB: $Package" }
    if ($integrity) {
        $algorithm, $expected = $integrity -split '-', 2
        $hasher = [Security.Cryptography.HashAlgorithm]::Create($algorithm.ToUpper())
        $stream = [IO.File]::OpenRead($archive)
        try { $hash = $hasher.ComputeHash($stream) } finally { $stream.Dispose() }
        $actual = if ($algorithm -eq 'sha1') { -join ($hash | ForEach-Object { $_.ToString('x2') }) } else { [Convert]::ToBase64String($hash) }
        if ($actual -ne $expected) { throw "Integrity mismatch for $Package@$version; nothing extracted" }
    }
    # Windows tar (bsdtar) reads tgz and zip and refuses absolute paths and '..' entries.
    $src = Join-Path $part 'src'
    [void](New-Item -ItemType Directory -Path $src)
    & "$env:SystemRoot\System32\tar.exe" -xf $archive -C $src
    if ($LASTEXITCODE -ne 0) { throw "Extraction failed: $Package" }
    Remove-Item -LiteralPath $archive
    Rename-Item -LiteralPath $part -NewName (Split-Path $dest -Leaf)
} finally {
    # A failed fetch leaves nothing a later step could mistake for the source.
    if (Test-Path -LiteralPath $part) { Remove-Item -LiteralPath $part -Recurse -Force }
}
Write-Output $dest
