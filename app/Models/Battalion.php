<?php

namespace App\Models;

use Database\Factories\BattalionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['brigade_id', 'name'])]
class Battalion extends Model
{
    /** @use HasFactory<BattalionFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Brigade, $this>
     */
    public function brigade(): BelongsTo
    {
        return $this->belongsTo(Brigade::class);
    }

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @return HasMany<Unit, $this>
     */
    public function units(): HasMany
    {
        return $this->hasMany(Unit::class);
    }

    /**
     * Scope the query to the battalions a given user is allowed to see.
     *
     * @param  Builder<Battalion>  $query
     * @return Builder<Battalion>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isBrigade()) {
            return $query->where('brigade_id', $user->brigade_id);
        }

        return $query->where('id', $user->battalion_id);
    }
}
