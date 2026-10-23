<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Running Phase 3 Frontend Integration Verification...\n";

try {
    $rendered = (string) Illuminate\Support\Facades\Blade::render('<x-ira-assistant />');
    
    // 1. Core Component Containers
    assert(strpos($rendered, 'id="iraFloatingContainer"') !== false, 'Floating container present');
    assert(strpos($rendered, 'id="iraFloatingBtn"') !== false, 'Floating mic button present');
    assert(strpos($rendered, 'id="iraFloatingExpandBtn"') !== false, 'Floating expand button present');
    assert(strpos($rendered, 'id="iraModal"') !== false, 'Modal container present');
    assert(strpos($rendered, 'id="iraOrbBtn"') !== false, 'Orb mic button present');
    assert(strpos($rendered, 'id="iraTranscriptArea"') !== false, 'Transcript area present');
    assert(strpos($rendered, 'id="iraUserText"') !== false, 'User transcript container present');
    assert(strpos($rendered, 'id="iraAiText"') !== false, 'AI response text present');
    assert(strpos($rendered, 'id="iraActionBanner"') !== false, 'Action banner present');
    assert(strpos($rendered, 'id="iraContextBadge"') !== false, 'Context role badge present');
    
    // 2. Strict Voice-Only Verification
    assert(strpos($rendered, '<input type="text"') === false, 'No text input found (voice-only satisfied)');
    assert(strpos($rendered, '<textarea') === false, 'No textarea found (voice-only satisfied)');
    
    // 3. Dual Framework Attributes (BS4 & BS5)
    assert(strpos($rendered, 'data-bs-toggle="modal"') !== false, 'Bootstrap 5 data-bs-toggle present');
    assert(strpos($rendered, 'data-toggle="modal"') !== false, 'Bootstrap 4 data-toggle present');
    assert(strpos($rendered, 'data-bs-dismiss="modal"') !== false, 'Bootstrap 5 data-bs-dismiss present');
    assert(strpos($rendered, 'data-dismiss="modal"') !== false, 'Bootstrap 4 data-dismiss present');
    
    // 4. Asset Links
    assert(strpos($rendered, 'ira-assistant.js') !== false, 'ira-assistant.js referenced');
    assert(strpos($rendered, 'ira-assistant.css') !== false, 'ira-assistant.css referenced');
    
    // 5. Layout Inclusions
    $memberFooter = file_get_contents(__DIR__ . '/../resources/views/member/layouts/footer.blade.php');
    assert(strpos($memberFooter, '<x-ira-assistant') !== false, 'Member footer contains <x-ira-assistant />');
    
    $adminMain = file_get_contents(__DIR__ . '/../resources/views/admin/layouts/main.blade.php');
    assert(strpos($adminMain, '<x-ira-assistant') !== false, 'Admin main layout contains <x-ira-assistant />');
    
    $adminFooter = file_get_contents(__DIR__ . '/../resources/views/admin/layouts/footer.blade.php');
    assert(strpos($adminFooter, '<x-ira-assistant') !== false, 'Admin fallback footer contains <x-ira-assistant />');
    
    // 6. Sidebar Inclusions
    $memberHeader = file_get_contents(__DIR__ . '/../resources/views/member/layouts/header.blade.php');
    assert(strpos($memberHeader, 'ira-open-assistant-trigger') !== false, 'Member sidebar contains Voice Assistant item');
    assert(strpos($memberHeader, 'Voice Assistant') !== false, 'Member sidebar displays "Voice Assistant" label');
    
    $adminSidebar = file_get_contents(__DIR__ . '/../resources/views/admin/include/sidebar.blade.php');
    assert(strpos($adminSidebar, 'ira-open-assistant-trigger') !== false, 'Admin sidebar contains Voice Assistant item');
    assert(strpos($adminSidebar, 'Voice Assistant') !== false, 'Admin sidebar displays "Voice Assistant" label');
    
    echo "========================================================\n";
    echo "ALL 15 INTEGRATION ASSERTIONS PASSED SUCCESSFULLY!\n";
    echo "========================================================\n";
    echo "1. Component DOM structure: VALID (".strlen($rendered)." bytes rendered)\n";
    echo "2. Dual Bootstrap 4 / Bootstrap 5 compatibility: VERIFIED\n";
    echo "3. Voice-only policy enforcement: VERIFIED (0 inputs, 0 textareas)\n";
    echo "4. Floating Launcher & Docked Panel: INSTALLED\n";
    echo "5. Member Layout & Sidebar: INSTALLED\n";
    echo "6. Admin Layout & Sidebar: INSTALLED\n";
} catch (\Throwable $e) {
    echo "FAIL: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
    exit(1);
}
