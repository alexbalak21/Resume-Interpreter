<?php
/**
 * MiniMarkdown
 * -------------
 * A tiny, dependency-free inline Markdown renderer.
 * We don't need a full Markdown engine like Parsedown here because
 * cv.md uses a very predictable structure (headings, dashes, bold).
 * This class only handles the inline bits: **bold** and plain text.
 *
 * If you later want full Markdown support (tables, links, nested lists,
 * etc.) you can drop in Parsedown.php from https://parsedown.org/
 * and swap MiniMarkdown::inline() calls for $Parsedown->line().
 */
class MiniMarkdown
{
    /** Convert **bold** markers to <strong> and escape HTML. */
    public static function inline(string $text): string
    {
        $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
        $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
        return $text;
    }

    /** Render a "- item" Markdown list block as <li>...</li> items. */
    public static function listItems(string $block): string
    {
        $html = '';
        foreach (preg_split('/\r?\n/', trim($block)) as $line) {
            $line = trim($line);
            if ($line === '') continue;
            if (preg_match('/^-\s+(.*)$/', $line, $m)) {
                $html .= '<li>' . self::inline(trim($m[1])) . "</li>\n";
            }
        }
        return $html;
    }
}
