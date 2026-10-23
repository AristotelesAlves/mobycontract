<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->string('jti_access', 128)->nullable()->change();
            $table->string('jti_refresh', 128)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table): void {
            $table->uuid('jti_access')->nullable()->change();
            $table->uuid('jti_refresh')->nullable()->change();
        });
    }
};
