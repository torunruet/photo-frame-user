<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $connection = 'frame_admin_db'; // <- use the shared DB
    protected $table = 'devices';

    protected $fillable = ['device_id', 'name', 'location_id', 'phone', 'is_active'];
    public $timestamps = false;
}
