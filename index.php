<?php
/**
 * index.php
 * ---------
 * Loads cv.md, splits it into sections, converts each section to HTML,
 * and injects the result into template.html.
 *
 * Edit cv.md to update your CV content — no HTML editing required.
 */

require_once __DIR__ . '/MiniMarkdown.php';

// ----------------------------------------------------------------
// 1. Load and split cv.md into top-level sections ("# SECTION")
// ----------------------------------------------------------------
$mdPath = __DIR__ . '/cv.md';
if (!file_exists($mdPath)) {
    die('cv.md introuvable. Place le fichier à côté de index.php.');
}
$md = file_get_contents($mdPath);

// Global slot store: maps index → real HTML kept safe from htmlspecialchars()
$GLOBALS['__mm_slots'] = [];

function parseFaShortcodes(string $text): string
{
    return preg_replace_callback(
        '/\[fa:(brands|solid|regular):([a-z0-9\-]+)\]/i',
        function ($m) {
            $html  = '<i class="fa-' . $m[1] . ' fa-' . $m[2] . '"></i>';
            $index = count($GLOBALS['__mm_slots']);
            $GLOBALS['__mm_slots'][$index] = $html;
            return '§§' . $index . '§§';  // placeholder — no < or > to escape
        },
        $text
    );
}

// Split BEFORE running shortcodes so section keys stay clean (e.g. "CONTACT" not "§§0§§ CONTACT")
$chunks = preg_split('/^# (.+)$/m', $md, -1, PREG_SPLIT_DELIM_CAPTURE);
$sections      = [];   // key → content body (raw markdown)
$sectionTitles = [];   // key → original title line (may contain [fa:...] shortcodes)
for ($i = 1; $i < count($chunks); $i += 2) {
    $rawTitle = trim($chunks[$i]);
    // Strip any [fa:...] shortcodes from the key so lookups stay predictable
    $key = strtoupper(trim(preg_replace('/\[fa:[^\]]+\]/i', '', $rawTitle)));
    $sectionTitles[$key] = $rawTitle;          // keep original (with icon shortcode)
    $sections[$key]      = trim($chunks[$i + 1]);
}

// Now apply shortcodes to content bodies and title strings
foreach ($sections as $k => $v)      { $sections[$k]      = parseFaShortcodes($v); }
foreach ($sectionTitles as $k => $v) { $sectionTitles[$k] = parseFaShortcodes($v); }

/**
 * Render a section title string: icon (if any) + label text.
 * Used for both aside-title and section-title elements.
 */
function renderSectionTitle(string $raw): string
{
    // $raw may contain §§N§§ placeholders already processed by parseFaShortcodes
    // Restore them via MiniMarkdown::inline so the <i> tag appears
    return MiniMarkdown::inline($raw);
}

// ----------------------------------------------------------------
// 2. Helper: parse "## Title" blocks into timeline items
//    Used for EXPERIENCE and EDUCATION sections.
// ----------------------------------------------------------------
function parseTimelineSection(string $content): array
{
    $blocks = preg_split('/^## (.+)$/m', $content, -1, PREG_SPLIT_DELIM_CAPTURE);
    $items = [];
    for ($i = 1; $i < count($blocks); $i += 2) {
        $title = trim($blocks[$i]);
        $body  = trim($blocks[$i + 1]);

        $metaParts = [];
        $bullets   = [];

        foreach (preg_split('/\r?\n/', $body) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            if (preg_match('/^-\s+(.*)$/', $line, $m)) {
                $bullets[] = trim($m[1]);
            } elseif (preg_match('/^\*\*(.+)\*\*$/', $line, $m)) {
                $metaParts[] = trim($m[1]); // bold date range
            } else {
                $metaParts[] = $line; // e.g. institution / company line
            }
        }

        $items[] = [
            'title'   => $title,
            'meta'    => implode(' — ', $metaParts),
            'bullets' => $bullets,
        ];
    }
    return $items;
}

/** Render timeline items array as the .timeline-item HTML used by the template. */
function renderTimeline(array $items): string
{
    $html = '';
    foreach ($items as $item) {
        $html .= "<div class=\"timeline-item\">\n";
        $html .= '  <h4 class="job-title">' . MiniMarkdown::inline($item['title']) . "</h4>\n";
        $html .= '  <p class="job-meta">' . MiniMarkdown::inline($item['meta']) . "</p>\n";
        if (!empty($item['bullets'])) {
            $html .= "  <ul>\n";
            foreach ($item['bullets'] as $bullet) {
                $html .= '    <li>' . MiniMarkdown::inline($bullet) . "</li>\n";
            }
            $html .= "  </ul>\n";
        }
        $html .= "</div>\n";
    }
    return $html;
}

// ----------------------------------------------------------------
// 3. Build each placeholder's HTML from its Markdown section
// ----------------------------------------------------------------
$placeholders = [];

// --- SECTION TITLES (supports [fa:...] icons in # headings) ---
// For every known section, expose a {{SECTION_TITLE}} placeholder
// that renders the icon + label from the markdown heading.
// French display labels used as fallback when the heading has no icon shortcode
$sectionLabels = [
    'CONTACT'        => 'Contact',
    'SKILLS'         => 'Compétences',
    'CERTIFICATIONS' => 'Certifications',
    'LANGUAGES'      => 'Langues',
    'HOBBIES'        => 'Intérêts',
    'EXPERIENCE'     => 'Expériences Professionnelles',
    'EDUCATION'      => 'Formations',
    'PROFILE'        => 'Profil',
];
foreach ($sectionLabels as $sec => $label) {
    if (isset($sectionTitles[$sec])) {
        // Heading existed in cv.md — render it (may contain icon + text or just text)
        $placeholders[$sec . '_TITLE'] = renderSectionTitle($sectionTitles[$sec]);
    } else {
        // Section has no custom heading — use the French default
        $placeholders[$sec . '_TITLE'] = htmlspecialchars($label, ENT_QUOTES, 'UTF-8');
    }
}

