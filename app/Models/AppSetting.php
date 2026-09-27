<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['warning_days', 'attention_days', 'first_contact_days', 'visit_days'])]
class AppSetting extends Model
{
    private static ?self $cached = null;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'warning_days' => 'integer',
            'attention_days' => 'integer',
            'first_contact_days' => 'integer',
            'visit_days' => 'integer',
        ];
    }

    /**
     * The application has a single settings row. Fetch (or lazily create)
     * it, memoized for the lifetime of the request.
     */
    public static function current(): self
    {
        return static::$cached ??= static::query()->firstOrCreate([]);
    }

    public static function forgetCached(): void
    {
        static::$cached = null;
    }
}
