<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class SecurityHelpersTest extends TestCase
{
    public function testEEscapesHtmlSpecialChars(): void
    {
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', e('<script>alert(1)</script>'));
        $this->assertSame('Tom &amp; Jerry', e('Tom & Jerry'));
        $this->assertSame('&quot;quoted&quot;', e('"quoted"'));
    }

    public function testENullBecomesEmptyString(): void
    {
        $this->assertSame('', e(null));
    }

    public function testCleanTextStripsTagsAndTrims(): void
    {
        $this->assertSame('Hello World', clean_text('  <b>Hello World</b>  '));
        $this->assertSame('alert(1)', clean_text('<script>alert(1)</script>'));
    }

    public function testCleanTextPreservesInnerPlainText(): void
    {
        $this->assertSame('Past questions & notes', clean_text('Past questions & notes'));
    }

    public function testPaginateFirstPageDefaults(): void
    {
        $_GET = [];
        $p = paginate(45, 20);

        $this->assertSame(1, $p['page']);
        $this->assertSame(20, $p['perPage']);
        $this->assertSame(3, $p['totalPages']);
        $this->assertSame(45, $p['totalRows']);
        $this->assertSame(0, $p['offset']);
    }

    public function testPaginateRespectsRequestedPage(): void
    {
        $_GET = ['page' => '2'];
        $p = paginate(45, 20);

        $this->assertSame(2, $p['page']);
        $this->assertSame(20, $p['offset']);
    }

    public function testPaginateClampsPageAboveTotalPages(): void
    {
        $_GET = ['page' => '999'];
        $p = paginate(45, 20);

        $this->assertSame(3, $p['page']); // last real page, not 999
    }

    public function testPaginateClampsPageBelowOne(): void
    {
        $_GET = ['page' => '-5'];
        $p = paginate(45, 20);

        $this->assertSame(1, $p['page']);
    }

    public function testPaginateWithZeroRowsStillHasOnePage(): void
    {
        $_GET = [];
        $p = paginate(0, 20);

        $this->assertSame(1, $p['totalPages']);
        $this->assertSame(1, $p['page']);
    }
}
