<?php

$raw = "Member panel ke Team section mein kaun-kaun se pages hain?";
$pattern = '/\b(?:kaun[-\s]*kaun\s*se\s*pages|kya[-\s]*kya\s*hai|kitne\s*pages|pages\s*(?:batao|dikhao|list)|which\s*pages|what\s*pages|show\s*all\s*pages|list\s*pages|under\s+(?:section|management))\b/iu';

echo "Match raw: " . (preg_match($pattern, $raw) ? "YES" : "NO") . "\n";
