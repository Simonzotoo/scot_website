<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class StudentEmailValidationTest extends TestCase
{
    public static function setUpBeforeClass(): void
    {
        require_once dirname(__DIR__, 2) . '/includes/student_auth.php';
    }

    public function testAcceptsAddressOnTheSchoolDomain(): void
    {
        $email = 'jane.doe@' . STUDENT_EMAIL_DOMAIN;
        $this->assertSame(strtolower($email), valid_school_email($email));
    }

    public function testAcceptsMixedCaseDomain(): void
    {
        $email = 'Jane.Doe@' . strtoupper(STUDENT_EMAIL_DOMAIN);
        $this->assertNotNull(valid_school_email($email));
    }

    public function testRejectsAddressOnADifferentDomain(): void
    {
        $this->assertNull(valid_school_email('someone@gmail.com'));
    }

    public function testRejectsMalformedEmail(): void
    {
        $this->assertNull(valid_school_email('not-an-email'));
        $this->assertNull(valid_school_email('missing-at-sign.' . STUDENT_EMAIL_DOMAIN));
    }

    public function testRejectsDomainThatMerelyContainsTheSuffix(): void
    {
        // e.g. someone@evilwiuc-ghana.edu.gh should NOT pass just because it
        // ends with the right characters after the @ — it must be an exact
        // domain match, not a substring match.
        $this->assertNull(valid_school_email('student@fake' . STUDENT_EMAIL_DOMAIN));
    }

    public function testTrimsWhitespace(): void
    {
        $email = '  jane.doe@' . STUDENT_EMAIL_DOMAIN . '  ';
        $this->assertNotNull(valid_school_email($email));
    }
}
