<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class ResourceFilterTest extends TestCase
{
    private int $itProgramId;
    private int $csProgramId;
    private int $level100Id;
    private int $level200Id;
    private int $semester1Id;

    protected function setUp(): void
    {
        test_reset_database();

        $this->itProgramId = (int) db()->query("SELECT id FROM programs WHERE name = 'Information Technology'")->fetchColumn();
        $this->csProgramId = (int) db()->query("SELECT id FROM programs WHERE name = 'Computer Science'")->fetchColumn();
        $this->level100Id  = (int) db()->query("SELECT id FROM levels WHERE name = 'Level 100'")->fetchColumn();
        $this->level200Id  = (int) db()->query("SELECT id FROM levels WHERE name = 'Level 200'")->fetchColumn();
        $this->semester1Id = (int) db()->query('SELECT id FROM semesters ORDER BY id LIMIT 1')->fetchColumn();

        db()->prepare('INSERT INTO courses (program_id, level_id, semester_id, code, title) VALUES (?, ?, ?, ?, ?)')
            ->execute([$this->itProgramId, $this->level100Id, $this->semester1Id, 'ITC101', 'Intro to IT']);
        $itCourseId = (int) db()->lastInsertId();

        db()->prepare('INSERT INTO courses (program_id, level_id, semester_id, code, title) VALUES (?, ?, ?, ?, ?)')
            ->execute([$this->csProgramId, $this->level200Id, $this->semester1Id, 'CSC202', 'Data Structures']);
        $csCourseId = (int) db()->lastInsertId();

        $this->insertResource($itCourseId, 'past_question', 'ITC101 Past Paper 2024', 8, 2024);
        $this->insertResource($csCourseId, 'lecture_note', 'CSC202 Lecture Notes', 1, 2023);
    }

    private function insertResource(int $courseId, string $type, string $title, ?int $month, ?int $year): void
    {
        db()->prepare(
            "INSERT INTO past_questions (course_id, title, resource_type, exam_month, exam_year, original_filename, file_path, file_size, status)
             VALUES (?, ?, ?, ?, ?, 'test.pdf', 'resources/test.pdf', 1024, 'active')"
        )->execute([$courseId, $title, $type, $month, $year]);
    }

    public function testNoFiltersReturnsEverythingActive(): void
    {
        $result = search_resources([], 12);
        $this->assertCount(2, $result['resources']);
        $this->assertSame(2, $result['pagination']['totalRows']);
    }

    public function testFilterByProgramId(): void
    {
        $result = search_resources(['program_id' => (string) $this->itProgramId], 12);

        $this->assertCount(1, $result['resources']);
        $this->assertSame('ITC101 Past Paper 2024', $result['resources'][0]['title']);
    }

    public function testFilterByResourceType(): void
    {
        $result = search_resources(['type' => 'lecture_note'], 12);

        $this->assertCount(1, $result['resources']);
        $this->assertSame('CSC202 Lecture Notes', $result['resources'][0]['title']);
    }

    public function testFilterByExamMonthAndYear(): void
    {
        $result = search_resources(['exam_month' => '8', 'exam_year' => '2024'], 12);
        $this->assertCount(1, $result['resources']);
        $this->assertSame('ITC101 Past Paper 2024', $result['resources'][0]['title']);
    }

    public function testFilterByExamYearThatMatchesNothing(): void
    {
        $result = search_resources(['exam_year' => '2019'], 12);
        $this->assertCount(0, $result['resources']);
        $this->assertSame(0, $result['pagination']['totalRows']);
    }

    public function testSearchQueryMatchesCourseCode(): void
    {
        $result = search_resources(['q' => 'CSC202'], 12);
        $this->assertCount(1, $result['resources']);
    }

    public function testSearchQueryMatchesResourceTitle(): void
    {
        $result = search_resources(['q' => 'Past Paper'], 12);
        $this->assertCount(1, $result['resources']);
        $this->assertSame('ITC101 Past Paper 2024', $result['resources'][0]['title']);
    }

    public function testArchivedResourcesAreExcluded(): void
    {
        db()->exec("UPDATE past_questions SET status = 'archived' WHERE title = 'CSC202 Lecture Notes'");

        $result = search_resources([], 12);
        $this->assertCount(1, $result['resources']);
        $this->assertSame('ITC101 Past Paper 2024', $result['resources'][0]['title']);
    }

    public function testCombiningFiltersNarrowsResultsFurther(): void
    {
        // Program matches both nothing here since courses differ by program;
        // combine level + type instead, which should isolate just one row.
        $result = search_resources(['level_id' => (string) $this->level100Id, 'type' => 'past_question'], 12);
        $this->assertCount(1, $result['resources']);

        $result2 = search_resources(['level_id' => (string) $this->level100Id, 'type' => 'lecture_note'], 12);
        $this->assertCount(0, $result2['resources']);
    }

    public function testFetchFilterOptionsReturnsExpectedShape(): void
    {
        $options = fetch_resource_filter_options();

        $this->assertArrayHasKey('programs', $options);
        $this->assertArrayHasKey('levels', $options);
        $this->assertArrayHasKey('semesters', $options);
        $this->assertArrayHasKey('examYears', $options);
        $this->assertContains(2024, $options['examYears']);
        $this->assertContains(2023, $options['examYears']);
    }
}
