<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use SebastianBergmann\Template\Template;

class Products extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'item',
        'asin',
        'msku',
        'fnsku',
        'pack',
        'poly_bag',
        'poly_bag_size',
        'shrink_wrap',
        'shrink_wrap_size',
        'no_of_pcs_in_carton',
        'carton_size',
        'ti_in_item_page',
        'label_1',
        'label_2',
        'label_3',
        'packing_link',
        'cotton_size_sales',
        'weight_lb',
        'weight_oz',
        'image',
        'deleted_at',
        'created_at',
        'updated_at',
    ];
    public function dailyInputDetails()
    {
        return $this->hasMany(DailyInputDetail::class, 'fnsku', 'fnsku')->with('dailyInput');
    }
    public function templates()
    {
        return $this->hasMany(PackingTemplate::class, 'product_id', 'id');
    }
}
