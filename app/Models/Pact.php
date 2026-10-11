<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Pact extends Model
{
    protected $fillable = [
        'organiser_id', 'name', 'description', 'type', 'currency', 'contribution_amount', 'contribution_frequency', 'collection_method', 'visibility', 'join_method', 'activation_method', 'status', 'min_members', 'max_members', 'planned_slots', 'max_slots_per_member', 'start_date',
    ];

    public function members(): HasMany
    {
        return $this->hasMany(PactMember::class);
    }

    public function rules(): HasOne
    {
        return $this->hasOne(PactRule::class);
    }

    public function slots(): HasMany
    {
        return $this->hasMany(PactSlot::class);
    }
}