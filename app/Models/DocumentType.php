<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'version',
        'template_path',
        'config_path',
        'preview_image',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
