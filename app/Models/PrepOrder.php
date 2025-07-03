<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrepOrder extends Model
{
    use HasFactory;
    protected $fillable = ['custom_id', 'employee_id', 'date', 'start_time', 'end_time','name'];

    // Relationship with Employee
    public function employee()
    {
        return $this->belongsTo(User::class,'employee_id');
    }

    // Relationship with User (Created By)
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function details()
    {
        return $this->hasMany(PrepOrderDetail::class, 'prep_order_id','custom_id');
    }

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($order) {
            // Get the last created order to determine the next custom_id
            $latestOrder = self::latest('custom_id')->first();
            // Determine the next ID
            $nextId = $latestOrder ? intval($latestOrder->custom_id) + 1 : 1;
            // Format it as 3-digit (e.g., 001, 002, 003)
            $order->custom_id = str_pad($nextId, 3, '0', STR_PAD_LEFT);
        });
    }

}

