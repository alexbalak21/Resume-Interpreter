# Update — Customer Table & Picker

## Task
Add a customers table. When creating a Quote or Invoice, the user can select an existing customer or type a new one. Every customer is automatically saved to the database.

---

## What Changed

### New Files

| File | Description |
|---|---|
| `database/migrations/2026_07_07_000005_create_customers_table.php` | Creates the `customers` table with all fields |
| `database/migrations/2026_07_07_000006_add_customer_id_to_documents_table.php` | Adds `customer_id` foreign key to `documents` |
| `app/Models/Customer.php` | Customer Eloquent model with `displayName` accessor |
| `app/Http/Controllers/CustomerController.php` | index, store (HTML + JSON/AJAX), edit, update, list (API) |
| `resources/views/customers/index.blade.php` | Customer list page with New Customer modal |
| `resources/views/customers/_form.blade.php` | Shared form partial (used in modal and edit page) |
| `resources/views/customers/edit.blade.php` | Edit customer page |

### Modified Files

| File | What Changed |
|---|---|
| `app/Models/Document.php` | Added `customer_id` to fillable, added `customer()` BelongsTo relationship |
| `app/Http/Controllers/DocumentController.php` | `create()` now passes `$customers`; `store()` saves or updates customer automatically; `convert()` passes `convert_customer_id` to session |
| `resources/views/documents/create.blade.php` | Added customer picker dropdown at top; New Customer modal with AJAX save; auto-fill of form fields when customer is selected |
| `routes/web.php` | Added customer routes: index, store, edit, update, and `/api/customers` JSON list |

---

## Commands to Run

```bash
php artisan migrate
php artisan view:clear
```

---

## How It Works

### Select existing customer
- Dropdown at the top of the form lists all saved customers
- Selecting one auto-fills all customer_* fields instantly (JS)

### Type a new customer
- Leave the dropdown on "Type a new customer below"
- Fill the fields manually
- On Save, the customer is automatically stored in the `customers` table
- The document is linked via `customer_id`

### New Customer modal
- Click "New Customer" button in the form header
- Fill the modal fields and click "Save & Select"
- Customer is saved via AJAX (no page reload)
- Dropdown updates and auto-fills the form immediately

### Customer list
- Visit `/customers` to view, add, and edit customers
