<?php

namespace AppModels;

use IlluminateDatabaseEloquentFactoriesHasFactory;
use IlluminateDatabaseEloquentModel;
use IlluminateDatabaseEloquentSoftDeletes;

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

    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    public function statusHistories()
    {
        return $this->hasMany(BookingStatusHistory::class);
    }
}
