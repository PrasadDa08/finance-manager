<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;


#[Fillable(['user_id', 'name', 'opening_balance', 'type', 'currency', 'is_active'])]

class Account extends Model
{

    protected $casts = ['opening_balance' => 'decimal:2', 'is_active' => 'boolean',];


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
