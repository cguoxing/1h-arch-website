<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/helpers.php';

function oneh_sample_projects(): array
{
    return [
        ['id' => 'fuyong', 'title' => 'Fuyong Industrial Zone Comprehensive Renovation', 'category' => 'Renovation', 'type' => 'renovation', 'types' => ['renovation'], 'city' => 'Shenzhen', 'year' => '2016', 'area' => 'Industrial district', 'role' => 'Renewal strategy', 'image' => 'img/001.jpg', 'link' => 'project.php?project=fuyong', 'wechat_link' => 'https://mp.weixin.qq.com/s/Kp-_lkXuwAvLZPvlS8KoHQ', 'summary' => 'A renewal strategy for an industrial district, connecting existing urban fabric with new public and commercial value.', 'lead_text' => '更新既有产业片区，重建城市界面和公共活力。', 'overview_title' => 'Renewing an industrial district into a more connected public environment.', 'overview' => 'Existing industrial fabric is treated as a resource. The project clarifies movement, civic frontage and adaptable commercial ground to give the district a renewed long-term structure.', 'status' => 'sample', 'is_featured' => 1, 'sort_order' => 10],
        ['id' => 'song-qingling', 'title' => 'Song Qingling Kindergarten', 'category' => 'Education', 'type' => 'education', 'types' => ['education'], 'city' => 'Shanghai', 'year' => '2018', 'area' => 'Learning space', 'role' => 'Interior design', 'image' => 'img/002.jpg', 'link' => 'project.php?project=song-qingling', 'wechat_link' => '', 'summary' => 'An education environment organized around safety, clarity, daily learning and spatial warmth.', 'lead_text' => '以安全、秩序和温度回应儿童学习空间。', 'overview_title' => 'A daily learning environment built around safety, clarity and warmth.', 'overview' => 'The interior organizes arrival, learning and play as an easy sequence. Material restraint and clear sightlines support a calm setting for children, teachers and families.', 'status' => 'sample', 'is_featured' => 0, 'sort_order' => 20],
        ['id' => 'four-points', 'title' => 'Four Points by Sheraton Yangjiang', 'category' => 'Hospitality', 'type' => 'hospitality', 'types' => ['hospitality'], 'city' => 'Yangjiang', 'year' => '2023', 'area' => 'Hotel', 'role' => 'Concept design', 'image' => 'img/003.jpg', 'link' => 'project.php?project=four-points', 'wechat_link' => '', 'summary' => 'A hospitality project balancing brand standards, local context and guest experience.', 'lead_text' => '在品牌标准、地域体验与运营效率之间建立平衡。', 'overview_title' => 'Hospitality shaped between brand standards and a local sense of arrival.', 'overview' => 'The project balances operational clarity with an atmosphere rooted in regional character, giving guests a coherent experience from arrival to private stay.', 'status' => 'sample', 'is_featured' => 0, 'sort_order' => 30],
        ['id' => 'sports-innovation', 'title' => 'Sports Innovation Park', 'category' => 'Commercial', 'type' => 'commercial', 'types' => ['commercial'], 'city' => 'Shanghai', 'year' => '2017', 'area' => 'Urban activity park', 'role' => 'Planning + design', 'image' => 'img/004.jpg', 'link' => 'project.php?project=sports-innovation', 'wechat_link' => '', 'summary' => 'A commercial and activity park shaped around sports, public life and district identity.', 'lead_text' => '把运动、商业与公共生活组织为新的片区体验。', 'overview_title' => 'A public commercial district energized by sport, movement and shared activity.', 'overview' => 'Commercial space is organized as a sequence of public rooms. Sport provides the social anchor, while flexible edges support an active district throughout the day.', 'status' => 'sample', 'is_featured' => 0, 'sort_order' => 40],
        ['id' => 'zhangshan', 'title' => 'Zhangshan Village Guesthouse', 'category' => 'Landscape', 'type' => 'landscape', 'types' => ['landscape'], 'city' => 'Lishui', 'year' => '2019', 'area' => 'Culture & tourism', 'role' => 'Landscape + spatial design', 'image' => 'img/005.jpg', 'link' => 'project.php?project=zhangshan', 'wechat_link' => '', 'summary' => 'A culture and tourism project using landscape, village context and hospitality experience as one design system.', 'lead_text' => '以村落环境、景观秩序和住宿体验共同塑造文旅空间。', 'overview_title' => 'A guesthouse experience rooted in village landscape and the pace of the site.', 'overview' => 'Landscape, village context and hospitality are treated as one system. The project frames a measured sequence between approach, communal space and private retreat.', 'status' => 'sample', 'is_featured' => 0, 'sort_order' => 50],
        ['id' => 'experimental-school', 'title' => 'Experimental School Interior', 'category' => 'Interior', 'type' => 'interior', 'types' => ['interior'], 'city' => 'Shanghai', 'year' => '2018', 'area' => 'School interior', 'role' => 'Interior design', 'image' => 'img/006.jpg', 'link' => 'project.php?project=experimental-school', 'wechat_link' => '', 'summary' => 'An interior project for education, combining circulation, learning settings and material clarity.', 'lead_text' => '以流线、学习场景和材料秩序提升校园日常体验。', 'overview_title' => 'An interior system that supports learning through order, movement and material clarity.', 'overview' => 'The project uses circulation as a learning framework, connecting classrooms and shared space through a consistent material palette and adaptable settings.', 'status' => 'sample', 'is_featured' => 0, 'sort_order' => 60],
    ];
}

