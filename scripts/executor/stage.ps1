# Byte-safe staging (GOV-09) on a machine with global core.autocrlf=true: the working copy of an LF blob is CRLF,
# so staging it with core.autocrlf=false would rewrite every line. Explicit paths only; the blob's LF form is restored first.
param(
    [Parameter(Mandatory = $true)][string[]]$Paths,
    [string]$Root
)
$ErrorActionPreference = 'Stop'
. (Join-Path $PSScriptRoot 'lib\state.ps1')
if (-not $Root) { $Root = Get-PranataRepoRoot }
# powershell -File passes a comma list as one string.
$Paths = @($Paths | ForEach-Object { $_ -split ',' } | Where-Object { $_ })
$mode = Get-PranataEffectiveMode $Root

foreach ($path in $Paths) {
    if ($path -match '[*?\[\]]|^:') { throw "Explicit file paths only, no patterns: $path" }
    $relative = $path -replace '\\', '/'
    if ($relative -match '(^|/)\.\.(/|$)|^/|^[A-Za-z]:') { throw "Repository-relative paths only: $path" }
    # Section 11: guardrail paths stay read-only in AUTONOMOUS mode, also through this script.
    if ($mode -eq 'AUTONOMOUS' -and ($relative -cmatch $script:GuardPathRegex -or $relative -match '^\.pranata/')) {
        throw "Guardrail path is read-only in AUTONOMOUS mode: $path"
    }
    $full = Join-Path $Root $path
    if (Test-Path -LiteralPath $full -PathType Container) { throw "Explicit file paths only, not a folder: $path" }
    # Literal pathspecs: Git must never expand anything passed here.
    $literal = ":(literal)$path"
    if (-not (Test-Path -LiteralPath $full)) {
        & git -C $Root rm --cached --quiet --ignore-unmatch -- $literal
        continue
    }
    $eol = (& git -C $Root ls-files --eol --cached --others --exclude-standard -- $literal) | Select-Object -First 1
    if (-not $eol) { throw "Ignored by Git, not staged: $path" }
    if ($eol -notmatch '^i/(\S*)\s+w/(\S*)') { throw "Unexpected eol report: $eol" }
    $index = $Matches[1]
    $work = $Matches[2]
    # A mixed blob stays byte for byte as it is; binary files are never touched.
    if ($index -in @('', 'lf') -and $work -in @('crlf', 'mixed')) {
        $bytes = [IO.File]::ReadAllBytes($full)
        $output = New-Object System.Collections.Generic.List[byte]($bytes.Length)
        for ($i = 0; $i -lt $bytes.Length; $i++) {
            if ($bytes[$i] -eq 13 -and $i + 1 -lt $bytes.Length -and $bytes[$i + 1] -eq 10) { continue }
            $output.Add($bytes[$i])
        }
        [IO.File]::WriteAllBytes($full, $output.ToArray())
    }
    & git -c core.autocrlf=false -C $Root add -- $literal
    if ($LASTEXITCODE -ne 0) { throw "git add failed: $path" }
}
& git -C $Root diff --cached --stat
