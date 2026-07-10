<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Document extends Model
{
    protected $fillable = [
        'document_type_id',
        'customer_id',
        'title',
        'reference',
        'status',
        'parent_id',
        'json_data',
        'html_snapshot',
    ];

    protected $casts = [
        'json_data' => 'array',
    ];

    const STATUS_DRAFT     = 'draft';
    const STATUS_SENT      = 'sent';
    const STATUS_ACCEPTED  = 'accepted';
    const STATUS_REJECTED  = 'rejected';
    const STATUS_INVOICED  = 'invoiced';
    const STATUS_PAID      = 'paid';
    const STATUS_CANCELLED = 'cancelled';

    public static array $statusColors = [
        'draft'     => 'secondary',
        'sent'      => 'primary',
        'accepted'  => 'success',
        'rejected'  => 'danger',
        'invoiced'  => 'info',
        'paid'      => 'dark',
        'cancelled' => 'warning',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'parent_id');
    }

    public function convertedInvoice(): HasOne
    {
        return $this->hasOne(Document::class, 'parent_id');
    }

    public function isQuote(): bool
    {
        return $this->documentType->slug === 'quote';
    }

    public function isInvoice(): bool
    {
        return $this->documentType->slug === 'invoice';
    }

    public function canBeConverted(): bool
    {
        return $this->isQuote()
            && $this->status === self::STATUS_ACCEPTED
            && is_null($this->convertedInvoice);
    }
}