function oneh_default_settings(): array
{
    return [
        'homeHeroIds' => ['259m', 'song-qingling', 'four-points', 'sports-innovation', 'zhangshan'],
        'projectIndexIds' => ['259m', 'song-qingling', 'four-points', 'sports-innovation'],
        'selectedWorks' => ['featuredId' => '259m', 'smallIds' => ['song-qingling', 'four-points', 'sports-innovation', 'zhangshan']],
        'heroFixedId' => '259m',
        'heroFixedPos' => 0,
        'heroInterval' => 3,
        'randomHeroEnabled' => 0,
        'indexInterval' => 5,
        'randomIndexEnabled' => 0,
        'randomSelectedEnabled' => 0,
        'selectedFeaturedInterval' => 7,
        'selectedSmallInterval' => 4,
        'projectsPage' => [
            'typeFilters' => array_keys(oneh_type_labels()),
            'yearFilters' => array_keys(oneh_period_labels()),
            'defaultYearFilter' => '2016-2020',
            'representativeByType' => ['renovation' => 'fuyong', 'commercial' => 'sports-innovation', 'education' => 'song-qingling', 'hospitality' => 'four-points', 'landscape' => 'zhangshan', 'interior' => 'experimental-school'],
            'representativeByYear' => ['2009-2015' => 'fuyong', '2016-2020' => 'fuyong', '2021-2025' => 'four-points', '2026-present' => 'sports-innovation'],
        ],
        'team' => [
            'founders' => [
                ['name' => 'Avery Lin', 'label' => 'Founding Partner', 'title' => 'Founding Partner / Design Principal', 'photo' => 'img/t2.jpg'],
                ['name' => 'Ming Zhao', 'label' => 'Founding Partner', 'title' => 'Founding Partner / Strategy Director', 'photo' => 'img/t3.jpg'],
            ],
            'designTeam' => [
                ['name' => 'Yuan Chen', 'label' => 'Design', 'title' => 'Design Director', 'photo' => 'img/t4.jpg'],
                ['name' => 'Shan Li', 'label' => 'Architecture', 'title' => 'Associate Director', 'photo' => 'img/t5.jpg'],
                ['name' => 'Wei Xu', 'label' => 'Interiors', 'title' => 'Senior Interior Designer', 'photo' => 'img/t6.jpg'],
                ['name' => 'Qian Zhou', 'label' => 'Architecture', 'title' => 'Project Architect', 'photo' => 'img/t7.jpg'],
                ['name' => 'Rui Sun', 'label' => 'Landscape', 'title' => 'Landscape Designer', 'photo' => 'img/t8.jpg'],
                ['name' => 'Jia Wu', 'label' => 'Delivery', 'title' => 'Project Coordinator', 'photo' => 'img/t9.jpg'],
            ],
        ],
    ];
}


