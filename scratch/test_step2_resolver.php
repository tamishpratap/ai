<?php

$options = [
    'Staking Income' => 'member_income_staking',
    'Level Income' => 'member_income_level',
    'Direct Income' => 'member_income_direct',
];

function resolveChoice(string $input, array $options) {
    $clean = trim($input);
    $lower = strtolower($clean);
    
    // Ordinals
    $keys = array_keys($options);
    if (preg_match('/\b(?:pehla|pehli|first|1st|number\s*one|ek)\b/iu', $clean)) {
        return $options[$keys[0]] ?? null;
    }
    if (preg_match('/\b(?:doosra|doosri|dusra|second|2nd|number\s*two|do)\b/iu', $clean)) {
        return $options[$keys[1]] ?? null;
    }
    if (preg_match('/\b(?:teesra|teesri|tisra|third|3rd|number\s*three|teen)\b/iu', $clean)) {
        return $options[$keys[2]] ?? null;
    }
    if (preg_match('/\b(?:aakhri|last)\b/iu', $clean)) {
        return end($options);
    }
    
    // Check label keywords
    foreach ($options as $label => $destKey) {
        $lLower = strtolower($label);
        // Direct match or word match
        if (str_contains($lower, $lLower)) {
            return $destKey;
        }
        $words = explode(' ', $lLower);
        foreach ($words as $w) {
            if (strlen($w) > 3 && preg_match('/\b' . preg_quote($w, '/') . '\b/iu', $clean)) {
                return $destKey;
            }
        }
    }
    
    return null;
}

$testInputs = [
    "Direct Income par le chalo",
    "Level Income wale page par le chalo.",
    "pehla wala",
    "second page",
    "Direct wala",
    "3rd one",
    "last wala",
];

foreach ($testInputs as $in) {
    $res = resolveChoice($in, $options);
    echo "$in ==> $res\n";
}
