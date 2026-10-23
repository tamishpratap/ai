<?php

$changes = [];

// 1. Member Footer: Add <x-ira-assistant /> before </body>
$memberFooterPath = __DIR__ . '/../resources/views/member/layouts/footer.blade.php';
$memberFooter = file_get_contents($memberFooterPath);
if (strpos($memberFooter, '<x-ira-assistant') === false) {
    if (strpos($memberFooter, '</body>') !== false) {
        $memberFooter = str_replace('</body>', "   <!-- IRA Voice Navigation Assistant Component -->\n   <x-ira-assistant />\n   </body>", $memberFooter);
        file_put_contents($memberFooterPath, $memberFooter);
        $changes[] = "member/layouts/footer.blade.php: Injected <x-ira-assistant />";
    } else {
        $changes[] = "ERROR: </body> not found in member/layouts/footer.blade.php";
    }
} else {
    $changes[] = "member/layouts/footer.blade.php: Already present";
}

// 2. Admin Layouts Main: Add <x-ira-assistant /> before </body>
$adminMainPath = __DIR__ . '/../resources/views/admin/layouts/main.blade.php';
$adminMain = file_get_contents($adminMainPath);
if (strpos($adminMain, '<x-ira-assistant') === false) {
    if (strpos($adminMain, '</body>') !== false) {
        $adminMain = str_replace('</body>', "<!-- IRA Voice Navigation Assistant Component -->\n\t<x-ira-assistant />\n</body>", $adminMain);
        file_put_contents($adminMainPath, $adminMain);
        $changes[] = "admin/layouts/main.blade.php: Injected <x-ira-assistant />";
    } else {
        $changes[] = "ERROR: </body> not found in admin/layouts/main.blade.php";
    }
} else {
    $changes[] = "admin/layouts/main.blade.php: Already present";
}

// 3. Admin Layouts Footer (fallback layout): Add <x-ira-assistant /> before </body>
$adminFooterPath = __DIR__ . '/../resources/views/admin/layouts/footer.blade.php';
$adminFooter = file_get_contents($adminFooterPath);
if (strpos($adminFooter, '<x-ira-assistant') === false) {
    if (strpos($adminFooter, '</body>') !== false) {
        $adminFooter = str_replace('</body>', "<!-- IRA Voice Navigation Assistant Component -->\n<x-ira-assistant />\n</body>", $adminFooter);
        file_put_contents($adminFooterPath, $adminFooter);
        $changes[] = "admin/layouts/footer.blade.php: Injected <x-ira-assistant />";
    } else {
        $changes[] = "ERROR: </body> not found in admin/layouts/footer.blade.php";
    }
} else {
    $changes[] = "admin/layouts/footer.blade.php: Already present";
}

