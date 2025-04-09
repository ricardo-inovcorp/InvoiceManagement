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
        Schema::create('counties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 6)->nullable()->unique();
            $table->foreignId('district_id')->constrained()->onDelete('cascade');
            $table->timestamps();
            
            // Um concelho com o mesmo nome pode existir em distritos diferentes
            $table->unique(['name', 'district_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('counties');
    }
};
