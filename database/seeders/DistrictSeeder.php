<?php

namespace Database\Seeders;

use App\Models\District;
use Illuminate\Database\Seeder;

class DistrictSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $districts = [
            ['name' => 'Aveiro', 'code' => 'AV'],
            ['name' => 'Beja', 'code' => 'BE'],
            ['name' => 'Braga', 'code' => 'BR'],
            ['name' => 'Bragança', 'code' => 'BG'],
            ['name' => 'Castelo Branco', 'code' => 'CB'],
            ['name' => 'Coimbra', 'code' => 'CO'],
            ['name' => 'Évora', 'code' => 'EV'],
            ['name' => 'Faro', 'code' => 'FA'],
            ['name' => 'Guarda', 'code' => 'GU'],
            ['name' => 'Leiria', 'code' => 'LE'],
            ['name' => 'Lisboa', 'code' => 'LI'],
            ['name' => 'Portalegre', 'code' => 'PG'],
            ['name' => 'Porto', 'code' => 'PO'],
            ['name' => 'Santarém', 'code' => 'SA'],
            ['name' => 'Setúbal', 'code' => 'SE'],
            ['name' => 'Viana do Castelo', 'code' => 'VC'],
            ['name' => 'Vila Real', 'code' => 'VR'],
            ['name' => 'Viseu', 'code' => 'VI'],
            // Regiões Autónomas
            ['name' => 'Região Autónoma dos Açores', 'code' => 'RAA'],
            ['name' => 'Região Autónoma da Madeira', 'code' => 'RAM'],
        ];

        foreach ($districts as $district) {
            District::create($district);
        }
    }
}
