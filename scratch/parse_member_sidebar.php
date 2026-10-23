<?php

$c = file_get_contents(__DIR__ . '/../resources/views/member/layouts/header.blade.php');

// Let's find the sidebar section: starts around line 900 where <nav class="side-menu"> or <ul class="main-menu"> is
$pos = strpos($c, 'class="main-menu"');
if ($pos === false) {
    $pos = strpos($c, 'side-menu');
}

$sidebarContent = substr($c, $pos);
// Cut off at </nav> or end of sidebar
$endPos = strpos($sidebarContent, '</aside>');
if ($endPos !== false) {
    $sidebarContent = substr($sidebarContent, 0, $endPos);
}

// Split into top-level slides: <li class="slide ...">
preg_match_all('/<li class="slide(?:\s+has-sub)?"[^>]*>([\s\S]*?)(?=<li class="slide|<\/ul>\s*<\/nav>|<\/aside>)/i', $sidebarContent, $slides);

echo "Found " . count($slides[0]) . " slides in Member Sidebar:\n\n";

foreach ($slides[0] as $slide) {
    // Check if it's a section header
    if (preg_match('/<li class="slide__category">\s*<span class="category-name">([^<]+)<\/span>/i', $slide, $cat)) {
        echo "=== CATEGORY: " . trim($cat[1]) . " ===\n";
    }
    
    // Get label
    $label = '';
    if (preg_match('/<span class="side-menu__label[^"]*">\s*([^<]+)\s*<\/span>/i', $slide, $mLabel)) {
        $label = trim($mLabel[1]);
    }
    
    // Check if it has a direct link
    $directLink = '';
    if (preg_match('/<a href="([^"]+)"\s+class="side-menu__item(?:\s+[^"]*)?">\s*(?:<svg[\s\S]*?<\/svg>|<i[\s\S]*?<\/i>)?\s*<span class="side-menu__label/i', $slide, $mLink)) {
        $directLink = trim($mLink[1]);
    }
    
    // Check for submenu items
    $subItems = [];
    if (preg_match('/<ul class="slide-menu child1">([\s\S]*?)<\/ul>/i', $slide, $mSub)) {
        preg_match_all('/<a href="([^"]+)"[^>]*class="side-menu__item"[^>]*>([\s\S]*?)<\/a>/i', $mSub[1], $mLinks);
        for ($i = 0; $i < count($mLinks[0]); $i++) {
            $subTitle = trim(preg_replace('/\s+/', ' ', strip_tags($mLinks[2][$i])));
            $subUrl = trim($mLinks[1][$i]);
            // check if commented out
            $subItems[] = ['title' => $subTitle, 'url' => $subUrl];
        }
    }
    
    if ($label !== '') {
        echo "SECTION: [{$label}]" . ($directLink && $directLink !== 'javascript:void(0);' ? " -> {$directLink}" : "") . "\n";
        foreach ($subItems as $sub) {
            echo "   • {$sub['title']}  ==>  {$sub['url']}\n";
        }
    }
}
