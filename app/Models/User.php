<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserRole;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'brigade_id', 'battalion_id'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
        ];
    }

    /**
     * @return BelongsTo<Brigade, $this>
     */
    public function brigade(): BelongsTo
    {
        return $this->belongsTo(Brigade::class);
    }

    /**
     * @return BelongsTo<Battalion, $this>
     */
    public function battalion(): BelongsTo
    {
        return $this->belongsTo(Battalion::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SuperAdmin;
    }

    public function isBrigade(): bool
    {
        return $this->role === UserRole::Brigade;
    }

    public function isBattalion(): bool
    {
        return $this->role === UserRole::Battalion;
    }

    /**
     * Scope the query to the users who may be assigned as a curator by
     * the given (currently authenticated) user: battalion users see users
     * of their own battalion, brigade users see users of their own brigade,
     * everyone else (super admin) sees all brigade/battalion users.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeAvailableAsCuratorFor(Builder $query, User $currentUser): Builder
    {
        if ($currentUser->isBattalion()) {
            return $query->where('battalion_id', $currentUser->battalion_id);
        }

        if ($currentUser->isBrigade()) {
            return $query->where('brigade_id', $currentUser->brigade_id);
        }

        return $query->whereIn('role', [UserRole::Brigade, UserRole::Battalion]);
    }
}
