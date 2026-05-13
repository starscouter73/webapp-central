param()

$ErrorActionPreference = 'Stop'

$localAlias = 'webapp-central.test'
$localPort = 8080

function Test-Administrator {
    $identity = [Security.Principal.WindowsIdentity]::GetCurrent()
    $principal = New-Object Security.Principal.WindowsPrincipal($identity)
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

if (-not (Test-Administrator)) {
    throw 'setup-lan.ps1 muss als Administrator ausgefuehrt werden.'
}

$hostsPath = Join-Path $env:WINDIR 'System32\drivers\etc\hosts'
$currentIp = (
    Get-NetIPAddress -AddressFamily IPv4 |
    Where-Object { $_.IPAddress -like '192.168.*' -and $_.PrefixOrigin -ne 'WellKnown' } |
    Select-Object -First 1 -ExpandProperty IPAddress
)

if (-not $currentIp) {
    throw 'Keine passende LAN-IPv4-Adresse gefunden.'
}

$entries = @(
    "$currentIp $localAlias"
)
$hostsContent = Get-Content $hostsPath -Raw

if ($hostsContent -notmatch ('(^|\r?\n)\s*\d{1,3}(\.\d{1,3}){3}\s+' + [regex]::Escape($localAlias) + '(\s|$)')) {
    $block = "`r`n# Webapp Central`r`n" + ($entries -join "`r`n") + "`r`n"
    Add-Content -Path $hostsPath -Value $block
}

$ruleName = 'Webapp Central HTTP 8080'
if (-not (Get-NetFirewallRule -DisplayName $ruleName -ErrorAction SilentlyContinue)) {
    New-NetFirewallRule `
        -DisplayName $ruleName `
        -Direction Inbound `
        -Action Allow `
        -Protocol TCP `
        -LocalPort $localPort `
        -Profile Private | Out-Null
}

Write-Host "Hosts-Eintrag aktiv: http://$localAlias`:$localPort/"
Write-Host "LAN-IP: http://$currentIp`:$localPort/"
