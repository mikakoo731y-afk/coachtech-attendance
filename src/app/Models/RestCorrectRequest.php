<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RestCorrectRequest extends Model
{
    protected $fillable = [
        'attendance_correct_request_id',
        'proposed_start_time',
        'proposed_end_time',
    ];
}
