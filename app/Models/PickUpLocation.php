<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickUpLocation extends Model
{
    use HasFactory; 
    protected $table = "pick_up_locations";

    protected $fillable = [
        'pickup_location',
        'city_id',
        'type',
        'status',
        'city_id'
    ];

    // public $timestamps = false;

    protected $casts = [
        'status' => 'string',
    ];

    public function dropOffLocation()
    {
        return $this->hasMany(DropLocation::class);
    }
    
    public function city()
    {
        return $this->belongsTo(City::class);
    }
}
