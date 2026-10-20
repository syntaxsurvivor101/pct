<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PactSlot extends Model
{
    protected $fillable = ['pact_id', 'position', 'scheduled_date', 'status', 'completed_at'];
}