<?php

// Parse Member Sidebar
$memberHeader = file_get_contents(__DIR__ . '/../resources/views/member/layouts/header.blade.php');
echo "=== MEMBER SIDEBAR PARSER ===\n";

// Let's find all slide items
preg_match_all('/<li class="slide(?:\s+has-sub)?">([\s\S]*?)<\/li>/', $memberHeader, $matches);

// Or let's look for sections and links
preg_match_all('/(?:<span class="side-menu__label(?:\s+[^"]*)?">([^<]+)<\/span>[\s\S]*?<ul class="slide-menu child1">([\s\S]*?)<\/ul>|<a href="([^"]+)" class="side-menu__item">\s*(?:<svg[\s\S]*?<\/svg>|<i[\s\S]*?<\/i>)?\s*<span class="side-menu__label[^"]*">([^<]+)<\/span>)/i', $memberHeader, $mItems);

echo "Found matches.\n";

// Parse Admin Sidebar
$adminSidebar = file_get_contents(__DIR__ . '/../resources/views/admin/include/sidebar.blade.php');
echo "\n=== ADMIN SIDEBAR PARSER ===\n";
echo $adminSidebar;
