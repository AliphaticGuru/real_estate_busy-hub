<?php

namespace App\Models;

use App\Enums\RoleName;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'label'])]
class Role extends Model
{
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'name' => RoleName::class,
        ];
    }

    /**
     * Users that belong to this role.
     *
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get the role record for the given role name, creating it if needed.
     */
    public static function forName(RoleName $name): self
    {
        return static::firstOrCreate(
            ['name' => $name->value],
            ['label' => $name->label()],
        );
    }
}
