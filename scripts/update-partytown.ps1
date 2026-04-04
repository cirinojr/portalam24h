param(
    [string]$Version = '0.10.3'
)

$ErrorActionPreference = 'Stop'

$repoRoot = (Resolve-Path (Join-Path $PSScriptRoot '..')).Path
$targetDir = Join-Path $repoRoot 'assets/vendor/partytown'
$tempDir = Join-Path ([System.IO.Path]::GetTempPath()) ('partytown-update-' + [Guid]::NewGuid().ToString('N'))
$preserve = @('README.md', 'VERSION')

try {
    New-Item -ItemType Directory -Path $tempDir -Force | Out-Null
    New-Item -ItemType Directory -Path $targetDir -Force | Out-Null

    $metaUrl = "https://unpkg.com/@builder.io/partytown@$Version/lib/?meta"
    $meta = Invoke-RestMethod -Uri $metaUrl

    if (-not $meta -or -not $meta.files) {
        throw "Unable to read file list from $metaUrl"
    }

    $files = @($meta.files | Where-Object { $_.path -like '/lib/*' })

    if ($files.Count -eq 0) {
        throw "No files found in Partytown lib for version $Version"
    }

    foreach ($file in $files) {
        $rel = $file.path.Substring('/lib/'.Length)
        $downloadUrl = "https://unpkg.com/@builder.io/partytown@$Version/lib/$rel"
        $destination = Join-Path $tempDir $rel
        $destinationDir = Split-Path -Parent $destination

        if (-not (Test-Path $destinationDir)) {
            New-Item -ItemType Directory -Path $destinationDir -Force | Out-Null
        }

        Invoke-WebRequest -Uri $downloadUrl -OutFile $destination
    }

    Get-ChildItem -Path $targetDir -Force | ForEach-Object {
        if ($preserve -notcontains $_.Name) {
            Remove-Item -Path $_.FullName -Recurse -Force
        }
    }

    Copy-Item -Path (Join-Path $tempDir '*') -Destination $targetDir -Recurse -Force
    Set-Content -Path (Join-Path $targetDir 'VERSION') -Value $Version -Encoding UTF8

    Write-Output "Partytown updated to version $Version in assets/vendor/partytown"
}
finally {
    if (Test-Path $tempDir) {
        Remove-Item -Path $tempDir -Recurse -Force
    }
}
