<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class)->where('status', CartItem::PENDING)->latest();
    }
}
