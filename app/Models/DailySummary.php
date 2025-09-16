<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailySummary extends Model
{
    protected $fillable = [
        'user_id',
        'date',
        'pemasukan',
        'pengeluaran',
        'saldo',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'pemasukan' => 'decimal:2',
        'pengeluaran' => 'decimal:2',
        'saldo' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
