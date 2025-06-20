<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PrepOrderDetail extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = ['prep_order_id','product_id','fnsku','qty','pack'];
    public function product(){
        return $this->belongsTo(Products::class, 'product_id');
    }
}
