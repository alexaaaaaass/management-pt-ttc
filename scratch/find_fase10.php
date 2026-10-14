<?php
$f = fopen('C:/Users/ThinkBook/.gemini/antigravity-ide/brain/6c2fff15-afb3-4707-824a-af10f5c7a6f5/.system_generated/logs/transcript.jsonl', 'r');
$lineNum = 0;
while (($line = fgets($f)) !== false) {
    $lineNum++;
    if (strpos($line, 'Fase 10') !== false || strpos($line, 'Fase 5:') !== false) {
        $json = json_decode($line, true);
        if ($json && isset($json['content'])) {
            echo "--- Line $lineNum ---\n";
            echo substr($json['content'], 0, 1000) . "\n\n";
        }
    }
}
fclose($f);
