<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->foreignId('facility_settlement_id')->nullable()->after('facility_type')->constrained('settlements')->nullOnDelete();
        });

        // Best-effort match of the old free-text city/oblast to a known settlement.
        DB::table('servicemen')
            ->whereNotNull('facility_city')
            ->select('id', 'facility_city', 'facility_oblast')
            ->orderBy('id')
            ->get()
            ->each(function ($serviceman) {
                $settlement = DB::table('settlements')
                    ->where('name', $serviceman->facility_city)
                    ->when($serviceman->facility_oblast, fn ($query) => $query->where('oblast', $serviceman->facility_oblast))
                    ->orderByRaw('(raion is null) desc')
                    ->orderByRaw("(type = 'місто') desc")
                    ->first();

                if ($settlement) {
                    DB::table('servicemen')->where('id', $serviceman->id)->update(['facility_settlement_id' => $settlement->id]);
                }
            });

        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropColumn(['facility_city', 'facility_oblast']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('servicemen', function (Blueprint $table) {
            $table->string('facility_city')->nullable()->after('facility_type');
            $table->string('facility_oblast')->nullable()->after('facility_city');
        });

        DB::table('servicemen')
            ->whereNotNull('facility_settlement_id')
            ->select('id', 'facility_settlement_id')
            ->get()
            ->each(function ($serviceman) {
                $settlement = DB::table('settlements')->find($serviceman->facility_settlement_id);

                if ($settlement) {
                    DB::table('servicemen')->where('id', $serviceman->id)->update([
                        'facility_city' => $settlement->name,
                        'facility_oblast' => $settlement->oblast,
                    ]);
                }
            });

        Schema::table('servicemen', function (Blueprint $table) {
            $table->dropConstrainedForeignId('facility_settlement_id');
        });
    }
};