// --- HEADER (name, job title, links) ---
$headerLines = preg_split('/\r?\n/', $sections['HEADER'] ?? '');
$headerLines = array_values(array_filter(array_map('trim', $headerLines), fn($l) => $l !== ''));

$placeholders['FULL_NAME'] = MiniMarkdown::inline($headerLines[0] ?? '');
$placeholders['JOB_TITLE'] = MiniMarkdown::inline($headerLines[1] ?? '');

$linksHtml = '';
foreach (array_slice($headerLines, 2) as $line) {
    // Expected format: "Label: text | https://url"
    if (preg_match('/^(.+?):\s*(.+?)(?:\s*\|\s*(\S+))?$/', $line, $m)) {
        $label = trim($m[1]);
        $text  = trim($m[2]);
        $url   = isset($m[3]) ? trim($m[3]) : $text;
        $linkIcons = [
            'linkedin'  => '<i class="fa-brands fa-linkedin"></i>',
            'github'    => '<i class="fa-brands fa-github"></i>',
            'twitter'   => '<i class="fa-brands fa-x-twitter"></i>',
            'site web'  => '<i class="fa-solid fa-globe"></i>',
            'portfolio' => '<i class="fa-solid fa-globe"></i>',
        ];
        $icon = '<i class="fa-solid fa-globe"></i>'; // default
        foreach ($linkIcons as $keyword => $faIcon) {
            if (stripos($label, $keyword) !== false) { $icon = $faIcon; break; }
        }
        $linksHtml .= '<a href="' . htmlspecialchars($url, ENT_QUOTES) . '" target="_blank">'
                    . '<span class="icon">' . $icon . '</span> ' . MiniMarkdown::inline($text)
                    . "</a>\n";
    }
}
$placeholders['LINKS'] = $linksHtml;

// --- PROFILE ---
$placeholders['PROFILE'] = '<p>' . MiniMarkdown::inline($sections['PROFILE'] ?? '') . '</p>';

// --- CONTACT (FA icon per known label) ---
// Supports optional link: "Label : display text | tel:+33..." or "| mailto:..."
$contactIcons = [
    'téléphone'         => '<i class="fa-solid fa-phone"></i>',
    'email'             => '<i class="fa-solid fa-envelope"></i>',
    'localisation'      => '<i class="fa-solid fa-location-dot"></i>',
    'date de naissance' => '<i class="fa-solid fa-cake-candles"></i>',
    'permis'            => '<i class="fa-solid fa-car"></i>',
];
$contactHtml = '';
foreach (preg_split('/\r?\n/', trim($sections['CONTACT'] ?? '')) as $line) {
    $line = trim($line);
    if ($line === '') continue;
    // Format: "- Label : display text | scheme:value"
    // The scheme (tel, mailto) is captured separately so the colon in "Label :" doesn't clash
    if (!preg_match('/^-\s+(.+?)\s*:\s*(.+?)(?:\s*\|\s*((?:tel|mailto):[^\s]+))?$/u', $line, $m)) continue;
    $label   = trim($m[1]);
    $display = trim($m[2]);
    $href    = isset($m[3]) ? trim($m[3]) : null;
    $icon    = $contactIcons[mb_strtolower($label)] ?? '<i class="fa-solid fa-circle-dot"></i>';
    $text    = MiniMarkdown::inline($display);
    $content = $href
        ? '<a href="' . htmlspecialchars($href, ENT_QUOTES) . '">' . $text . '</a>'
        : $text;
    $contactHtml .= '<div class="contact-item"><span class="icon">' . $icon . '</span> '
                  . $content . "</div>\n";
}
$placeholders['CONTACT'] = $contactHtml;

// --- Simple bullet-list sections ---
$placeholders['SKILLS']         = '<ul>' . MiniMarkdown::listItems($sections['SKILLS'] ?? '') . '</ul>';
$placeholders['CERTIFICATIONS'] = '<ul class="no-bullets">' . MiniMarkdown::listItems($sections['CERTIFICATIONS'] ?? '') . '</ul>';
$placeholders['HOBBIES']        = '<ul class="no-bullets">' . MiniMarkdown::listItems($sections['HOBBIES'] ?? '') . '</ul>';

// --- LANGUAGES ("Name — Level" pairs) ---
$langHtml = '';
foreach (preg_split('/\r?\n/', trim($sections['LANGUAGES'] ?? '')) as $line) {
    $line = trim($line);
    if ($line === '' || !preg_match('/^-\s+(.+?)\s*—\s*(.+)$/u', $line, $m)) continue;
    $langHtml .= '<div class="lang-item"><span class="lang-name">' . MiniMarkdown::inline(trim($m[1]))
               . '</span><span class="lang-level">' . MiniMarkdown::inline(trim($m[2])) . "</span></div>\n";
}
$placeholders['LANGUAGES'] = $langHtml;

// --- EXPERIENCE / EDUCATION (timelines) ---
$placeholders['EXPERIENCE'] = renderTimeline(parseTimelineSection($sections['EXPERIENCE'] ?? ''));
$placeholders['EDUCATION']  = renderTimeline(parseTimelineSection($sections['EDUCATION'] ?? ''));

// ----------------------------------------------------------------
// 4. Inject placeholders into template.html and output
// ----------------------------------------------------------------
$template = file_get_contents(__DIR__ . '/template.html');

foreach ($placeholders as $key => $html) {
    $template = str_replace('{{' . $key . '}}', $html, $template);
}

echo $template;
