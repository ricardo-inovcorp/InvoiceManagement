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
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->foreignId('article_id')->nullable()->after('invoice_id')
                  ->constrained('articles')->nullOnDelete();
            $table->boolean('is_valid')->default(false)->after('total_price');
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('validation_status', ['pending', 'validated', 'verified'])
                  ->default('pending')
                  ->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoice_items', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->dropColumn(['article_id', 'is_valid']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('validation_status');
        });
    }
};
