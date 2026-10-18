<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KasirTransaksi extends Model
{
    protected $table = 'kasir_transaksis';

    protected $fillable = [
        'reference',
        'submission_key',
        'completed_at',
        'total',
        'amount_received',
        'change_amount',
        'payment_method',
    ];

    protected function casts(): array
    {
        return [
            'completed_at' => 'datetime',
            'total' => 'integer',
            'amount_received' => 'integer',
            'change_amount' => 'integer',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(KasirTransaksiItem::class, 'kasir_transaksi_id');
    }
}
