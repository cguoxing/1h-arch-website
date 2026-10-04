<?php
declare(strict_types=1);

require_once __DIR__ . '/projects.php';

/**
 * 全站可编辑文案的默认值（也是后台编辑表单的结构定义）。
 * 后台保存的内容存放在 site_settings.content，读取时与默认值深度合并，
 * 所以后续新增字段不会导致旧数据缺字段。
 */
function oneh_default_content(): array
{
    return [
        'site' => [
            'name' => '1+H Integrated Design',
            'url' => 'https://www.1h-arch.com',
            'description' => '1+H Integrated Design is a multidisciplinary practice for architecture, interior, planning, landscape and renewal.',
            'og_image' => 'img/001.jpg',
            'icp' => '沪ICP备10036719号',
            'icp_url' => 'https://beian.miit.gov.cn/',
            'notify_email' => 'info@1h-arch.com',
        ],
        'home' => [
            'seo_title' => '1+H | Shaping a Better Space',
            'seo_description' => '1+H Integrated Design is a multidisciplinary practice for architecture, interior, planning, landscape and renewal.',
            'hero_title' => 'Shaping a better Space.',
            'hero_lead' => '塑建一个更好的空间：以建筑、室内、规划与景观的综合改造设计，为城市、乡村、商业与生活场景创造清晰而长期的空间价值。',
            'hero_chips' => ['Scale 尺度 · City / Architecture / Interior', 'Method 方法 · Strategy / Design / Delivery'],
            'board' => [
                'eyebrow' => 'Project Index',
                'title' => 'Selected works as an active index.',
                'title_cn' => '链接设计项目。',
                'lead' => 'Click or tab through the project names to update the image, moving point, project facts and current visual logic.',
            ],
            'who' => [
                'eyebrow' => 'Who We Are',
                'title' => 'An integrated design practice for space, city and life.',
                'title_cn' => '面向空间、城市与生活的综合设计机构。',
                'lead' => '1+H works across architecture, interior, urban planning, landscape and renewal. We connect strategic thinking with disciplined execution so each project can move from concept to built value.',
                'card' => '我们把复杂项目转译为清晰的空间秩序：从前期判断、概念策略到深化协同与落地控制。',
            ],
            'works' => [
                'eyebrow' => 'Selected Works',
                'title' => 'Project evidence across renewal, education, hospitality and public life.',
                'lead' => '精选作品覆盖更新改造、生活空间、酒店、商业与文旅场景。',
            ],
            'capabilities' => [
                'eyebrow' => 'Capabilities',
                'title' => 'Capabilities work together, not as separate services.',
                'lead' => '从建筑到室内，从规划到景观，能力之间保持连续判断，减少项目从概念到落地的断点。',
                'items' => [
                    ['title' => 'Architecture 建筑设计', 'text' => 'Building strategy, renovation, facade order and spatial organization.', 'image' => 'img/services.jpg'],
                    ['title' => 'Interior 室内设计', 'text' => 'Workplace, education, hospitality and commercial interiors with clear identity.', 'image' => 'img/blog1.jpg'],
                    ['title' => 'Planning 城市规划', 'text' => 'Urban renewal, district planning and development studies for long-term value.', 'image' => 'img/blog.jpg'],
                    ['title' => 'Landscape 景观设计', 'text' => 'Public realm, site experience and environmental identity across scales.', 'image' => 'img/g7.jpg'],
                ],
            ],
            'why' => [
                'eyebrow' => 'Why 1+H',
                'title' => 'One design judgment from strategy to delivery.',
                'lead' => '1+H 的价值不只在单一风格，而在跨专业协作、长期项目经验和面向落地的判断能力。',
                'items' => [
                    ['title' => 'Integrated capability', 'text' => 'Architecture, interiors, planning and landscape are organized as one spatial system.'],
                    ['title' => 'Cross-disciplinary collaboration', 'text' => '我们在业主、工程、施工与设计团队之间建立共同语言，降低沟通损耗。'],
                    ['title' => 'Design-to-delivery thinking', 'text' => 'Concepts are tested against construction, operations and everyday use before they become images.'],
                    ['title' => 'Long-term project experience', 'text' => '长期面对更新、商业、教育与生活场景，让设计判断更接近真实项目的复杂度。'],
                ],
            ],
            'cta' => [
                'eyebrow' => 'Contact',
                'title' => 'Have a project in mind? Start a conversation with 1+H.',
                'lead' => '如果你正在推进城市更新、商业空间、教育、酒店或复合型空间项目，可以从这里发起合作咨询。',
                'image' => 'img/contact.jpg',
            ],
            'footer' => 'Architecture / Interior / Planning / Landscape / Renewal',
        ],
        'about' => [
            'seo_title' => 'About 1+H | Integrated Design Practice',
            'seo_description' => 'About 1+H Integrated Design: company profile, design philosophy, capabilities, team and credentials.',
            'hero' => [
                'eyebrow' => 'About 1+H',
                'title' => 'Turning complex projects into ordered space through clear judgment.',
                'lead' => '1+H is a multidisciplinary design practice focused on architecture, interiors, urban planning, landscape and renewal.',
                'lead_cn' => '我们以清晰的设计判断连接策略、表达与落地。',
            ],
            'story' => [
                'eyebrow' => 'Company Profile',
                'title' => 'From 2009 to today, 1+H has grown through real projects and cross-disciplinary work.',
                'lead' => '自成立以来，持续服务城市、乡村、综合改造、商业、教育、酒店与生活方式空间，以长期项目经验形成稳定的方法。',
                'timeline' => [
                    ['year' => '2005', 'title' => '1+H Integrated Design', 'text' => 'Since its founding, our firm has focused on urban building renovation. We revitalize historic spaces through targeted upgrades, working closely with clients. We also prioritize environmental protection and old town renewal to create valuable new living spaces.'],
                    ['year' => '2009', 'title' => '正是成立，服务范围进入商业、教育、办公与生活场景，形成从空间策略到体验表达的完整判断链。', 'text' => 'Urban renewal, comprehensive renovation, comprehensive land consolidation, rural revitalization, space upgrading, elderly care & wellness, community commerce, interior design, landscape planning, corporate office, educational institution, hotel design, boutique club and luxury show flat design as well as engineering consulting services.'],
                    ['year' => '2016', 'title' => 'Urban renewal becomes a core practice', 'text' => 'Projects are considered in a wider city context, linking planning, architecture, landscape and operations. Boasting a talented and energetic team, we have delivered numerous high-quality design works since launch.'],
                    ['year' => '2018', 'title' => 'Integrated design as one system', 'text' => 'Driven by innovative design philosophy, we integrate modern urban thinking and professional theories to deliver all-round services and create harmonious, sophisticated spaces.'],
                ],
            ],
            'values' => [
                'eyebrow' => 'Design Philosophy',
                'title' => 'Good design is not decoration. It is a precise response to people, place and use.',
                'lead' => '好的空间来自对城市关系、使用逻辑、商业目标和建造条件的共同理解。1+H 用一套连续判断贯穿从策略到落地的全过程。',
                'items' => [
                    ['title' => 'Integrated design capability 综合设计能力', 'text' => 'We treat architecture, interiors, planning, and landscape as one system instead of separate conversations.'],
                    ['title' => 'Cross-disciplinary collaboration 跨专业协作', 'text' => 'We build a shared language across client, engineering, construction and design teams to reduce friction.'],
                    ['title' => 'Design to delivery 从概念到落地', 'text' => 'A concept only matters if it still works once it is built. We keep the result intact through execution.'],
                    ['title' => 'Long-term value 长期价值', 'text' => 'We care about how a space behaves over time, not only how it photographs on launch day.'],
                ],
            ],
            'team' => [
                'eyebrow' => 'Team',
                'title' => 'A compact team structure keeps each project’s direction, detail and coordination aligned.',
                'lead' => '团队以项目负责人、设计深化与研究策略协同推进，确保概念、技术和落地之间保持一致。',
                'items' => [
                    ['label' => 'Principal', 'title' => 'Design Direction', 'text' => 'Responsible for project direction, concept logic and key reviews.', 'image' => ''],
                    ['label' => 'Design', 'title' => 'Project Development', 'text' => 'Handles design development, spatial detail and day-to-day coordination.', 'image' => ''],
                    ['label' => 'Strategy', 'title' => 'Research & Strategy', 'text' => 'Supports site research, business understanding and pre-design strategy.', 'image' => ''],
                ],
            ],
            'credentials' => [
                'eyebrow' => 'Awards / Press / Partners',
                'title' => 'Credentials support the work with partners, project records and professional recognition.',
                'lead' => '资质与合作信息保持清晰克制，服务于项目判断，而不是堆叠展示。',
                'items' => [
                    ['title' => 'Selected recognition', 'text' => 'Project recognition, publications and professional records can be organized here as the public archive grows.'],
                    ['title' => 'Project experience', 'text' => '城市更新、教育、酒店、商业、办公与文旅项目经验共同构成 1+H 的判断基础。'],
                    ['title' => 'Partners', 'text' => 'Collaboration with owners, developers, institutions, consultants and construction teams keeps delivery grounded.'],
                ],
            ],
            'cta' => [
                'eyebrow' => 'Next Step',
                'title' => 'The clearest way to understand 1+H is through the work.',
                'lead' => '继续进入项目页面，查看不同尺度和类型中的空间判断。',
                'image' => 'img/about.jpg',
            ],
            'footer' => 'Company / Philosophy / Team / Credentials',
        ],
        'team' => [
            'seo_title' => 'Founders & Design Team | 1+H Integrated Design',
            'seo_description' => 'Founders and design team of 1+H Integrated Design across architecture, interiors, renewal and landscape.',
            'eyebrow' => 'Founders & Design Team',
            'title' => 'A focused studio, built around clear responsibility and collaborative delivery.',
            'lead' => '充分沟通的设计哲学，相互认同理念造就因地制宜的空间塑建，形成与自然环境和谐共存的空间作品。我们跨专业协同共同推进项目，在每个阶段保持概念、技术和落地的一致性。',
            'founders_label' => 'Founders',
            'design_label' => 'Design Team',
            'design_note' => '涵盖建筑、室内、城市更新、景观、深化与工程咨询。',
            'footer' => 'Founders / Design Team / Delivery',
        ],
        'projects' => [
            'seo_title' => 'Projects | 1+H Integrated Design',
            'seo_description' => 'Selected projects by 1+H Integrated Design across renovation, education, hospitality, commercial, landscape and interiors.',
            'hero' => [
                'eyebrow' => 'Projects',
                'title' => 'Project evidence across renewal, education, hospitality and public life.',
                'lead' => 'Filter the archive by project type, then inspect the detail panel for location, year, scale and design role.',
                'lead_cn' => '用项目本身说明 1+H 的综合设计能力。',
            ],
            'filter' => [
                'eyebrow' => 'Filter by Type',
                'title' => 'Browse work by commission type and spatial scale.',
            ],
            'downloads' => [
                'eyebrow' => 'Project Downloads',
                'title' => 'Request project materials for deeper review.',
                'lead' => '如需项目册、公司介绍或案例资料，可通过联系页面发起申请。',
                'items' => [
                    ['title' => '2026 Project Casebook', 'text' => 'Selected cases, key visuals and concise project notes for client review.', 'link' => 'contact.php'],
                    ['title' => '1+H Company Profile', 'text' => 'Company background, capability overview and representative project types.', 'link' => 'contact.php'],
                ],
            ],
            'footer' => 'Selected Works / Project Archive / Downloads',
        ],
        'project' => [
            'principles' => [
                'eyebrow' => 'Design Principles',
                'title' => 'A disciplined method for spatial change.',
                'lead' => '设计从现状判断开始，以清晰秩序、使用体验和长期适应性形成完整回应。',
                'items' => [
                    ['title' => 'Read the existing', 'text' => 'Start from site conditions, operational needs and long-term spatial value.'],
                    ['title' => 'Clarify the order', 'text' => 'Use clear circulation, hierarchy and material logic to make complex programs readable.'],
                    ['title' => 'Design for change', 'text' => 'Keep the spatial system adaptable for future use, maintenance and growth.'],
                ],
            ],
            'footer' => 'Selected Works / Project Archive',
        ],
        'contact' => [
            'seo_title' => 'Contact 1+H | Project Inquiry',
            'seo_description' => 'Contact 1+H Integrated Design for architecture, interior, urban renewal, landscape and planning projects.',
            'hero' => [
                'eyebrow' => 'Contact',
                'title' => 'Enquire about Space projects & start discussions with 1+H.',
                'lead' => 'Share the project type, location, timeline and key question. We will review the brief and reply with the next step.',
                'lead_cn' => '欢迎就城市、乡村、全域、更新、商业、教育、酒店、办公与复合型空间项目联系 1+H。',
            ],
            'social' => [
                ['name' => 'WeChat', 'icon' => 'img/social-wechat.svg', 'url' => ''],
                ['name' => 'Xiaohongshu', 'icon' => 'img/social-xhs.svg', 'url' => ''],
                ['name' => 'Weibo', 'icon' => 'img/social-weibo.svg', 'url' => ''],
                ['name' => 'Instagram', 'icon' => 'img/social-instagram.svg', 'url' => ''],
                ['name' => 'Facebook', 'icon' => 'img/social-facebook.svg', 'url' => ''],
            ],
            'channels' => [
                'eyebrow' => 'Contact Information',
                'title' => 'The fastest route is a clear project brief and a direct contact channel.',
                'lead' => '请尽量说明项目地点、类型、阶段和当前最需要判断的问题，便于团队快速回复。',
                'emails' => ['info@1h-arch.com', 'design@1h-arch.com'],
                'phone' => '',
                'address' => '912# West BUILD,HengfengRd. JINGAN District,Shanghai,China',
                'response' => 'We reply within one business day.',
            ],
            'form' => [
                'eyebrow' => 'Inquiry Form',
                'title' => 'Send a concise project inquiry.',
                'lead' => '提交后信息会直接发送给 1+H 团队，我们会在一个工作日内回复。',
                'project_types' => ['Architecture', 'Interior', 'Urban Planning', 'Landscape', 'Commercial', 'Education'],
                'success' => 'Thank you. Your inquiry has been received — we will reply within one business day. 感谢留言，我们会尽快联系你。',
            ],
            'location' => [
                'eyebrow' => 'Location',
                'title' => 'Shanghai base, project work across cities and scenarios.',
                'lead' => '1+H 以上海为基地，深圳与广州分支机构联合，服务不同城市中的更新、商业、教育、酒店与生活方式空间项目。',
                'image' => '',
                'note_title' => 'Before you write',
                'note_text' => 'If you are reaching out for a bid, collaboration or a new project, include the location, type, timeline and main decision point. That helps 1+H respond with something useful faster.',
                'notice' => 'We reply within one business day after receiving a clear brief.',
            ],
            'footer' => 'Contact / Inquiry / Collaboration',
        ],
    ];
}

