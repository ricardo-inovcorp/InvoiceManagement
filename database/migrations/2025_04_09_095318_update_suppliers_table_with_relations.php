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
        Schema::table('suppliers', function (Blueprint $table) {
            // Remover campos antigos
            $table->dropColumn('state');
            $table->dropColumn('county');
            
            // Adicionar chaves estrangeiras
            $table->foreignId('sector_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('organization_type_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('district_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('county_id')->nullable()->constrained()->onDelete('set null');
            
            // Modificar o nome da empresa para não incluir o tipo de organização (agora separado)
            $table->renameColumn('name', 'company_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            // Remover chaves estrangeiras
            $table->dropForeign(['sector_id']);
            $table->dropForeign(['organization_type_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['county_id']);
            
            $table->dropColumn(['sector_id', 'organization_type_id', 'district_id', 'county_id']);
            
            // Restaurar colunas antigas
            $table->string('state');
            $table->string('county');
            
            // Restaurar nome da coluna
            $table->renameColumn('company_name', 'name');
        });
    }
};
