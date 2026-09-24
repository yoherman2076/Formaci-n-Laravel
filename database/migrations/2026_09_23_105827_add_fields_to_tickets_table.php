<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->string('title')->after('id');
            $table->text('description')->nullable()->after('title');
            $table->string('status')->default('open')->after('description');
            $table->foreignId('customer_id')->after('status')->constrained('users')->cascadeOnDelete();
            $table->foreignId('agent_id')->nullable()->after('customer_id')->constrained('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tickets', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('agent_id');
            $table->dropConstrainedForeignId('customer_id');
            $table->dropColumn(['title', 'description', 'status']);
        });
    }
};
