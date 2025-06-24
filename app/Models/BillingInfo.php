<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BillingInfo extends Model
{

    protected $connection = 'mysql_admin';

    protected $table = 'billing_info';

    protected $fillable = [
        'bill_date',
        'bill_total_amount',
        'customer_name',
        'customer_email',
        'customer_phone_no',
        'customer_address',
    ];

    //public $timestamps = false;
}
