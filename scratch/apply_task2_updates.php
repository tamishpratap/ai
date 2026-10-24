<?php

$updates = [
    // 1. frontend/index.blade.php
    [
        'file' => 'resources/views/frontend/index.blade.php',
        'replacements' => [
            [
                'from' => '<title>Quantum ARB — Smart Arbitrage. Max Returns.</title>',
                'to'   => '<title>Demo — Smart Arbitrage. Max Returns.</title>'
            ],
            [
                'from' => 'content="Quantum ARB leverages advanced arbitrage technology to identify price differences across global markets, ensuring consistent returns with minimal risk. Arbitrage. Advantage. Automated.">',
                'to'   => 'content="Demo leverages advanced arbitrage technology to identify price differences across global markets, ensuring consistent returns with minimal risk. Arbitrage. Advantage. Automated.">'
            ],
            [
                'from' => 'alt="Quantum ARB"></a>',
                'to'   => 'alt="Demo"></a>'
            ],
            [
                'from' => '<h1>WELCOME TO<br><span class="gtext">QUANTUM ARB</span></h1>',
                'to'   => '<h1>WELCOME TO<br><span class="gtext">DEMO</span></h1>'
            ],
            [
                'from' => '<p class="lead mt-5">Quantum ARB leverages advanced arbitrage technology to identify price differences across',
                'to'   => '<p class="lead mt-5">Demo leverages advanced arbitrage technology to identify price differences across'
            ],
            [
                'from' => '<p class="lead mt-5">Quantum ARB is a modern and structured business platform designed around a transparent and',
                'to'   => '<p class="lead mt-5">Demo is a modern and structured business platform designed around a transparent and'
            ],
            [
                'from' => 'alt="Quantum ARB" class="w-40 mx-auto mb-6" style="filter:drop-shadow(0 0 30px rgba(43,232,107,.5))">',
                'to'   => 'alt="Demo" class="w-40 mx-auto mb-6" style="filter:drop-shadow(0 0 30px rgba(43,232,107,.5))">'
            ],
            [
                'from' => '<div class="head font-bold text-[17px]">Quantum Engine</div>',
                'to'   => '<div class="head font-bold text-[17px]">Demo Engine</div>'
            ],
            [
                'from' => 'alt="Quantum ARB" class="w-24 mx-auto mb-6" style="filter:drop-shadow(0 0 26px rgba(43,232,107,.5))">',
                'to'   => 'alt="Demo" class="w-24 mx-auto mb-6" style="filter:drop-shadow(0 0 26px rgba(43,232,107,.5))">'
            ],
            [
                'from' => 'alt="Quantum ARB">',
                'to'   => 'alt="Demo">'
            ],
            [
                'from' => 'www.qunatumarb.world',
                'to'   => 'www.demo.com'
            ],
            [
                'from' => '<span class="text-[13px]" style="color:var(--mut)">© 2026 Quantum ARB. All rights reserved.</span>',
                'to'   => '<span class="text-[13px]" style="color:var(--mut)">© {{ date(\'Y\') }} Demo. All rights reserved.</span>'
            ]
        ]
    ],
    // 2. member/dashboard.blade.php
    [
        'file' => 'resources/views/member/dashboard.blade.php',
        'replacements' => [
            [
                'from' => "<span>{{ !empty(\$popupTitle) ? \$popupTitle : 'Welcome to QuantumARB' }}</span>",
                'to'   => "<span>{{ !empty(\$popupTitle) ? \$popupTitle : 'Welcome to Demo' }}</span>"
            ]
        ]
    ],
    // 3. admin/popup-image.blade.php
    [
        'file' => 'resources/views/admin/popup-image.blade.php',
        'replacements' => [
            [
                'from' => 'placeholder="e.g. https://t.me/quantum_arb or member/staking"',
                'to'   => 'placeholder="e.g. https://t.me/demo or member/staking"'
            ]
        ]
    ],
    // 4. components/ira-assistant.blade.php
    [
        'file' => 'resources/views/components/ira-assistant.blade.php',
        'replacements' => [
            [
                'from' => '{{-- Quantum ARB IRA Voice Navigation Assistant Component --}}',
                'to'   => '{{-- Demo IRA Voice Navigation Assistant Component --}}'
            ],
            [
                'from' => '<small class="text-muted ira-header-subtitle">Quantum ARB Voice AI</small>',
                'to'   => '<small class="text-muted ira-header-subtitle">Demo Voice AI</small>'
            ]
        ]
    ],
    // 5. member/mails/mail.blade.php
    [
        'file' => 'resources/views/member/mails/mail.blade.php',
        'replacements' => [
            [
                'from' => "<div class=\"url-value\">https://qunatumarb.world/</div>\n                    <a href=\"https://qunatumarb.world/\" class=\"url-link\">📍 Visit Platform</a>",
                'to'   => "<div class=\"url-value\">{{ url('/') }}</div>\n                    <a href=\"{{ url('/') }}\" class=\"url-link\">📍 Visit Platform</a>"
            ],
            // Also handle \r\n
            [
                'from' => "<div class=\"url-value\">https://qunatumarb.world/</div>\r\n                    <a href=\"https://qunatumarb.world/\" class=\"url-link\">📍 Visit Platform</a>",
                'to'   => "<div class=\"url-value\">{{ url('/') }}</div>\r\n                    <a href=\"{{ url('/') }}\" class=\"url-link\">📍 Visit Platform</a>"
            ]
        ]
    ],
    // 6. AdminController.php
    [
        'file' => 'app/Http/Controllers/AdminController.php',
        'replacements' => [
            [
                'from' => "\$message->subject('Quantum ARB Admin Login OTP');",
                'to'   => "\$message->subject('Demo Admin Login OTP');"
            ],
            [
                'from' => "\$message->subject('Quantum ARB Admin Account Password');",
                'to'   => "\$message->subject('Demo Admin Account Password');"
            ]
        ]
    ],
    // 7. MemberDetailController.php
    [
        'file' => 'app/Http/Controllers/MemberDetailController.php',
        'replacements' => [
            [
                'from' => "\$message->subject('Quantum ARB Member Login Credentials');",
                'to'   => "\$message->subject('Demo Member Login Credentials');"
            ],
            [
                'from' => "\$message->subject('Quantum ARB Member Account Password');",
                'to'   => "\$message->subject('Demo Member Account Password');"
            ]
        ]
    ],
    // 8. AdminChatReportService.php
    [
        'file' => 'app/Services/ChatAi/AdminChatReportService.php',
        'replacements' => [
            [
                'from' => "'system_name' => config('app.name', 'Quantum ARB AI Demo'),",
                'to'   => "'system_name' => config('app.name', 'Demo'),"
            ]
        ]
    ],
    // 9. config/detailsApp.php
    [
        'file' => 'config/detailsApp.php',
        'replacements' => [
            [
                'from' => "'url'=>'https://qunatumarb.world/',",
                'to'   => "'url'=> env('APP_URL', 'http://localhost'),"
            ]
        ]
    ],
    // 10. logo/favicon/site.webmanifest
    [
        'file' => 'public/logo/favicon/site.webmanifest',
        'replacements' => [
            [
                'from' => "\"name\": \"MyWebSite\",\n  \"short_name\": \"MySite\",",
                'to'   => "\"name\": \"Demo\",\n  \"short_name\": \"Demo\","
            ],
            [
                'from' => "\"name\": \"MyWebSite\",\r\n  \"short_name\": \"MySite\",",
                'to'   => "\"name\": \"Demo\",\r\n  \"short_name\": \"Demo\","
            ]
        ]
    ],
    // 11. mem_assets/images/brand-logos/favicon/site.webmanifest
    [
        'file' => 'public/mem_assets/images/brand-logos/favicon/site.webmanifest',
        'replacements' => [
            [
                'from' => "\"name\": \"MyWebSite\",\n  \"short_name\": \"MySite\",",
                'to'   => "\"name\": \"Demo\",\n  \"short_name\": \"Demo\","
            ],
            [
                'from' => "\"name\": \"MyWebSite\",\r\n  \"short_name\": \"MySite\",",
                'to'   => "\"name\": \"Demo\",\r\n  \"short_name\": \"Demo\","
            ]
        ]
    ],
    // 12. welcome.blade.php
    [
        'file' => 'resources/views/welcome.blade.php',
        'replacements' => [
            [
                'from' => '<title>Laravel</title>',
                'to'   => '<title>Demo</title>'
            ]
        ]
    ]
];

foreach ($updates as $item) {
    $filePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $item['file']);
    if (!file_exists($filePath)) {
        echo "ERROR: File not found: {$item['file']}\n";
        continue;
    }
    $content = file_get_contents($filePath);
    $modified = false;
    foreach ($item['replacements'] as $r) {
        if (strpos($content, $r['from']) !== false) {
            $content = str_replace($r['from'], $r['to'], $content);
            echo "UPDATED in {$item['file']}: '{$r['from']}' -> '{$r['to']}'\n";
            $modified = true;
        }
    }
    if ($modified) {
        file_put_contents($filePath, $content);
        echo "SUCCESS: Saved {$item['file']}\n\n";
    } else {
        echo "NO MATCH in {$item['file']}\n\n";
    }
}
