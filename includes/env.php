<?php
declare(strict_types=1);

/**
 * Minimal .env reader, no Composer dependency needed for a handful of
 * deploy-time settings. Values already set in the real environment
 * (e.g. by the hosting panel) always win over the file.
 */
function env(string $key, ?string $default = null): ?string
{
    static $loaded = false;

    if (!$loaded) {
        $path = dirname(__DIR__) . '/.env';
        if (is_file($path) && is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);
                if ($line === '' || $line[0] === '#' || !str_contains($line, '=')) {
                    continue;
                }
                [$k, $v] = explode('=', $line, 2);
                $k = trim($k);
                $v = trim($v);
                if (strlen($v) >= 2 && (
                    ($v[0] === '"' && str_ends_with($v, '"')) ||
                    ($v[0] === "'" && str_ends_with($v, "'"))
                )) {
                    $v = substr($v, 1, -1);
                }
                if (getenv($k) === false) {
                    putenv("$k=$v");
                }
            }
        }
        $loaded = true;
    }

    $value = getenv($key);
    return $value !== false ? $value : $default;
}
