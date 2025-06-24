<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */

namespace Callcocam\ReactPapaLeguas\Models;

use App\Models\User; 
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Tall\Sluggable\HasSlug;
use Tall\Sluggable\SlugOptions;
use Callcocam\ReactPapaLeguas\Landlord\BelongsToTenants;
use Callcocam\ReactPapaLeguas\Enums\BaseStatus;

abstract class AbstractModel extends Model
{
    use HasUlids, 
        HasSlug, 
        SoftDeletes, 
        BelongsToTenants;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'status' => BaseStatus::class,
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();
        
        // Auto-set user_id on creating
        static::creating(function ($model) {
            if (auth()->check() && $model->isFillable('user_id') && !$model->user_id) {
                $model->user_id = auth()->id();
            }
        });
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    /**
     * Get the user that owns this model.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the slug options for the model.
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom($this->getSlugSource())
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate()
            ->slugsShouldBeNoLongerThan(191);
    }

    /**
     * Get the source field for slug generation.
     * Override this method in child classes to specify different source.
     */
    protected function getSlugSource(): string
    {
        return 'name';
    }

    /**
     * Scope a query to only include published records.
     */
    public function scopePublished($query)
    {
        return $query->where('status', BaseStatus::Published);
    }

    /**
     * Scope a query to only include draft records.
     */
    public function scopeDraft($query)
    {
        return $query->where('status', BaseStatus::Draft);
    }

    /**
     * Scope a query to find by slug.
     */
    public function scopeBySlug($query, string $slug)
    {
        return $query->where('slug', $slug);
    }

    /**
     * Check if the model is published.
     */
    public function isPublished(): bool
    {
        
        return $this->status === BaseStatus::Published;
    }

    /**
     * Check if the model is draft.
     */
    public function isDraft(): bool
    {
        return $this->status === BaseStatus::Draft;
    }

    /**
     * Publish the model.
     */
    public function publish(): bool
    {
        return $this->update(['status' => BaseStatus::Published]);
    }

    /**
     * Set model as draft.
     */
    public function draft(): bool
    {
        return $this->update(['status' => BaseStatus::Draft]);
    }

    /**
     * Get the table name for the model.
     */
    public static function getTableName(): string
    {
        return (new static())->getTable();
    }

    /**
     * Route model binding by slug.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