function oneh_unique_project_ids(array $ids, array $allowedIds = [], array $excludeIds = []): array
{
    $allowedLookup = $allowedIds ? array_flip($allowedIds) : [];
    $excludeLookup = array_flip($excludeIds);
    $result = [];
    foreach ($ids as $id) {
        $id = trim((string) $id);
        if ($id === '' || $id === 'fuyong' || isset($excludeLookup[$id])) {
            continue;
        }
        if ($allowedLookup && !isset($allowedLookup[$id])) {
            continue;
        }
        if (!in_array($id, $result, true)) {
            $result[] = $id;
        }
    }
    return $result;
}

function oneh_fill_project_ids(array $ids, array $pool, int $count): array
{
    if ($count <= 0 || !$pool) {
        return [];
    }
    $result = oneh_unique_project_ids($ids, $pool);
    foreach ($pool as $id) {
        if (count($result) >= $count) {
            break;
        }
        if (!in_array($id, $result, true)) {
            $result[] = $id;
        }
    }
    return array_slice($result, 0, $count);
}

function oneh_pick_home_ids(array $settings, string $cacheKey, string $manualKey, array $pool, int $count, int $days, bool $enabled, bool $forceRefresh = false): array
{
    if ($count <= 0 || !$pool) {
        return [];
    }

    $lastUpdate = (int) ($settings['last_update_' . $cacheKey] ?? 0);
    $cacheIds = oneh_unique_project_ids((array) ($settings[$cacheKey . '_ids'] ?? []), $pool);
    $manualIds = oneh_unique_project_ids((array) ($settings[$manualKey] ?? []), $pool);
    $shouldRotate = $forceRefresh || !$cacheIds || (time() - $lastUpdate) >= (max(1, $days) * 86400);

    if ($enabled && $shouldRotate) {
        $picked = $pool;
        shuffle($picked);
        $picked = array_slice($picked, 0, $count);
        oneh_save_setting($cacheKey . '_ids', $picked);
        oneh_save_setting('last_update_' . $cacheKey, time());
        return oneh_fill_project_ids($picked, $pool, $count);
    }

    $source = $enabled ? $cacheIds : $manualIds;
    return oneh_fill_project_ids($source, $pool, $count);
}

