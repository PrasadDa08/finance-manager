<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_id', 'account_id', 'category_id', 'type', 'amount', 'note', 'day_of_month', 'start_month', 'end_month', 'is_active'])]

class RecurringTemplate extends Model
{
    protected $casts = [
        'amount' => 'decimal:2',
        'day_of_month' => 'integer',
        'start_month' => 'date',
        'end_month' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function runs()
    {
        return $this->hasMany(RecurringRun::class);
    }
}
