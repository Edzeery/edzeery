$ErrorActionPreference = "Continue"
$f = "app\Domains\Order\Services\OrderTrackingService.php"
$lines = Get-Content $f
"== generateRiderTrackingNumber (exact) =="
$hit = $lines | Select-String -Pattern "function generateRiderTrackingNumber"
if ($hit) { $s = $hit.LineNumber - 怎么办 3; if ($s -lt 1) { $s = 1 }; $lines[($s-1)..([Math]::Min($s+16,$lines.Count-1))] | ForEach-Object -Begin {$n=$s} -Process { "{0}: {1}" -f $n, $_.Trim(); $n++ } }
""
"== ensureRiderTracking (exact) =="
$hit = $lines | Select-String -Pattern "function ensureRiderTracking"
if ($hit) { $s = $hit.LineNumber; $lines[($s-1)..([Math]::Min($s+40,$lines.Count-1))] | ForEach-Object -Begin {$n=$s} -Process { "{0}: {1}" -f $n, $_.Trim(); $n++ } }
""
"== current std tracking-number generators in same class =="
$lines | Select-String -Pattern "function generate|tracking_number = Str::random|TrackingNumber|riderTrackingNumber" | ForEach-Object { "{0}: {1}" -f $_.LineNumber, $_.Line.Trim() }
