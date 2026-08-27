<?php
declare(strict_types=1);

/**
 * Dropdown/datalist options for the resource filter forms on resources.php
 * and student/resources.php.
 */
function fetch_resource_filter_options(): array
{
    try {
        return [
            'programs'  => db()->query('SELECT id, name FROM programs ORDER BY name')->fetchAll(),
            'levels'    => db()->query('SELECT id, name FROM levels ORDER BY id')->fetchAll(),
            'semesters' => db()->query('SELECT id, name FROM semesters ORDER BY id')->fetchAll(),
            'examYears' => db()->query(
                'SELECT DISTINCT exam_year FROM past_questions WHERE exam_year IS NOT NULL ORDER BY exam_year DESC'
            )->fetchAll(PDO::FETCH_COLUMN),
        ];
    } catch (Throwable $e) {
        error_log('[SCOTSA ResourceFilters] fetch options failed: ' . $e->getMessage());
        return ['programs' => [], 'levels' => [], 'semesters' => [], 'examYears' => []];
    }
}

/**
 * Runs the same filtered, paginated resource search used by both the public
 * and student-portal resource browsers, so the two pages can never drift
 * apart in behavior. $filters accepts: q, program_id, level_id, semester_id,
 * type, exam_month, exam_year (any/all optional, '' or absent = no filter).
 *
 * Only resource types currently open for browsing (see ACTIVE_RESOURCE_TYPES
 * in includes/config.php) are returned — existing rows of other types, if
 * any, stay in the DB but are hidden from the catalog for now.
 *
 * Returns ['resources' => array, 'pagination' => array (see paginate())].
 */
function search_resources(array $filters, int $perPage): array
{
    try {
        $activeTypePlaceholders = implode(',', array_fill(0, count(ACTIVE_RESOURCE_TYPES), '?'));
        $where = "WHERE pq.status = \"active\" AND pq.resource_type IN ($activeTypePlaceholders)";
        $params = array_keys(ACTIVE_RESOURCE_TYPES);

        if (($filters['q'] ?? '') !== '') {
            $where .= ' AND (c.code LIKE ? OR c.title LIKE ? OR pq.title LIKE ?)';
            $t = '%' . $filters['q'] . '%';
            array_push($params, $t, $t, $t);
        }
        foreach (['program_id' => 'c.program_id', 'level_id' => 'c.level_id', 'semester_id' => 'c.semester_id'] as $key => $col) {
            if (($filters[$key] ?? '') !== '') {
                $where .= " AND {$col} = ?";
                $params[] = (int) $filters[$key];
            }
        }
        if (($filters['type'] ?? '') !== '') {
            $where .= ' AND pq.resource_type = ?';
            $params[] = $filters['type'];
        }
        if (($filters['exam_month'] ?? '') !== '') {
            $where .= ' AND pq.exam_month = ?';
            $params[] = (int) $filters['exam_month'];
        }
        if (($filters['exam_year'] ?? '') !== '') {
            $where .= ' AND pq.exam_year = ?';
            $params[] = (int) $filters['exam_year'];
        }

        $joins = 'FROM past_questions pq
                  JOIN courses c ON c.id = pq.course_id
                  JOIN programs p ON p.id = c.program_id
                  JOIN levels l ON l.id = c.level_id
                  JOIN semesters s ON s.id = c.semester_id';

        $countStmt = db()->prepare("SELECT COUNT(*) $joins $where");
        $countStmt->execute($params);
        $pg = paginate((int) $countStmt->fetchColumn(), $perPage);

        $sql = "SELECT pq.*, c.code, c.title AS course_title, p.name AS program_name, l.name AS level_name, s.name AS semester_name
                $joins
                $where
                ORDER BY pq.created_at DESC
                LIMIT {$pg['perPage']} OFFSET {$pg['offset']}";
        $stmt = db()->prepare($sql);
        $stmt->execute($params);

        return ['resources' => $stmt->fetchAll(), 'pagination' => $pg];
    } catch (Throwable $e) {
        error_log('[SCOTSA ResourceFilters] search failed: ' . $e->getMessage());
        return ['resources' => [], 'pagination' => paginate(0, $perPage)];
    }
}
