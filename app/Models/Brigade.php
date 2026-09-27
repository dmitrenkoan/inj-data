<?php

namespace App\Models;

use Database\Factories\BrigadeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class Brigade extends Model
{
    /** @use HasFactory<BrigadeFactory> */
    use HasFactory;

    /**
     * @return HasMany<Battalion, $this>
     */
    public function battalions(): HasMany
    {
        return $this->hasMany(Battalion::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Units directly subordinate to this military unit (brigade), i.e. not
     * attached through a battalion.
     *
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }
}
