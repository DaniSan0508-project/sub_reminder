<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserSubscription extends Model
{
    protected $fillable = [
        'user_id',
        'service_id',
        'price',
        'renewal_date',
        'renewal_period_value',
        'renewal_period_unit',
        'notify_before_value',
        'notify_before_unit',
        'notification_date',
    ];


    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
