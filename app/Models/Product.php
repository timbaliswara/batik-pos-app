<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    public const TYPE_CLOTHES = 'baju';

    public const TYPE_FABRIC = 'kain';

    public const CLOTHING_SIZES = ['S', 'M', 'L', 'XL', 'XXL'];

    public const FABRIC_SIZES = ['NONE'];

    protected $fillable = [
        'code',
        'name',
        'type',
        'description',
        'price',
        'image',
        'best_seller',
        'low_stock_threshold',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'best_seller' => 'boolean',
    ];

    public function stocks(): HasMany
    {
        return $this->hasMany(ProductStock::class);
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(StockIn::class);
    }

    public function stockOuts(): HasMany
    {
        return $this->hasMany(StockOut::class);
    }

    public function availableSizes(): array
    {
        return $this->type === self::TYPE_CLOTHES
            ? self::CLOTHING_SIZES
            : self::FABRIC_SIZES;
    }
}
