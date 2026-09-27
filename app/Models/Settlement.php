<?php

namespace App\Models;

use Database\Factories\SettlementFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'raion', 'oblast', 'type'])]
class Settlement extends Model
{
    /** @use HasFactory<SettlementFactory> */
    use HasFactory;

    /**
     * @return HasMany<Serviceman, $this>
     */
    public function servicemen(): HasMany
    {
        return $this->hasMany(Serviceman::class, 'facility_settlement_id');
    }

    public function label(): string
    {
        return collect([$this->oblast, $this->raion, $this->name])->filter()->implode(', ');
    }
}
