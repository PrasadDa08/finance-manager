<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['recurring_template_id', 'user_id', 'month_start', 'status', 'transaction_id'])]

class RecurringRun extends Model
{
    protected $casts = [
        'month_start' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function recurringTemplate()
    {
        return $this->belongsTo(RecurringTemplate::class);
    }

    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }
}
