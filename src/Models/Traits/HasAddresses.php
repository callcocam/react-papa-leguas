<?php

namespace Callcocam\ReactPapaLeguas\Models\Traits;

use Callcocam\ReactPapaLeguas\Models\Address;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasAddresses
{
    /**
     * Get all addresses for the model.
     */
    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    /**
     * Get the primary address.
     */
    public function primaryAddress()
    {
        return $this->addresses()->where('is_primary', true)->first();
    }

    /**
     * Get addresses by type.
     */
    public function addressesByType(string $type)
    {
        return $this->addresses()->where('type', $type)->get();
    }
}
