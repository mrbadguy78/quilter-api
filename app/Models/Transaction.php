<?php

namespace App\Models;

use App\Enums\TransactionType;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    /** @use HasFactory<\Database\Factories\TransactionFactory> */
    use HasFactory;

    protected $fillable = [
        'account_id',
        'type',
        'amount',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'type' => TransactionType::class,
    ];

    protected function amount(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => number_format(
                (float) $value,
                2,
                '.',
                ''
            ),
        );
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
