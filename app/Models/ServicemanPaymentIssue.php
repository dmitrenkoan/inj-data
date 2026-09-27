<?php

namespace App\Models;

use App\Enums\PaymentIssueStatus;
use App\Enums\PaymentIssueType;
use Database\Factories\ServicemanPaymentIssueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['serviceman_id', 'type', 'description', 'status'])]
class ServicemanPaymentIssue extends Model
{
    /** @use HasFactory<ServicemanPaymentIssueFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => PaymentIssueType::class,
            'status' => PaymentIssueStatus::class,
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
