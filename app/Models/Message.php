<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'message',
        'image',
        'read',
    ];

    /**
     * Return the patient that sent the message
     */
    public function fromPatient()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    /**
     * Return the med_provider that sent the message
     */
    public function fromMed_provider()
    {
        return $this->belongsTo(User::class, 'from_id');
    }

    /**
     * Return the patient that received the message
     */
    public function toPatient()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    /**
     * Return the med_provider that received the message
     */
    public function toMed_provider()
    {
        return $this->belongsTo(User::class, 'to_id');
    }

    /**
     * Return the image associated with the message
     */
    public function images()
    {
        return $this->morphOne(Image::class, 'imageable');
    }
}
