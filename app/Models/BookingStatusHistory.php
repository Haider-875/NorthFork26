<?php

namespace AppModels;

use IlluminateDatabaseEloquentFactoriesHasFactory;
use IlluminateDatabaseEloquentModel;

class BookingStatusHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'from_status',
        'to_status',
        'changed_by_user_id',
        'notes',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
