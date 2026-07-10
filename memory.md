# Project Memory — Document Flow (Laravel)

## Context

Laravel 12 document generation platform. Goal: a configuration-driven system capable of generating any type of printable document from uploadable template packages — not just invoices.

Single admin user. Auth via Laravel Breeze (login only, no registration).

---

## Architecture (from Laravel_impl.md)

- **Templates** are self-contained packages: `manifest.json`, `template.html`, `style.css`, `form.json`, `preview.png`
- **Forms** are generated dynamically from `form.json` — no hardcoded PHP per document type
- **Document data** is stored as flexible JSON (`json_data`) instead of per-type SQL columns
- **Placeholders** in HTML templates use `{{field_name}}` syntax, replaced at render time

---

## Database Schema

| Table | Key columns |
|---|---|
| `users` | id, name, email, password |
| `document_types` | id, name, slug, version, template_path, config_path, preview_image, active |
| `products` | id, reference, name, price |
| `documents` | id, document_type_id, title, reference, json_data, html_snapshot, timestamps |

---

## Session 1 — Document History Implementation

### Problem
Documents were rendered on the fly and never persisted. No history existed.

### What was built

**Migration** — `2026_07_07_000003_create_documents_table.php`
- `document_type_id` (FK → document_types)
- `title` (string) — auto-generated: `"{Type} #{reference} — {client_name}"`
- `reference` (nullable string) — pulled from `invoice_number`, `quote_number`, or `reference` field
- `json_data` (JSON) — full form submission
- `html_snapshot` (longText) — full rendered HTML saved at generation time

**Model** — `app/Models/Document.php`
- `belongsTo(DocumentType)`
- `json_data` cast to array

**Controller updates** — `DocumentController.php`
- `preview()` now saves to DB after rendering, then returns the HTML as before (no UX change for the user)
- `history()` — lists all documents, filterable by document type slug via `?type=invoice`
- `show(Document $document)` — re-serves the saved HTML snapshot in a new tab

**Routes added** — `routes/web.php`
```
GET  /history              → documents.history
GET  /history/{document}   → documents.show
```

**New view** — `resources/views/documents/history.blade.php`
- Type-filter buttons (All + one per active template)
- Paginated table: Title, Type badge, Reference, Created date, View button
- Empty state with link to dashboard

**Dashboard updates** — `DashboardController.php` + `dashboard.blade.php`
- `DashboardController` now passes `$documentCount` and `$recentDocs` (last 5)
- New "Documents" stat card (clickable → history page)
- "History" button added to the top-right header
- Recent Documents table at the bottom of the dashboard (shows last 5, with "View all N documents →" link if more exist)

### Deploy step
```bash
php artisan migrate
```

---

## File Map (modified/created files)

```
database/migrations/
    2026_07_07_000003_create_documents_table.php   ← NEW

app/Models/
    Document.php                                    ← NEW

app/Http/Controllers/
    DocumentController.php                          ← MODIFIED (preview saves, + history + show)
    DashboardController.php                         ← MODIFIED (+ documentCount, recentDocs)

resources/views/
    dashboard.blade.php                             ← MODIFIED (stat card, recent docs, history btn)
    documents/
        history.blade.php                           ← NEW

routes/
    web.php                                         ← MODIFIED (+ /history routes)
```