/**
 * 以默认值为结构做深度合并：
 * - 关联数组：逐键合并，只保留默认值里存在的键；
 * - 列表：整体采用保存值（允许增删条目），每条按默认第一条的结构补齐。
 */
function oneh_merge_content($default, $saved, bool $blankMissing = false)
{
    if (is_array($default)) {
        $isList = $default === [] || array_keys($default) === range(0, count($default) - 1);
        if ($isList) {
            if (!is_array($saved)) {
                return $blankMissing ? [] : $default;
            }
            $template = $default[0] ?? '';
            $result = [];
            foreach (array_values($saved) as $item) {
                // 列表条目里缺失的字段视为空，而不是套用默认文案
                $result[] = oneh_merge_content($template, $item, true);
            }
            return $result;
        }
        $result = [];
        foreach ($default as $key => $value) {
            if (is_array($saved) && array_key_exists($key, $saved)) {
                $result[$key] = oneh_merge_content($value, $saved[$key], $blankMissing);
            } else {
                $result[$key] = $blankMissing ? oneh_merge_content($value, is_array($value) ? [] : '', true) : $value;
            }
        }
        return $result;
    }
    if (is_array($saved)) {
        return $default;
    }
    return trim((string) ($saved ?? ''));
}

function oneh_get_content(?string $section = null): array
{
    static $content = null;
    if ($content === null) {
        $settings = oneh_get_settings();
        $saved = is_array($settings['content'] ?? null) ? $settings['content'] : [];
        $content = oneh_merge_content(oneh_default_content(), $saved);
    }
    if ($section === null) {
        return $content;
    }
    return $content[$section] ?? [];
}

/** 从后台提交的数据里清洗出合法的文案结构。 */
function oneh_sanitize_content(array $posted): array
{
    $clean = oneh_merge_content(oneh_default_content(), $posted);
    // 列表里去掉完全为空的条目
    $prune = function ($value) use (&$prune) {
        if (!is_array($value)) {
            return $value;
        }
        $isList = $value === [] || array_keys($value) === range(0, count($value) - 1);
        $out = [];
        foreach ($value as $key => $item) {
            $item = $prune($item);
            if ($isList) {
                $flat = is_array($item) ? implode('', array_map(function ($v) { return is_array($v) ? '' : (string) $v; }, $item)) : (string) $item;
                if (trim($flat) === '') {
                    continue;
                }
                $out[] = $item;
            } else {
                $out[$key] = $item;
            }
        }
        return $out;
    };
    return $prune($clean);
}
