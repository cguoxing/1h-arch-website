<?php
declare(strict_types=1);

function h($value): string
{
    return htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
}

/** 兼容未开启 mbstring 扩展的主机。 */
function oneh_substr(string $value, int $start, int $length): string
{
    return function_exists('mb_substr') ? mb_substr($value, $start, $length, 'UTF-8') : (string) substr($value, $start, $length);
}

function oneh_strlen(string $value): int
{
    return function_exists('mb_strlen') ? mb_strlen($value, 'UTF-8') : (int) preg_match_all('/./us', $value);
}

function oneh_slugify(string $value): string
{
    $slug = strtolower(trim($value));
    $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?: 'project';
    return trim($slug, '-') ?: 'project';
}

function oneh_json_encode($value): string
{
    return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
}

function oneh_redirect(string $url): void
{
    header('Location: ' . $url);
    exit;
}

function oneh_type_labels(): array
{
    return [
        'renovation' => 'Renovation',
        'commercial' => 'Commercial',
        'education' => 'Education',
        'hospitality' => 'Hospitality',
        'landscape' => 'Landscape',
        'interior' => 'Interior',
        'planning' => 'Planning',
        'clc' => 'CLC',
        'art' => 'Art',
        'architecture' => 'Architecture',
        'revitalization' => 'Revitalization',
    ];
}

function oneh_period_labels(): array
{
    return [
        '2009-2015' => '2009-2015',
        '2016-2020' => '2016-2020',
        '2021-2025' => '2021-2025',
        '2026-present' => '2026 - Present',
    ];
}
