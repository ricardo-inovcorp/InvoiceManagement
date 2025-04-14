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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique()->comment('Código único do artigo');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('category_id')->nullable()->constrained();
            $table->foreignId('subcategory_id')->nullable()->constrained();
            $table->boolean('active')->default(true);
            $table->timestamps();
            $table->softDeletes(); // Impede que artigos sejam permanentemente excluídos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
