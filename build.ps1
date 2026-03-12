# Dynamic Classes for Elementor - Build Script
# This script packages the plugin into a ZIP file ready for WordPress upload.

$PluginName = "dynamic-classes-elementor"
$MainFile = "dynamic-classes-elementor.php"
$BuildDir = "build_temp"

# 1. Extract version from main file
$Content = Get-Content $MainFile -Raw
if ($Content -match "Version:\s+([0-9\.]+)") {
    $Version = $Matches[1]
} else {
    $Version = "unknown"
}
$ZipName = "$PluginName-v$Version.zip"

# 2. Clean up old builds
if (Test-Path $BuildDir) { Remove-Item -Recurse -Force $BuildDir }
if (Test-Path $ZipName) { Remove-Item $ZipName }

# 3. Create structure
New-Item -ItemType Directory -Path "$BuildDir/$PluginName" | Out-Null

# 4. Copy files (excluding dev files)
$ExcludeList = @(".git*", "node_modules", "build.ps1", "*.zip", ".DS_Store", "package*.json")
Copy-Item -Path "assets", "data", "includes", "languages", "README.md", "dynamic-classes-elementor.php" -Destination "$BuildDir/$PluginName" -Recurse -Exclude $ExcludeList

Write-Host "Packaging version v$Version..." -ForegroundColor Cyan

# 5. Create the ZIP
Compress-Archive -Path "$BuildDir/$PluginName" -DestinationPath "./$ZipName" -Force

Write-Host "Success! ZIP created: $ZipName" -ForegroundColor Green

# 6. Final Cleanup
Remove-Item -Recurse -Force $BuildDir
