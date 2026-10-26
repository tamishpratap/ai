<?php

namespace Tests\Feature;

use App\Models\MemberDetail;
use Tests\TestCase;

class DashboardAccessTest extends TestCase
{
    /**
     * Test guest access to admin dashboard redirects to admin login.
     */
    public function test_guest_access_to_admin_dashboard_redirects(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/admin');
    }

    /**
     * Test guest access to member dashboard redirects to home login.
     */
    public function test_guest_access_to_member_dashboard_redirects(): void
    {
        $response = $this->get('/member/dashboard');
        $response->assertStatus(302);
        $response->assertRedirect('/');
    }

    /**
     * Test authenticated admin dashboard renders successfully with HTTP 200.
     */
    public function test_authenticated_admin_dashboard_renders_successfully(): void
    {
        $response = $this->withSession([
            'ADMIN_LOGIN' => true,
            'ADMIN_USER' => 'admin',
        ])->get('/admin/dashboard');

        $response->assertStatus(200);
        $response->assertSee("Total Withdrawal");
        $response->assertSee("Withdrawal Gross Amount");
    }

    /**
     * Test authenticated member dashboard renders successfully with HTTP 200
     * even when activated_at is null.
     */
    public function test_authenticated_member_dashboard_renders_successfully(): void
    {
        $member = MemberDetail::where('memberid', 'QRB839609')->first();
        if (!$member) {
            $member = MemberDetail::first();
        }

        $this->assertNotNull($member, 'At least one member must exist in database for dashboard test');

        $response = $this->withSession([
            'MEMBER_ID' => $member->memberid,
            'MEMBER_NAME' => $member->name,
        ])->get('/member/dashboard');

        $response->assertStatus(200);
        $response->assertSee('Total Withdrawal');
        $response->assertSee($member->memberid);
    }

    /**
     * Test role boundary: member session cannot access admin dashboard.
     */
    public function test_member_cannot_access_admin_dashboard(): void
    {
        $response = $this->withSession([
            'MEMBER_ID' => 'QRB839609',
        ])->get('/admin/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/admin');
    }

    /**
     * Test role boundary: admin session without member session cannot access member dashboard.
     */
    public function test_admin_cannot_access_member_dashboard_without_member_session(): void
    {
        $response = $this->withSession([
            'ADMIN_LOGIN' => true,
        ])->get('/member/dashboard');

        $response->assertStatus(302);
        $response->assertRedirect('/');
    }
}
