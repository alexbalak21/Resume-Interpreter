<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Document;
use App\Models\DocumentType;
use App\Models\Product;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // -------------------------------------------------------------------------
    // CREATE FORM
    // -------------------------------------------------------------------------

    public function create(string $slug)
    {
        $type      = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $form      = json_decode(file_get_contents($type->config_path), true);
        $products  = Product::orderBy('name')->get();
        $customers = Customer::orderBy('name')->get();

        return view('documents.create', compact('type', 'form', 'products', 'customers'));
    }

    // -------------------------------------------------------------------------
    // SAVE
    // -------------------------------------------------------------------------

    public function store(Request $request, string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();

        $data = $request->except(['_token', 'customer_id', 'save_customer']);

        // --- Handle customer ---
        $customerId = null;

        if ($request->filled('customer_id')) {
            // Existing customer selected
            $customerId = $request->customer_id;

            // Optionally update the customer fields if they changed
            $customer = Customer::find($customerId);
            if ($customer) {
                $customer->update($this->extractCustomerFields($data));
            }
        } else {
            // No existing customer selected — save as new if name is filled
            $fields = $this->extractCustomerFields($data);
            if (! empty($fields['name'])) {
                $customer   = Customer::create($fields);
                $customerId = $customer->id;
            }
        }

        $data = $this->computeTotals($slug, $data);
        $html = $this->renderHtml($type, $data);

        $reference = $data[$slug . '_number'] ?? $data['invoice_number'] ?? $data['quote_number'] ?? null;

        $parentId = session('convert_from');
        session()->forget(['convert_from', 'convert_data']);

        $document = Document::create([
            'document_type_id' => $type->id,
            'customer_id'      => $customerId,
            'parent_id'        => $parentId,
            'title'            => $type->name . ($reference ? ' #' . $reference : ''),
            'reference'        => $reference,
            'status'           => Document::STATUS_DRAFT,
            'json_data'        => $data,
            'html_snapshot'    => $html,
        ]);

        // If this was a conversion, mark the quote as invoiced
        if ($parentId) {
            Document::find($parentId)?->update(['status' => Document::STATUS_INVOICED]);
        }

        return redirect()->route('documents.show', $document)
            ->with('success', $type->name . ' saved successfully.');
    }

    // -------------------------------------------------------------------------
    // PREVIEW (no save)
    // -------------------------------------------------------------------------

    public function preview(Request $request, string $slug)
    {
        $type = DocumentType::where('slug', $slug)->where('active', true)->firstOrFail();
        $data = $request->except(['_token', 'customer_id', 'save_customer']);
        $data = $this->computeTotals($slug, $data);
        $html = $this->renderHtml($type, $data);

        return response($html);
    }

    // -------------------------------------------------------------------------
    // SHOW
    // -------------------------------------------------------------------------

    public function show(Document $document)
    {
        return response($document->html_snapshot);
    }

    // -------------------------------------------------------------------------
    // HISTORY
    // -------------------------------------------------------------------------

    public function history(Request $request)
    {
        $types        = DocumentType::orderBy('name')->get();
        $selectedSlug = $request->query('type');

        $query = Document::with(['documentType', 'customer'])->orderByDesc('created_at');

        if ($selectedSlug) {
            $query->whereHas('documentType', fn($q) => $q->where('slug', $selectedSlug));
        }

        $documents = $query->paginate(15)->withQueryString();

        return view('documents.history', compact('documents', 'types', 'selectedSlug'));
    }

    // -------------------------------------------------------------------------
    // UPDATE STATUS
    // -------------------------------------------------------------------------

    public function updateStatus(Request $request, Document $document)
    {
        $request->validate([
            'status' => ['required', 'in:draft,sent,accepted,rejected,invoiced,paid,cancelled'],
        ]);

        if ($document->status === Document::STATUS_INVOICED) {
            return back()->with('error', 'This quote has already been converted to an invoice.');
        }

        $document->update(['status' => $request->status]);

        return back()->with('success', 'Status updated to "' . $request->status . '".');
    }

    // -------------------------------------------------------------------------
    // CONVERT QUOTE → INVOICE
    // -------------------------------------------------------------------------

    public function convert(Document $document)
    {
        if (! $document->isQuote()) {
            return back()->with('error', 'Only quotes can be converted.');
        }

        if ($document->status !== Document::STATUS_ACCEPTED) {
            return back()->with('error', 'Only accepted quotes can be converted to an invoice.');
        }

        if ($document->convertedInvoice) {
            return redirect()->route('documents.show', $document->convertedInvoice)
                ->with('error', 'This quote was already converted.');
        }

        $invoiceType = DocumentType::where('slug', 'invoice')->where('active', true)->first();

        if (! $invoiceType) {
            return back()->with('error', 'Invoice template is not installed.');
        }

        $data = $document->json_data;

        if (isset($data['quote_number']) && ! isset($data['invoice_number'])) {
            $data['invoice_number'] = '';
        }

        session([
            'convert_from' => $document->id,
            'convert_data' => $data,
            'convert_customer_id' => $document->customer_id,
        ]);

        return redirect()->route('documents.create', 'invoice')
            ->with('info', 'Quote #' . $document->reference . ' loaded. Review and save the invoice.');
    }

    // -------------------------------------------------------------------------
    // HELPERS
    // -------------------------------------------------------------------------

    private function extractCustomerFields(array $data): array
    {
        return [
            'name'       => $data['customer_name']       ?? '',
            'company'    => $data['customer_company']    ?? null,
            'department' => $data['customer_department'] ?? null,
            'street'     => $data['customer_street']     ?? null,
            'city'       => $data['customer_city']       ?? null,
            'zip'        => $data['customer_zip']        ?? null,
            'country'    => $data['customer_country']    ?? null,
            'phone'      => $data['customer_phone']      ?? null,
            'email'      => $data['customer_email']      ?? null,
            'vat_number' => $data['customer_vat_number'] ?? null,
        ];
    }

    private function computeTotals(string $slug, array $data): array
    {
        if (in_array($slug, ['invoice', 'quote'])) {
            $qty       = (float) ($data['product_quantity']   ?? 0);
            $unitPrice = (float) ($data['product_unit_price'] ?? 0);
            $vatRate   = (float) ($data['vat_rate']           ?? 0);

            $subtotal  = $qty * $unitPrice;
            $vatAmount = $subtotal * ($vatRate / 100);
            $total     = $subtotal + $vatAmount;

            $data['subtotal']   = number_format($subtotal,  2, '.', '');
            $data['vat_amount'] = number_format($vatAmount, 2, '.', '');
            $data['total']      = number_format($total,     2, '.', '');
        }

        return $data;
    }

    private function renderHtml(DocumentType $type, array $data): string
    {
        $html = file_get_contents($type->template_path);
        $css  = file_get_contents(dirname($type->template_path) . '/style.css');

        $html = str_replace('{{style}}', $css, $html);

        $html = preg_replace_callback(
            '/\{\{#(\w+)\}\}(.*?)\{\{\/\1\}\}/s',
            function ($matches) use ($data) {
                $value = trim($data[$matches[1]] ?? '');
                return $value !== '' ? $matches[2] : '';
            },
            $html
        );

        foreach ($data as $key => $value) {
            $html = str_replace('{{' . $key . '}}', htmlspecialchars((string) $value), $html);
        }

        $html = preg_replace('/\{\{.*?\}\}/', '', $html);

        return $html;
    }
}
