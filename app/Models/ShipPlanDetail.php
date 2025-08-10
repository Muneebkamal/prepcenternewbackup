<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShipPlanDetail extends Model
{
    use HasFactory;
    protected $fillable = [
        'ship_plan_id',
        'product_id',
        'boxes',
        'units',
        'expiration',
        'tempalte', // Note: is this a typo? Should it be 'template'?
    ];
    public function product()
    {
        return $this->belongsTo(Products::class);
    }
}
