<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PactMember extends Model
{
    protected $fillable = [
        'pact_id', 'user_id', 'role', 'is_financial_participant', 'status', 'joined_at', 'rules_accepted_at', 'accepted_rules_version',
    ];
}