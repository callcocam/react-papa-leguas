<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Models;

use App\Models\User;
use Callcocam\ReactPapaLeguas\Enums\TenantStatus;
use Callcocam\ReactPapaLeguas\Models\Traits\HasAddresses;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany; 
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tall\Sluggable\HasSlug;
use Tall\Sluggable\SlugOptions;

class Tenant extends Model
{
    use HasUlids, SoftDeletes, HasFactory, HasAddresses, HasSlug;

    /**
     * The attributes that aren't mass assignable.
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'settings' => 'array',
        'status' => TenantStatus::class,
        'is_primary' => 'boolean'
    ];

    /**
     * The relationships that should always be loaded.
     */
    protected $with = ['addresses'];

    /**
     * Get the slug options for the model.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(191);
    }

    /**
     * Get the users that belong to this tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'tenant_id', 'id');
    }

    /**
     * Get the user that created this tenant.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
 
    /**
     * Get the roles for this tenant.
     */
    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }

    /**
     * Scope to find by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Scope to only include active tenants.
     */
    public function scopeActive($query)
    {
        return $query->where('status', TenantStatus::Published);
    }

    /**
     * Route model binding by slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
