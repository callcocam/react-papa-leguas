<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Shinobi\Models;

use Callcocam\ReactPapaLeguas\Shinobi\Concerns\RefreshesPermissionCache;
use Callcocam\ReactPapaLeguas\Shinobi\Contracts\Permission as PermissionContract;
use Callcocam\ReactPapaLeguas\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Permission extends AbstractModel implements PermissionContract
{
    use RefreshesPermissionCache, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'user_id',
        'tenant_id',
    ];

    /**
     * Create a new Permission instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('shinobi.tables.permissions'));
    }

    /**
     * Permissions can belong to many roles.
     *
     * @return Model
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(config('shinobi.models.role'))->withTimestamps();
    }
}
