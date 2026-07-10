<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name',
        'company',
        'department',
        'street',
        'city',
        'zip',
        'country',
        'phone',
        'email',
        'vat_number',
    ];

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Display label for dropdowns.
     */
    public function getDisplayNameAttribute(): string
    {
        return $this->company
            ? "{$this->name} — {$this->company}"
            : $this->name;
    }
}
