<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Option extends Model
{
    use HasFactory;
    protected $fillable = ['system_option_id', 'value', 'no_of_pcs_in_cotton', 'price_of_cotton'];
    public function systemOption()
    {
        return $this->belongsTo(SystemOption::class, 'system_option_id');
    }
}
