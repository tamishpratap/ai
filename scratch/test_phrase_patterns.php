<?php

$phrases = [
    "Earning section mein kaun-kaun se pages hain?",
    "Earning section mein kaun se pages hain?",
    "Earnings section mein kaun-kaun se pages hain?",
    "P2P section mein kaun-kaun se pages hain?",
    "P to P ke pages batao.",
    "P2P mein kya-kya hai?",
    "Team section mein kya hai?",
    "Show me all pages under Member Management",
    "Admin ke Income section mein kaun-kaun se pages hain?",
    "Team section ke pages batao",
    "Team section mein kaunse page hain?",
];

$pattern = '/\b(?:kaun[-\s]*kaun\s*(?:se)?\s*pages?|kya[-\s]*kya\s*hai|kitne\s*pages?|pages?\s*(?:batao|dikhao|list|bataiye)|which\s*pages?|what\s*pages?|show\s*all\s*pages?|list\s*pages?|under\s+(?:section|management))\b/iu';

foreach ($phrases as $p) {
    $match = preg_match($pattern, $p) ? "MATCHED" : "FAILED";
    echo "[$match] $p\n";
}