function oneh_get_home_section_ids(array $allIds, array $settings, bool $forceRefresh = false): array
{
    $availableIds = oneh_unique_project_ids($allIds);
    $usedIds = [];

    $heroFixedId = trim((string) ($settings['heroFixedId'] ?? '259m'));
    if ($heroFixedId === '' || $heroFixedId === 'fuyong' || !in_array($heroFixedId, $availableIds, true)) {
        $heroFixedId = in_array('259m', $availableIds, true) ? '259m' : ($availableIds[0] ?? '');
    }

    $heroFixedPos = max(0, min(4, (int) ($settings['heroFixedPos'] ?? 0)));
    $heroPool = array_values(array_diff($availableIds, [$heroFixedId]));
    $heroRandomIds = oneh_pick_home_ids(
        $settings,
        'homeHero',
        'homeHeroIds',
        $heroPool,
        4,
        (int) ($settings['heroInterval'] ?? 3),
        !empty($settings['randomHeroEnabled']),
        $forceRefresh
    );
    $heroIds = $heroRandomIds;
    if ($heroFixedId !== '') {
        array_splice($heroIds, $heroFixedPos, 0, $heroFixedId);
    }
    $heroIds = oneh_fill_project_ids($heroIds, $availableIds, 5);
    $usedIds = array_merge($usedIds, $heroIds);

    $indexPool = array_values(array_diff($availableIds, $usedIds));
    $indexIds = oneh_pick_home_ids(
        $settings,
        'projectIndex',
        'projectIndexIds',
        $indexPool,
        4,
        (int) ($settings['indexInterval'] ?? 5),
        !empty($settings['randomIndexEnabled']),
        $forceRefresh
    );
    $usedIds = array_merge($usedIds, $indexIds);

    $selectedConfig = is_array($settings['selectedWorks'] ?? null) ? $settings['selectedWorks'] : [];
    $selectedEnabled = !empty($settings['randomSelectedEnabled']);
    $selectedChanged = false;

    $featuredPool = array_values(array_diff($availableIds, $usedIds));
    $featuredSaved = oneh_unique_project_ids([$selectedConfig['featuredId'] ?? ''], $featuredPool);
    $featuredLastUpdate = (int) ($settings['last_update_selectedFeatured'] ?? 0);
    $featuredShouldRotate = $forceRefresh || !$featuredSaved || (time() - $featuredLastUpdate) >= (max(1, (int) ($settings['selectedFeaturedInterval'] ?? 7)) * 86400);
    if ($selectedEnabled && $featuredShouldRotate && $featuredPool) {
        $featuredPick = $featuredPool;
        shuffle($featuredPick);
        $featuredIds = array_slice($featuredPick, 0, 1);
        oneh_save_setting('last_update_selectedFeatured', time());
        $selectedChanged = true;
    } else {
        $featuredIds = oneh_fill_project_ids($featuredSaved, $featuredPool, 1);
    }
    $usedIds = array_merge($usedIds, $featuredIds);

    $smallPool = array_values(array_diff($availableIds, $usedIds));
    $smallSaved = oneh_unique_project_ids((array) ($selectedConfig['smallIds'] ?? []), $smallPool);
    $smallLastUpdate = (int) ($settings['last_update_selectedSmall'] ?? 0);
    $smallShouldRotate = $forceRefresh || !$smallSaved || (time() - $smallLastUpdate) >= (max(1, (int) ($settings['selectedSmallInterval'] ?? 4)) * 86400);
    if ($selectedEnabled && $smallShouldRotate && $smallPool) {
        $smallPick = $smallPool;
        shuffle($smallPick);
        $smallIds = array_slice($smallPick, 0, 4);
        oneh_save_setting('last_update_selectedSmall', time());
        $selectedChanged = true;
    } else {
        $smallIds = oneh_fill_project_ids($smallSaved, $smallPool, 4);
    }

    if ($selectedChanged) {
        oneh_save_setting('selectedWorks', [
            'featuredId' => $featuredIds[0] ?? '',
            'smallIds' => $smallIds,
        ]);
    }

    return [
        'heroIds' => $heroIds,
        'projectIndexIds' => $indexIds,
        'selectedIds' => array_values(array_filter(array_merge($featuredIds, $smallIds))),
    ];
}

function oneh_normalize_project(array $project): array
{
    if (($project['id'] ?? '') === '259m') {
        $project['image'] = 'project-uploads/259m-20260718094233-0.webp';
    }
    $types = $project['types'] ?? null;
    if (!is_array($types)) {
        $decoded = json_decode((string) ($project['types_json'] ?? ''), true);
        $types = is_array($decoded) ? $decoded : array_filter(array_map('trim', explode(',', (string) ($project['type'] ?? ''))));
    }
    $project['types'] = array_values($types ?: [$project['type'] ?? 'renovation']);
    $project['type_attribute'] = implode(' ', $project['types']);
    $project['link'] = $project['link'] ?: 'project.php?project=' . $project['id'];
    $project['lead_text'] = $project['lead_text'] ?? '';
    $project['wechat_link'] = $project['wechat_link'] ?? '';
    $project['images'] = $project['images'] ?? [];
    if (!$project['images'] && !empty($project['image'])) {
        $project['images'] = [['src' => $project['image'], 'caption' => $project['title']]];
    }
    return $project;
}

/** 同一次请求内缓存，保存设置后自动失效。 */
function oneh_settings_cache(?array $value = null, bool $reset = false): ?array
{
    static $cache = null;
    if ($reset) {
        $cache = null;
    } elseif ($value !== null) {
        $cache = $value;
    }
    return $cache;
}

function oneh_get_settings(): array
{
    $cached = oneh_settings_cache();
    if ($cached !== null) {
        return $cached;
    }
    $settings = oneh_default_settings();
    $rows = oneh_db_rows("SELECT setting_key, value_json FROM site_settings");
    foreach ($rows as $row) {
        $decoded = json_decode((string) $row['value_json'], true);
        if ($decoded !== null) {
            $settings[$row['setting_key']] = $decoded;
        }
    }
    oneh_settings_cache($settings);
    return $settings;
}

