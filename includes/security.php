<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function verify_csrf(): void
{
    $token = $_POST['csrf_token'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
        http_response_code(419);
        exit('Invalid security token.');
    }
}

function clean_text(string $value): string
{
    return trim(strip_tags($value));
}

function flash(string $type, string $message): void
{
    $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $messages = $_SESSION['flash'] ?? [];
    unset($_SESSION['flash']);
    return $messages;
}

function client_ip(): string
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

/**
 * Compute page/offset/limit from ?page= against a known row count.
 * Callers run a COUNT(*) first, then use ['offset'] / ['perPage'] in the
 * paginated query.
 */
function paginate(int $totalRows, int $perPage = 20): array
{
    $totalPages = max(1, (int) ceil($totalRows / $perPage));
    $page = max(1, min($totalPages, (int) ($_GET['page'] ?? 1)));

    return [
        'page'       => $page,
        'perPage'    => $perPage,
        'totalPages' => $totalPages,
        'totalRows'  => $totalRows,
        'offset'     => ($page - 1) * $perPage,
    ];
}

/**
 * Render Prev/Next pagination controls. $baseQuery is the current query
 * string with `page` removed (e.g. from http_build_query on $_GET), so
 * active filters survive the page change.
 */
function pagination_links(array $p, string $baseQuery = ''): string
{
    if ($p['totalPages'] <= 1) {
        return '';
    }

    $qs = static fn (int $page): string => '?' . ($baseQuery !== '' ? $baseQuery . '&' : '') . 'page=' . $page;
    $prevDisabled = $p['page'] <= 1;
    $nextDisabled = $p['page'] >= $p['totalPages'];

    $btn = static function (string $href, string $label, bool $disabled): string {
        $cls = 'inline-flex items-center rounded-lg border px-3 py-1.5 text-xs font-bold transition '
            . ($disabled
                ? 'border-slate-100 text-slate-300 cursor-not-allowed dark:border-white/5 dark:text-slate-600'
                : 'border-slate-200 text-slate-600 hover:bg-slate-50 dark:border-white/10 dark:text-slate-300 dark:hover:bg-white/5');
        $attrs = $disabled ? ' aria-disabled="true" tabindex="-1" onclick="return false;"' : '';
        return '<a href="' . e($href) . '" class="' . $cls . '"' . $attrs . '>' . e($label) . '</a>';
    };

    return '<nav class="flex items-center justify-center gap-2 py-2" aria-label="Pagination">'
        . $btn($qs(max(1, $p['page'] - 1)), '‹ Prev', $prevDisabled)
        . '<span class="text-xs font-semibold text-slate-400 dark:text-slate-500 px-2">Page ' . $p['page'] . ' of ' . $p['totalPages'] . '</span>'
        . $btn($qs(min($p['totalPages'], $p['page'] + 1)), 'Next ›', $nextDisabled)
        . '</nav>';
}

