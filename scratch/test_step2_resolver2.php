<?php

$options = [
    'Staking Income' => 'member_income_staking',
    'Level Income' => 'member_income_level',
    'Direct Income' => 'member_income_direct',
];

function resolveChoiceFixed(string $input, array $options) {
    $clean = trim($input);
    $lower = strtolower($clean);
    $keys = array_keys($options);

    // 1. Ordinals
    if (preg_match('/\b(?:pehla|pehli|first|1st|number\s*one)\b/iu', $clean)) {
        return $options[$keys[0]] ?? null;
    }
    if (preg_match('/\b(?:doosra|doosri|dusra|second|2nd|number\s*two)\b/iu', $clean)) {
        return $options[$keys[1]] ?? null;
    }
    if (preg_match('/\b(?:teesra|teesri|tisra|third|3rd|number\s*three)\b/iu', $clean)) {
        return $options[$keys[2]] ?? null;
    }
    if (preg_match('/\b(?:aakhri|last)\b/iu', $clean)) {
        return end($options);
    }

    // 2. Pass 1: Full label match
    foreach ($options as $label => $destKey) {
        if (str_contains($lower, strtolower($label))) {
            return $destKey;
        }
    }

    // 3. Pass 2: Distinctive keyword match (excluding generic terms like 'page', 'income', 'section')
    $genericWords = ['page', 'income', 'section', 'details', 'wala', 'par', 'chalo', 'kholo'];
    foreach ($options as $label => $destKey) {
        $words = explode(' ', strtolower($label));
        foreach ($words as $w) {
            if (strlen($w) >= 3 && !in_array($w, $genericWords, true)) {
                if (preg_match('/\b' . preg_quote($w, '/') . '\b/iu', $lower)) {
                    return $destKey;
                }
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
    $res = resolveChoiceFixed($in, $options);
    echo "$in ==> $res\n";
}
