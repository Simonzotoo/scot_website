<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class AdminAuthTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 2) . '/includes/auth.php';
    }

    protected function setUp(): void
    {
        test_reset_database();
        $_SESSION = [];
        $_SERVER['REMOTE_ADDR'] = '203.0.113.20';

        db()->prepare('INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, ?)')
            ->execute(['Test Admin', 'admin@example.test', password_hash('correcthorse', PASSWORD_DEFAULT), 'admin']);
    }

    public function testLoginWithCorrectPasswordSucceeds(): void
    {
        $result = login_admin('admin@example.test', 'correcthorse');

        $this->assertSame(AUTH_OK, $result);
        $this->assertNotEmpty($_SESSION['admin_id'] ?? null);
    }

    public function testLoginWithWrongPasswordFails(): void
    {
        $result = login_admin('admin@example.test', 'wrong-password');

        $this->assertSame(AUTH_FAIL_PASSWORD, $result);
        $this->assertArrayNotHasKey('admin_id', $_SESSION);
    }

    public function testLoginWithUnknownEmailFails(): void
    {
        $result = login_admin('nobody@example.test', 'whatever1');
        $this->assertSame(AUTH_FAIL_NOT_FOUND, $result);
    }

    public function testLoginLocksOutAfterTooManyFailedAttempts(): void
    {
        // LOGIN_MAX_ATTEMPTS is 5 per LOGIN_WINDOW_MINUTES (see includes/auth.php).
        for ($i = 0; $i < 5; $i++) {
            login_admin('admin@example.test', 'wrong-password');
        }

        $result = login_admin('admin@example.test', 'correcthorse'); // even the RIGHT password, 6th try
        $this->assertSame(AUTH_FAIL_LOCKED, $result);
    }

    public function testCurrentAdminReturnsNullWhenLoggedOut(): void
    {
        $this->assertNull(current_admin());
    }

    public function testCurrentAdminReturnsProfileAfterLogin(): void
    {
        login_admin('admin@example.test', 'correcthorse');

        $admin = current_admin();
        $this->assertNotNull($admin);
        $this->assertSame('Test Admin', $admin['name']);
        $this->assertSame('admin', $admin['role']);
    }

    public function testLogoutClearsTheSession(): void
    {
        login_admin('admin@example.test', 'correcthorse');
        $this->assertNotNull(current_admin());

        logout_admin();
        $this->assertNull(current_admin());
    }
}
