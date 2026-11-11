param(
    [Parameter(Mandatory = $true)]
    [string]$InputPath,
    [Parameter(Mandatory = $true)]
    [string]$OutputPath
)

$ErrorActionPreference = 'Stop'
$powerPoint = $null
$presentation = $null

try {
    $powerPoint = New-Object -ComObject PowerPoint.Application
    $powerPoint.DisplayAlerts = 1
    $powerPoint.AutomationSecurity = 3

    $presentation = $powerPoint.Presentations.Open($InputPath, -1, 0, 0)
    $presentation.SaveAs($OutputPath, 32)

    if (-not (Test-Path -LiteralPath $OutputPath -PathType Leaf)) {
        throw 'PowerPoint did not create the PDF output.'
    }
}
catch {
    Write-Error $_
    exit 1
}
finally {
    try {
        if ($presentation) {
            $presentation.Close()
        }
    }
    finally {
        try {
            if ($presentation) {
                [void][Runtime.InteropServices.Marshal]::FinalReleaseComObject($presentation)
            }
        }
        finally {
            try {
                if ($powerPoint) {
                    $powerPoint.Quit()
                }
            }
            finally {
                if ($powerPoint) {
                    [void][Runtime.InteropServices.Marshal]::FinalReleaseComObject($powerPoint)
                }
            }
        }
    }
}