function oneh_save_setting(string $key, $value): void
{
    $db = oneh_db();
    if (!$db) {
        return;
    }
    $json = oneh_json_encode($value);
    $stmt = $db->prepare("REPLACE INTO site_settings (setting_key, value_json) VALUES (?, ?)");
    $stmt->bind_param('ss', $key, $json);
    $stmt->execute();
    oneh_settings_cache(null, true);
}

function oneh_fetch_images_for_projects(array $projects): array
{
    if (!$projects) {
        return $projects;
    }
    $ids = array_column($projects, 'id');
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types = str_repeat('s', count($ids));
    $rows = oneh_db_rows("SELECT project_id, src, caption FROM project_images WHERE project_id IN ($placeholders) ORDER BY sort_order ASC, id ASC", $types, $ids);
    $byProject = [];
    foreach ($rows as $row) {
        $byProject[$row['project_id']][] = ['src' => $row['src'], 'caption' => $row['caption']];
    }
    foreach ($projects as &$project) {
        $project['images'] = $byProject[$project['id']] ?? [];
        $project = oneh_normalize_project($project);
    }
    unset($project);
    return $projects;
}

function oneh_get_projects(bool $includeHidden = false): array
{
    static $cache = [];
    $key = $includeHidden ? 'all' : 'published';
    if (!isset($cache[$key])) {
        $cache[$key] = oneh_load_projects($includeHidden);
    }
    return $cache[$key];
}

function oneh_load_projects(bool $includeHidden): array
{
    $statusSql = $includeHidden ? "1=1" : "status = 'published'";
    $rows = oneh_db_rows("SELECT * FROM projects WHERE $statusSql ORDER BY sort_order ASC, year DESC, title ASC");
    if (!$rows && !$includeHidden) {
        return array_map('oneh_normalize_project', oneh_sample_projects());
    }
    if (!$rows && $includeHidden) {
        return array_map('oneh_normalize_project', oneh_sample_projects());
    }
    return oneh_fetch_images_for_projects($rows);
}

function oneh_get_project(string $id, bool $includeHidden = false): ?array
{
    $statusSql = $includeHidden ? '' : " AND status = 'published'";
    $rows = oneh_db_rows("SELECT * FROM projects WHERE id = ?$statusSql LIMIT 1", 's', [$id]);
    if (!$rows) {
        foreach (oneh_sample_projects() as $sample) {
            if ($sample['id'] === $id) {
                return oneh_normalize_project($sample);
            }
        }
        return null;
    }
    $projects = oneh_fetch_images_for_projects($rows);
    return $projects[0] ?? null;
}

function oneh_projects_by_ids(array $ids, array $fallbackProjects, int $limit, bool $fillMissing = true): array
{
    $all = oneh_get_projects(false);
    $byId = [];
    foreach ($all as $project) {
        $byId[$project['id']] = $project;
    }
    foreach ($fallbackProjects as $project) {
        $byId[$project['id']] = $byId[$project['id']] ?? $project;
    }

    $result = [];
    foreach (array_unique($ids) as $id) {
        if (isset($byId[$id])) {
            $result[] = $byId[$id];
        }
    }
    if ($fillMissing) {
        foreach ($byId as $project) {
            if (count($result) >= $limit) {
                break;
            }
            if (!in_array($project['id'], array_column($result, 'id'), true)) {
                $result[] = $project;
            }
        }
    }
    return array_slice($result, 0, $limit);
}

function oneh_seed_samples(): bool
{
    $db = oneh_db();
    if (!$db || !oneh_install_schema()) {
        return false;
    }
    if ((int) oneh_db_value("SELECT COUNT(*) FROM projects") > 0) {
        return true;
    }
    foreach (oneh_sample_projects() as $project) {
        oneh_save_project($project);
    }
    foreach (oneh_default_settings() as $key => $value) {
        oneh_save_setting($key, $value);
    }
    return true;
}

