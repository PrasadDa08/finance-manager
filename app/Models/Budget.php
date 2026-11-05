<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable('user_id', 'type', 'account_id', 'amount', 'month_start')]

class Budget extends Model
{
    protected $casts = [
        'amount' => 'decimal:2',
        'month_start' => 'date'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
}
