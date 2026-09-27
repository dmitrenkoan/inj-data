<?php

namespace App\Models;

use Database\Factories\ServicemanAwardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['serviceman_id', 'name', 'submission_date', 'awarded_date'])]
class ServicemanAward extends Model
{
    /** @use HasFactory<ServicemanAwardFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'submission_date' => 'date',
            'awarded_date' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Serviceman, $this>
     */
    public function serviceman(): BelongsTo
    {
        return $this->belongsTo(Serviceman::class);
    }
}
