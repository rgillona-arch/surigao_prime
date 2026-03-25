<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'package_id',
        'customer_name',
        'date',
        'pax',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_proof_path',
        'paid_at',
        'note',
    ];

    protected $casts = [
        'date' => 'date',
        'paid_at' => 'datetime',
    ];

    public function package(): BelongsTo
    {
        return $this->belongsTo(Package::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(BookingDocument::class);
    }
}
