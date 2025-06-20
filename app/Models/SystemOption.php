<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SystemOption extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'type']; // Add other fields as needed

    public function options()
    {
        return $this->hasMany(Option::class, 'system_option_id');
    }
}
