<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'reference',
        'name',
        'description',
        'product_unit',
        'price',
        'page_url',
    ];

    /**
     * Price is stored in cents. This accessor returns a formatted string.
     * e.g. 1000 → "10.00"
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price / 100, 2);
    }
}
