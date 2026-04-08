<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOut extends Model
{
    protected $fillable = [
        'product_id',
        'size',
        'quantity',
        'transaction_number',
        'buyer_name',
        'note',
        'transaction_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'transaction_date' => 'date',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