// 4. Member Sidebar: Add Voice Assistant slide after Dashboard slide
$memberHeaderPath = __DIR__ . '/../resources/views/member/layouts/header.blade.php';
$memberHeader = file_get_contents($memberHeaderPath);
if (strpos($memberHeader, 'ira-open-assistant-trigger') === false) {
    $dashboardNeedle = "                                <span class=\"side-menu__label\">Dashboard</span>\n                            </a>\n                        </li>\n                        <!-- End::slide -->";
    // Also try CRLF
    $dashboardNeedleCRLF = "                                <span class=\"side-menu__label\">Dashboard</span>\r\n                            </a>\r\n                        </li>\r\n                        <!-- End::slide -->";
    
    $voiceItem = "\n\n                        <!-- Start::slide Voice Assistant -->\n" .
                 "                        <li class=\"slide\">\n" .
                 "                            <a href=\"javascript:void(0);\" class=\"side-menu__item ira-open-assistant-trigger\" data-bs-toggle=\"modal\" data-bs-target=\"#iraModal\">\n" .
                 "                                <svg xmlns=\"http://www.w3.org/2000/svg\" class=\"side-menu__icon\" viewBox=\"0 0 256 256\" width=\"24\" height=\"24\" fill=\"currentColor\">\n" .
                 "                                    <path d=\"M128,176a48.05,48.05,0,0,0,48-48V64a48,48,0,0,0-96,0v64A48.05,48.05,0,0,0,128,176ZM96,64a32,32,0,0,1,64,0v64a32,32,0,0,1-64,0Zm112,64a8,8,0,0,1-8,8,72.08,72.08,0,0,1-144,0,8,8,0,0,1,16,0,56.06,56.06,0,0,0,112,0A8,8,0,0,1,208,128Zm-72,87.71V232a8,8,0,0,1-16,0V215.71a80.19,80.19,0,0,1-37.49-14.77,8,8,0,1,1,10.6-12A64.13,64.13,0,0,0,120,200a64.13,64.13,0,0,0,26.89-11.06,8,8,0,1,1,10.6,12A80.19,80.19,0,0,1,136,215.71Z\"/>\n" .
                 "                                </svg>\n" .
                 "                                <span class=\"side-menu__label\">Voice Assistant</span>\n" .
                 "                            </a>\n" .
                 "                        </li>\n" .
                 "                        <!-- End::slide Voice Assistant -->";
                 
    if (strpos($memberHeader, $dashboardNeedleCRLF) !== false) {
        $memberHeader = str_replace($dashboardNeedleCRLF, $dashboardNeedleCRLF . $voiceItem, $memberHeader);
        file_put_contents($memberHeaderPath, $memberHeader);
        $changes[] = "member/layouts/header.blade.php: Added Voice Assistant sidebar item (CRLF)";
    } elseif (strpos($memberHeader, $dashboardNeedle) !== false) {
        $memberHeader = str_replace($dashboardNeedle, $dashboardNeedle . $voiceItem, $memberHeader);
        file_put_contents($memberHeaderPath, $memberHeader);
        $changes[] = "member/layouts/header.blade.php: Added Voice Assistant sidebar item (LF)";
    } else {
        $changes[] = "ERROR: Dashboard slide needle not found in member/layouts/header.blade.php";
    }
} else {
    $changes[] = "member/layouts/header.blade.php: Already present";
}

// 5. Admin Sidebar: Add Voice Assistant nav-item after Dashboard nav-item
$adminSidebarPath = __DIR__ . '/../resources/views/admin/include/sidebar.blade.php';
$adminSidebar = file_get_contents($adminSidebarPath);
if (strpos($adminSidebar, 'ira-open-assistant-trigger') === false) {
    $adminNeedle = "<a href=\"{{ url('admin/dashboard') }}\"><i class=\"ik ik-bar-chart-2\"></i><span>Dashboard</span></a>\n                </div>";
    $adminNeedleCRLF = "<a href=\"{{ url('admin/dashboard') }}\"><i class=\"ik ik-bar-chart-2\"></i><span>Dashboard</span></a>\r\n                </div>";
    
    $adminVoiceItem = "\n\n                <div class=\"nav-item\">\n" .
                      "                    <a href=\"javascript:void(0);\" class=\"ira-open-assistant-trigger\" data-toggle=\"modal\" data-target=\"#iraModal\"><i class=\"ik ik-mic\"></i><span>Voice Assistant</span></a>\n" .
                      "                </div>";
                      
    if (strpos($adminSidebar, $adminNeedleCRLF) !== false) {
        $adminSidebar = str_replace($adminNeedleCRLF, $adminNeedleCRLF . $adminVoiceItem, $adminSidebar);
        file_put_contents($adminSidebarPath, $adminSidebar);
        $changes[] = "admin/include/sidebar.blade.php: Added Voice Assistant sidebar item (CRLF)";
    } elseif (strpos($adminSidebar, $adminNeedle) !== false) {
        $adminSidebar = str_replace($adminNeedle, $adminNeedle . $adminVoiceItem, $adminSidebar);
        file_put_contents($adminSidebarPath, $adminSidebar);
        $changes[] = "admin/include/sidebar.blade.php: Added Voice Assistant sidebar item (LF)";
    } else {
        $changes[] = "ERROR: Admin Dashboard nav-item needle not found in admin/include/sidebar.blade.php";
    }
} else {
    $changes[] = "admin/include/sidebar.blade.php: Already present";
}

echo implode("\n", $changes) . "\n";
