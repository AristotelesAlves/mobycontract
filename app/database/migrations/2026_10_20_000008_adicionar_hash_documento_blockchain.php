<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contratos', fn (Blueprint $table) => $table->string('documento_hash_blockchain', 66)->nullable());
    }

    public function down(): void
    {
        Schema::table('contratos', fn (Blueprint $table) => $table->dropColumn('documento_hash_blockchain'));
    }
};
