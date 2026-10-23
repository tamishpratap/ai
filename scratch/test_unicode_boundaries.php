<?php

$pattern = '/(?<![\p{L}\p{M}\p{N}])' . preg_quote('अर्निंग', '/') . '(?![\p{L}\p{M}\p{N}])/u';
$text1 = 'अर्निंग सेक्शन में कौन-कौन से पेज हैं';
$text2 = 'मुझे अर्निंग दिखाओ';
$text3 = 'लर्निंग'; // should not match 'अर्निंग' inside 'लर्निंग'

echo 'Match text1: ' . (preg_match($pattern, $text1) ? 'YES' : 'NO') . PHP_EOL;
echo 'Match text2: ' . (preg_match($pattern, $text2) ? 'YES' : 'NO') . PHP_EOL;
echo 'Match text3 (learning): ' . (preg_match($pattern, $text3) ? 'YES' : 'NO') . PHP_EOL;

// Test with Latin alias
$patternLatin = '/(?<![\p{L}\p{M}\p{N}])' . preg_quote('earnings', '/') . '(?![\p{L}\p{M}\p{N}])/ui';
$textLatin1 = 'Show me earnings pages';
$textLatin2 = 'yearnings';
echo 'Match Latin1: ' . (preg_match($patternLatin, $textLatin1) ? 'YES' : 'NO') . PHP_EOL;
echo 'Match Latin2: ' . (preg_match($patternLatin, $textLatin2) ? 'YES' : 'NO') . PHP_EOL;
