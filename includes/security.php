<?php
declare(strict_types=1);

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * A password <input> with a built-in show/hide eye toggle (wired up by the
 * [data-password-toggle] handler in main.js). $id must be unique on the page.
 */
function password_field(string $id, string $name, string $placeholder = '', string $autocomplete = 'current-password', bool $required = true, int $minlength = 0): string
{
    $attrs = $required ? ' required' : '';
    $attrs .= $minlength > 0 ? ' minlength="' . $minlength . '"' : '';

    $eyeOpen = str_replace('<svg ', '<svg data-eye-open ', icon('eye', 'h-4 w-4'));
    $eyeShut = str_replace('<svg ', '<svg data-eye-closed ', icon('eye-slash', 'h-4 w-4 hidden'));

    return '<div class="relative">'
        . '<input id="' . e($id) . '" class="form-input pr-10" type="password" name="' . e($name) . '"'
        . ($placeholder !== '' ? ' placeholder="' . e($placeholder) . '"' : '')
        . ' autocomplete="' . e($autocomplete) . '"' . $attrs . '>'
        . '<button type="button" class="absolute inset-y-0 right-0 flex items-center pr-3 text-slate-400 hover:text-slate-600" '
        . 'data-password-toggle="' . e($id) . '" aria-label="Show password">'
        . $eyeOpen . $eyeShut . '</button>'
        . '</div>';
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
 * Generic per-bucket sliding-window rate limit backed by the DB (works
 * across PHP-FPM workers without needing shared memory). Returns true
 * when the caller should be turned away. Fails open on DB errors so an
 * infra hiccup can't take the whole site down.
 */
function rate_limit_hit(string $bucket, int $maxHits, int $windowSeconds): bool
{
    try {
        $pdo = db();
        $stmt = $pdo->prepare(
            'SELECT COUNT(*) FROM rate_limit_hits WHERE bucket = ? AND hit_at >= (NOW() - INTERVAL ? SECOND)'
        );
        $stmt->execute([$bucket, $windowSeconds]);
        if ((int) $stmt->fetchColumn() >= $maxHits) {
            return true;
        }
        $pdo->prepare('INSERT INTO rate_limit_hits (bucket) VALUES (?)')->execute([$bucket]);
        if (random_int(1, 200) === 1) {
            $pdo->exec('DELETE FROM rate_limit_hits WHERE hit_at < (NOW() - INTERVAL 1 HOUR)');
        }
        return false;
    } catch (Throwable $e) {
        error_log('[SCOTSA RateLimit] check failed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Record an admin action for accountability. Best-effort: a logging
 * failure must never block the action it's describing.
 */
function audit_log(string $action, string $entityType, ?int $entityId, ?string $detail = null): void
{
    try {
        db()->prepare(
            'INSERT INTO audit_log (admin_id, action, entity_type, entity_id, detail, ip_address) VALUES (?, ?, ?, ?, ?, ?)'
        )->execute([$_SESSION['admin_id'] ?? null, $action, $entityType, $entityId, $detail, client_ip()]);
    } catch (Throwable $e) {
        error_log('[SCOTSA Audit] failed to record "' . $action . ' ' . $entityType . '": ' . $e->getMessage());
    }
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

