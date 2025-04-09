<?php

namespace Database\Seeders;

use App\Models\Sector;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            ['name' => 'Agricultura e Pecuária', 'description' => 'Produção agrícola, pecuária e serviços relacionados'],
            ['name' => 'Alimentação e Bebidas', 'description' => 'Indústria alimentar e produção de bebidas'],
            ['name' => 'Automóvel', 'description' => 'Indústria automóvel e componentes'],
            ['name' => 'Comércio', 'description' => 'Comércio a retalho e por grosso'],
            ['name' => 'Construção', 'description' => 'Construção civil e obras públicas'],
            ['name' => 'Educação', 'description' => 'Ensino e formação profissional'],
            ['name' => 'Energia', 'description' => 'Produção e distribuição de energia'],
            ['name' => 'Farmacêutica', 'description' => 'Indústria farmacêutica e biotecnologia'],
            ['name' => 'Hotelaria e Turismo', 'description' => 'Alojamento, restauração e atividades turísticas'],
            ['name' => 'Imobiliário', 'description' => 'Atividades imobiliárias e gestão de propriedades'],
            ['name' => 'Indústria Transformadora', 'description' => 'Transformação de matérias-primas em produtos acabados'],
            ['name' => 'Saúde', 'description' => 'Serviços de saúde e assistência social'],
            ['name' => 'Serviços Financeiros', 'description' => 'Banca, seguros e outras atividades financeiras'],
            ['name' => 'Tecnologia e Informática', 'description' => 'Tecnologias de informação e comunicação'],
            ['name' => 'Telecomunicações', 'description' => 'Serviços de telecomunicações e redes'],
            ['name' => 'Têxtil e Calçado', 'description' => 'Indústria têxtil, vestuário e calçado'],
            ['name' => 'Transportes e Logística', 'description' => 'Transporte de mercadorias e pessoas, logística e armazenagem'],
        ];

        foreach ($sectors as $sector) {
            Sector::create($sector);
        }
    }
}
