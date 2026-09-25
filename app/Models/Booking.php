<?php

namespace AppModels;
namespace App\Models;

use IlluminateDatabaseEloquentFactoriesHasFactory;
use IlluminateDatabaseEloquentModel;
use IlluminateDatabaseEloquentSoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'booking_number',
        'service_id',
        'service_name',
        'customer_name',
        'customer_email',
        'customer_phone',
        'vehicle_year',
        'vehicle_make',
        'vehicle_model',
        'vehicle_mileage',
        'vehicle_vin',
        'preferred_date',
        'preferred_time',
        'status',
        'notes',
        'admin_notes',
    ];

    protected $casts = [
        'preferred_date' => 'date',
    ];

    protected $appends = [
        'name',
        'email',
        'phone',
        'service',
    ];

    public function getNameAttribute()
    {
        return $this->customer_name;
    }

    public function getEmailAttribute()
    {
        return $this->customer_email;
    }

    public function getPhoneAttribute()
    {
        return $this->customer_phone;
    }

    public function getServiceAttribute()
    {
        return $this->service_name;
    }

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }
}
