# Read-only source of one locked dependency for a step: the Owner's PC has no vendor/ or node_modules/, and gh is
# limited to this repository. Only the version in package-lock.json or composer.lock, from its locked URL, checked
# against the lock where the lock has a hash. The archive is extracted under .pranata/tools/src and never run.
param(
    [Parameter(Mandatory = $true)][string]$Package,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
$repoRoot = Get-PranataRepoRoot
if (-not $Root) { $Root = $repoRoot }
$Root = [IO.Path]::GetFullPath($Root).TrimEnd('\')
# Any other root (say .claude) would put the output in a guardrail path; only the offline tests use another one.
if ($Root -ne $repoRoot -and $env:PRANATA_FETCH_SOURCE_TEST -ne '1') { throw "Root must be the repository root: $Root" }
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
        # The GitHub slug often differs from the package name (Bacon/BaconQrCode), so only the commit is compared.
        if ([string]$dist['reference'] -notmatch '^[0-9a-f]{40}$' -or $url -notmatch '^https://api\.github\.com/repos/[A-Za-z0-9_.-]+/[A-Za-z0-9_.-]+/zipball/([0-9a-f]{40})$' -or $Matches[1] -ne $dist['reference']) {
            throw "Locked dist is not a GitHub zipball pinned to its commit: $Package"
        }
        if ($dist['shasum']) { $integrity = 'sha1-' + $dist['shasum'] }
    }
}
if (-not $url) { throw "Not a locked dependency of this repository: $Package" }
if ($version -notmatch '^v?[0-9][A-Za-z0-9.+-]*$') { throw "Not a release version: $Package $version" }

$base = [IO.Path]::GetFullPath((Join-Path $Root '.pranata\tools\src'))
$dest = [IO.Path]::GetFullPath((Join-Path $base (($Package -replace '/', '__') + "@$version")))
if (-not $dest.StartsWith($base + '\', [StringComparison]::OrdinalIgnoreCase)) { throw "Destination escapes .pranata/tools/src: $dest" }
$marker = Join-Path $dest 'FETCHED'
# Only a folder this script finished counts; anything else at that path is fetched again.
if ((Test-Path -LiteralPath $marker) -and (Get-Content -LiteralPath $marker -Raw).Trim() -eq "$url $integrity".Trim()) { Write-Output $dest; exit 0 }
if (Test-Path -LiteralPath $dest) { Remove-Item -LiteralPath $dest -Recurse -Force }
$part = "$dest.part"
if (Test-Path -LiteralPath $part) { Remove-Item -LiteralPath $part -Recurse -Force }
[void](New-Item -ItemType Directory -Path $part -Force)
$archive = Join-Path $part 'archive'
try {
    # Redirects are followed by hand: https only, and only to the registry or GitHub's archive hosts.
    [Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12
    $next = [Uri]$url
    for ($hop = 0; ; $hop++) {
        if ($hop -gt 5 -or $next.Scheme -ne 'https' -or $next.Host -notin @('registry.npmjs.org', 'api.github.com', 'codeload.github.com')) {
            throw "Refused download location: $next"
        }
        $request = [Net.HttpWebRequest]::Create($next)
        $request.AllowAutoRedirect = $false
        $request.UserAgent = 'pranata-fetch-source'
        $response = $request.GetResponse()
        $status = [int]$response.StatusCode
        if ($status -ge 300 -and $status -lt 400) {
            $next = New-Object Uri($next, $response.Headers['Location'])
            $response.Close()
            continue
        }
        break
    }
    $in = $response.GetResponseStream()
    $out = [IO.File]::Create($archive)
    try {
        $buffer = New-Object byte[] 65536
        $total = 0
        while (($read = $in.Read($buffer, 0, $buffer.Length)) -gt 0) {
            $total += $read
            if ($total -gt 50MB) { throw "Archive over 50 MB: $Package" }
            $out.Write($buffer, 0, $read)
        }
    } finally { $out.Dispose(); $response.Close() }
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
    Set-Content -LiteralPath (Join-Path $part 'FETCHED') -Value "$url $integrity".Trim() -Encoding ASCII
    Rename-Item -LiteralPath $part -NewName (Split-Path $dest -Leaf)
} finally {
    # A failed fetch leaves nothing a later step could mistake for the source.
    if (Test-Path -LiteralPath $part) { Remove-Item -LiteralPath $part -Recurse -Force }
}
Write-Output $dest
