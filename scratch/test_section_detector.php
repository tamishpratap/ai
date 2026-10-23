<?php

$phrases = [
    "Earning section mein kaun-kaun se pages hain?",
    "Earning section mein kaun se pages hain?",
    "Earning section mein kaunse page hain?",
    "Earnings section mein kaun-kaun se pages hain?",
    "P2P section mein kaun-kaun se pages hain?",
    "P to P ke pages batao.",
    "P2P mein kya-kya hai?",
    "Team section mein kya hai?",
    "Show me all pages under Member Management",
    "Admin ke Income section mein kaun-kaun se pages hain?",
    "Team section ke pages batao",
    "What pages are in the withdrawal section?",
    "List all pages in team management",
    "Profile section ke pages dikhao",
    "Support mein kya-kya pages hain?",
];

$pattern = '/\b(?:kaun[-\s]*kaun\s*(?:se)?|kaun\s*se|kaunse|kya[-\s]*kya|kya|kitne)\s*(?:bhi\s*)?(?:pages?|options?|features?)\b|\b(?:pages?|options?)\s*(?:batao|bataiye|dikhao|list|bata\s*do|batao\s*na)\b|\b(?:mein|under)\s+(?:kya\s+hai|kya[-\s]*kya\s+hai)\b|\b(?:what\s+pages?|which\s+pages?|list\s+(?:all\s+)?pages?|show\s+(?:all\s+)?pages?|pages?\s+in|pages?\s+under)\b|(?:कौन[-\s]*कौन\s*से\s*पेज|कौन\s*से\s*पेज|क्या[-\s]*क्या\s*है|पेज\s*बताओ|पेज\s*दिखाओ)/iu';

foreach ($phrases as $p) {
    $match = preg_match($pattern, $p) ? "MATCHED" : "FAILED";
    echo "[$match] $p\n";
}
