<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PactSlotAllocation extends Model
{
    protected $fillable = ['pact_id', 'pact_slot_id', 'pact_member_id', 'allocation_units'];
}