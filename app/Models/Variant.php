<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Variant extends Model
{
    protected $fillable = [
        'title', 'description'
    ];

    public function product_variants(){
        return $this->hasMany(ProductVariant::class, 'variant_id')->distinct()->selectRaw('variant, variant_id');
    }
}
