<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * One-time bulk import of the official Ukrainian settlement register
     * (KATOTTH, 2024) so the treatment-location field has real data to
     * search against out of the box. Super admins can add further
     * settlements manually afterwards.
     */
    public function up(): void
    {
        $path = database_path('data/ukraine-settlements.json');

        if (! file_exists($path)) {
            return;
        }

        $settlements = json_decode(file_get_contents($path), true);
        $now = now();

        collect($settlements)
            ->map(fn (array $s) => [
                'name' => $s['name'],
                'raion' => $s['raion'],
                'oblast' => $s['oblast'],
                'type' => $s['type'],
                'created_at' => $now,
                'updated_at' => $now,
            ])
            ->chunk(1000)
            ->each(fn ($chunk) => DB::table('settlements')->insert($chunk->all()));
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('settlements')->truncate();
    }
};
