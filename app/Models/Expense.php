<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Expense extends Model
{
    use HasFactory;
    protected $fillable = ['name','category_id','starting_date','type','description','amount','next_recreate_date','is_cron'];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
