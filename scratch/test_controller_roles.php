<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\IraNavigationController;
use Illuminate\Http\Request;

echo "--- Testing IraNavigationController Server-Side Role Disambiguation ---\n";

$controller = app(IraNavigationController::class);

$refMethod = new ReflectionMethod(IraNavigationController::class, 'resolveServerRole');
$refMethod->setAccessible(true);

function makeReq(string $path) {
    $r = Request::create('/ira/intent', 'POST', ['current_page' => $path]);
    $r->setLaravelSession(session()->driver());
    return $r;
}

// Scenario 1: Only Member Session
$req1 = makeReq('/member/dashboard');
session()->flush();
session()->put('MEMBER_ID', 101);
$role1 = $refMethod->invoke($controller, $req1, '/member/dashboard');
assert($role1 === 'member', "Scenario 1 must be member (got {$role1})");
echo "[PASS] Scenario 1: Sole Member Session -> 'member'\n";

// Scenario 2: Only Admin Session
$req2 = makeReq('/admin/dashboard');
session()->flush();
session()->put('ADMIN_LOGIN', true);
$role2 = $refMethod->invoke($controller, $req2, '/admin/dashboard');
assert($role2 === 'admin', "Scenario 2 must be admin (got {$role2})");
echo "[PASS] Scenario 2: Sole Admin Session -> 'admin'\n";

// Scenario 3: Dual Session on Member Page (The exact root cause of the previous bug!)
$req3 = makeReq('/member/dashboard');
session()->flush();
session()->put('ADMIN_LOGIN', true);
session()->put('ADMIN_ID', 1);
session()->put('MEMBER_ID', 101);
$role3 = $refMethod->invoke($controller, $req3, '/member/dashboard');
assert($role3 === 'member', "Scenario 3 must be member on /member/dashboard even with ADMIN_LOGIN present (got {$role3})");
echo "[PASS] Scenario 3: Dual Session on Member Page -> 'member' (Bug Fixed!)\n";

// Scenario 4: Dual Session on Admin Page
$req4 = makeReq('/admin/dashboard');
session()->flush();
session()->put('ADMIN_LOGIN', true);
session()->put('ADMIN_ID', 1);
session()->put('MEMBER_ID', 101);
$role4 = $refMethod->invoke($controller, $req4, '/admin/dashboard');
assert($role4 === 'admin', "Scenario 4 must be admin on /admin/dashboard (got {$role4})");
echo "[PASS] Scenario 4: Dual Session on Admin Page -> 'admin'\n";

// Scenario 5: Guest (no session)
$req5 = makeReq('/');
session()->flush();
$role5 = $refMethod->invoke($controller, $req5, '/');
assert($role5 === 'guest', "Scenario 5 must be guest (got {$role5})");
echo "[PASS] Scenario 5: Empty Session -> 'guest'\n";

echo "\nALL 5 ROLE DISAMBIGUATION SCENARIOS PASSED!\n";
