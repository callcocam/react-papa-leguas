<?php
/**
 * Created by Claudio Campos.
 * User: callcocam@gmail.com, contato@sigasmart.com.br
 * https://www.sigasmart.com.br
 */
namespace Callcocam\ReactPapaLeguas\Shinobi\Models;

use Callcocam\ReactPapaLeguas\Shinobi\Concerns\HasPermissions;
use Callcocam\ReactPapaLeguas\Shinobi\Contracts\Role as RoleContract;
use Callcocam\ReactPapaLeguas\Models\AbstractModel;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Role extends AbstractModel implements RoleContract
{
    use HasPermissions, HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'slug',
        'description',
        'status',
        'special',
        'user_id',
        'tenant_id',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'special' => 'boolean',
    ];

    /**
     * Create a new Role instance.
     */
    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('shinobi.tables.roles'));
    }

    /**
     * Roles can belong to many users.
     *
     * @return Model
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(config('auth.model') ?: config('auth.providers.users.model'))->withTimestamps();
    }

    /**
     * Determine if role has permission flags.
     * 
     * @return bool
     */
    public function hasPermissionFlags(): bool
    {
        return ! is_null($this->special);
    }

    /**
     * Determine if the requested permission is permitted or denied
     * through a special role flag.
     * 
     * @return bool
     */
    public function hasPermissionThroughFlag(): bool
    {
        if ($this->hasPermissionFlags()) {
            return ! ($this->special === 'no-access');
        }

        return true;
    }
}
