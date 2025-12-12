<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE assets ADD CONSTRAINT assets_amount_non_negative CHECK (amount >= 0)');
        DB::statement('ALTER TABLE assets ADD CONSTRAINT assets_locked_amount_non_negative CHECK (locked_amount >= 0)');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE assets DROP CONSTRAINT IF EXISTS assets_amount_non_negative');
        DB::statement('ALTER TABLE assets DROP CONSTRAINT IF EXISTS assets_locked_amount_non_negative');
    }
};
