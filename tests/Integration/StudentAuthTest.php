<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StudentAuthTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 2) . '/includes/student_auth.php';
    }

    protected function setUp(): void
    {
        test_reset_database();
        $_SESSION = [];
        $_SERVER['REMOTE_ADDR'] = '203.0.113.10';
    }

    private function validEmail(): string
    {
        return 'jane.doe@' . STUDENT_EMAIL_DOMAIN;
    }

    public function testRegisterCreatesNewStudentAndLogsThemIn(): void
    {
        $result = register_student('Jane Doe', $this->validEmail(), 'correcthorse', 1, 1);

        $this->assertSame(STUDENT_AUTH_OK, $result);
        $this->assertNotEmpty($_SESSION['student_id'] ?? null);

        $stmt = db()->prepare('SELECT full_name, email, program_id, level_id FROM users WHERE email = ?');
        $stmt->execute([$this->validEmail()]);
        $row = $stmt->fetch();

        $this->assertNotFalse($row);
        $this->assertSame('Jane Doe', $row['full_name']);
        $this->assertSame(1, (int) $row['program_id']);
        $this->assertSame(1, (int) $row['level_id']);
    }

    public function testRegisterHashesThePasswordNeverStoresItPlain(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);

        $stmt = db()->prepare('SELECT password_hash FROM users WHERE email = ?');
        $stmt->execute([$this->validEmail()]);
        $hash = $stmt->fetchColumn();

        $this->assertNotSame('correcthorse', $hash);
        $this->assertTrue(password_verify('correcthorse', $hash));
    }

    public function testRegisterRejectsNonSchoolEmail(): void
    {
        $result = register_student('Jane Doe', 'jane@gmail.com', 'correcthorse', null, null);
        $this->assertSame(STUDENT_AUTH_INVALID_EMAIL, $result);
    }

    public function testRegisterRejectsWeakPassword(): void
    {
        $result = register_student('Jane Doe', $this->validEmail(), 'short', null, null);
        $this->assertSame(STUDENT_AUTH_WEAK_PASSWORD, $result);
    }

    public function testRegisterRejectsMissingName(): void
    {
        $result = register_student('   ', $this->validEmail(), 'correcthorse', null, null);
        $this->assertSame(STUDENT_AUTH_MISSING_NAME, $result);
    }

    public function testRegisterRejectsAlreadyActivatedEmail(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);
        $_SESSION = [];

        $result = register_student('Someone Else', $this->validEmail(), 'differentpass', null, null);
        $this->assertSame(STUDENT_AUTH_EMAIL_TAKEN, $result);
    }

    public function testLoginWithCorrectCredentialsSucceeds(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);
        $_SESSION = [];

        $result = login_student($this->validEmail(), 'correcthorse');
        $this->assertSame(STUDENT_AUTH_OK, $result);
        $this->assertNotEmpty($_SESSION['student_id'] ?? null);
    }

    public function testLoginWithWrongPasswordFails(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);
        $_SESSION = [];

        $result = login_student($this->validEmail(), 'totally-wrong');
        $this->assertSame(STUDENT_AUTH_WRONG_PASSWORD, $result);
        $this->assertArrayNotHasKey('student_id', $_SESSION);
    }

    public function testLoginWithUnknownEmailFailsTheSameWayAsWrongPassword(): void
    {
        // Must not distinguish "no such account" from "wrong password" —
        // that would let an attacker enumerate registered emails.
        $result = login_student('nobody@' . STUDENT_EMAIL_DOMAIN, 'whatever1');
        $this->assertSame(STUDENT_AUTH_WRONG_PASSWORD, $result);
    }

    public function testLoginLocksOutAfterTooManyFailedAttempts(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);
        $_SESSION = [];

        // Limit is 8 attempts per 15 minutes (see login_student()).
        for ($i = 0; $i < 8; $i++) {
            login_student($this->validEmail(), 'wrong-password');
        }

        $result = login_student($this->validEmail(), 'correcthorse'); // even the RIGHT password, 9th try
        $this->assertSame(STUDENT_AUTH_LOCKED, $result);
    }

    public function testCurrentStudentReturnsNullWhenLoggedOut(): void
    {
        $this->assertNull(current_student());
    }

    public function testCurrentStudentReturnsProfileAfterLogin(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);

        $student = current_student();
        $this->assertNotNull($student);
        $this->assertSame('Jane Doe', $student['full_name']);
        $this->assertSame($this->validEmail(), $student['email']);
    }

    public function testLogoutClearsTheSession(): void
    {
        register_student('Jane Doe', $this->validEmail(), 'correcthorse', null, null);
        $this->assertNotNull(current_student());

        logout_student();
        $this->assertNull(current_student());
    }
}
