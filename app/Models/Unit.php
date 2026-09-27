<?php

namespace App\Models;

use Database\Factories\UnitFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['battalion_id', 'brigade_id', 'name'])]
class Unit extends Model
{
    /** @use HasFactory<UnitFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<Battalion, $this>
     */
    public function battalion(): BelongsTo
    {
        return $this->belongsTo(Battalion::class);
    }

    /**
     * The military unit (brigade) this unit is directly subordinate to,
     * when it isn't attached to a battalion.
     *
     * @return BelongsTo<Brigade, $this>
     */
    public function brigade(): BelongsTo
    {
        return $this->belongsTo(Brigade::class);
    }

    /**
     * @return HasMany<Serviceman, $this>
     */
    public function servicemen(): HasMany
    {
        return $this->hasMany(Serviceman::class);
    }

    /**
     * Scope the query to the units a given user is allowed to see.
     *
     * @param  Builder<Unit>  $query
     * @return Builder<Unit>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isSuperAdmin()) {
            return $query;
        }

        if ($user->isBrigade()) {
            return $query->forBrigade($user->brigade_id);
        }

        return $query->where('battalion_id', $user->battalion_id);
    }

    /**
     * Scope the query to units belonging to the given military unit
     * (brigade), whether attached directly or through a battalion.
     *
     * @param  Builder<Unit>  $query
     * @return Builder<Unit>
     */
    public function scopeForBrigade(Builder $query, int $brigadeId): Builder
    {
        return $query->where(
            fn (Builder $q) => $q->where('brigade_id', $brigadeId)
                ->orWhereHas('battalion', fn (Builder $battalion) => $battalion->where('brigade_id', $brigadeId)),
        );
    }
}
