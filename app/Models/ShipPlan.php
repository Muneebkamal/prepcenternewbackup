<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShipPlan extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'custom_id',
        'sku_method',
        'fullment_capability',
        'market_place',
        'show_filter',
        'created_by',
        'updated_by',
        'handling_cost',
        'shipment_fee',
    ];
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