function oneh_save_project(array $project, bool $allowUpdate = true): bool
{
    $db = oneh_db();
    if (!$db) {
        return false;
    }
    $id = $project['id'] ?: oneh_slugify($project['title'] ?: 'project');
    $types = $project['types'] ?? [$project['type'] ?? 'renovation'];
    $typesJson = oneh_json_encode(array_values($types));
    $link = $project['link'] ?? 'project.php?project=' . $id;
    $isFeatured = !empty($project['is_featured']) ? 1 : 0;
    $sortOrder = (int) ($project['sort_order'] ?? 100);
    $fields = [
        $id,
        $project['title'] ?? '',
        $project['category'] ?? '',
        $project['type'] ?? ($types[0] ?? 'renovation'),
        $typesJson,
        $project['city'] ?? '',
        $project['year'] ?? '',
        $project['area'] ?? '',
        $project['role'] ?? '',
        $project['image'] ?? '',
        $link,
        $project['wechat_link'] ?? '',
        $project['summary'] ?? '',
        $project['lead_text'] ?? '',
        $project['overview_title'] ?? '',
        $project['overview'] ?? '',
        $project['status'] ?? 'draft',
        $isFeatured,
        $sortOrder,
    ];
    $columns = "id, title, category, type, types_json, city, year, area, role, image, link, wechat_link, summary, lead_text, overview_title, overview, status, is_featured, sort_order";
    $placeholders = "?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?";
    $sql = $allowUpdate
        ? "INSERT INTO projects ($columns) VALUES ($placeholders)
            ON DUPLICATE KEY UPDATE
            title = VALUES(title),
            category = VALUES(category),
            type = VALUES(type),
            types_json = VALUES(types_json),
            city = VALUES(city),
            year = VALUES(year),
            area = VALUES(area),
            role = VALUES(role),
            image = VALUES(image),
            link = VALUES(link),
            wechat_link = VALUES(wechat_link),
            summary = VALUES(summary),
            lead_text = VALUES(lead_text),
            overview_title = VALUES(overview_title),
            overview = VALUES(overview),
            status = VALUES(status),
            is_featured = VALUES(is_featured),
            sort_order = VALUES(sort_order)"
        : "INSERT INTO projects ($columns) VALUES ($placeholders)";
    $stmt = $db->prepare($sql);
    oneh_bind_params($stmt, 'sssssssssssssssssii', $fields);
    if (!$stmt->execute()) {
        return false;
    }

    if (isset($project['images']) && is_array($project['images'])) {
        $finalImages = [];
        foreach (array_values($project['images']) as $image) {
            $src = is_array($image) ? ($image['src'] ?? '') : (string) $image;
            if ($src === '' || strpos($src, 'blob:') === 0) {
                continue;
            }
            $finalImages[] = [
                'src' => $src,
                'caption' => is_array($image) ? ($image['caption'] ?? '') : '',
            ];
        }

        $existingRows = oneh_db_rows("SELECT id, src FROM project_images WHERE project_id = ?", 's', [$id]);
        $existingBySrc = [];
        foreach ($existingRows as $row) {
            $existingBySrc[$row['src']] = (int) $row['id'];
        }
        $finalSrcs = array_column($finalImages, 'src');

        // 只删除“明确移除”的图片，绝不整表清空，避免旧图被误删/替代
        $deleteStmt = $db->prepare("DELETE FROM project_images WHERE id = ?");
        foreach ($existingBySrc as $src => $rowId) {
            if (!in_array($src, $finalSrcs, true)) {
                $deleteStmt->bind_param('i', $rowId);
                $deleteStmt->execute();
            }
        }

        // 已存在的更新说明/排序，不存在的插入
        $insertStmt = $db->prepare("INSERT INTO project_images (project_id, src, caption, sort_order) VALUES (?, ?, ?, ?)");
        $updateStmt = $db->prepare("UPDATE project_images SET caption = ?, sort_order = ? WHERE project_id = ? AND src = ?");
        foreach ($finalImages as $index => $image) {
            $order = ($index + 1) * 10;
            if (isset($existingBySrc[$image['src']])) {
                $updateStmt->bind_param('siss', $image['caption'], $order, $id, $image['src']);
                $updateStmt->execute();
            } else {
                $insertStmt->bind_param('sssi', $id, $image['src'], $image['caption'], $order);
                $insertStmt->execute();
            }
        }
    }
    return true;
}
