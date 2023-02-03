<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RecoveryPlan extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tracker',
        'slug',
        'details',
        'frequency',
        'times',
        'start_date',
        'end_date',
        'med_provider_id',
        'patient_id',
    ];
}
