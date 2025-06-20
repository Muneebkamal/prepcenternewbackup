<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingTemplate extends Model
{
    use HasFactory;
    protected $fillable = [
        'product_id', 'template_name', 'template_type',
        'units_per_box', 'box_length', 'box_width', 'box_height',
        'box_weight', 'labeling_by','original_pack'
    ];

    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
