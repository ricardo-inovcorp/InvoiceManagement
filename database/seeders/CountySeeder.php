<?php

namespace Database\Seeders;

use App\Models\County;
use App\Models\District;
use Illuminate\Database\Seeder;

class CountySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Para fins de demonstração, vamos adicionar os concelhos apenas para Lisboa
        // Um dataset completo pode ser criado posteriormente para todos os distritos
        
        $lisbonDistrict = District::where('name', 'Lisboa')->first();
        
        if (!$lisbonDistrict) {
            return;
        }
        
        $counties = [
            ['name' => 'Lisboa', 'code' => 'LSB'],
            ['name' => 'Sintra', 'code' => 'SNT'],
            ['name' => 'Cascais', 'code' => 'CSC'],
            ['name' => 'Oeiras', 'code' => 'OER'],
            ['name' => 'Amadora', 'code' => 'AMD'],
            ['name' => 'Odivelas', 'code' => 'ODV'],
            ['name' => 'Loures', 'code' => 'LRS'],
            ['name' => 'Vila Franca de Xira', 'code' => 'VFX'],
            ['name' => 'Mafra', 'code' => 'MFR'],
            ['name' => 'Alenquer', 'code' => 'ALQ'],
            ['name' => 'Torres Vedras', 'code' => 'TVD'],
            ['name' => 'Azambuja', 'code' => 'AZB'],
            ['name' => 'Arruda dos Vinhos', 'code' => 'ARV'],
            ['name' => 'Sobral de Monte Agraço', 'code' => 'SMA'],
            ['name' => 'Lourinhã', 'code' => 'LRN'],
            ['name' => 'Cadaval', 'code' => 'CDV'],
        ];
        
        foreach ($counties as $county) {
            $county['district_id'] = $lisbonDistrict->id;
            County::create($county);
        }
        
        // Adicionar alguns concelhos do Porto para demonstração
        $portoDistrict = District::where('name', 'Porto')->first();
        
        if (!$portoDistrict) {
            return;
        }
        
        $portoCounties = [
            ['name' => 'Porto', 'code' => 'PRT'],
            ['name' => 'Vila Nova de Gaia', 'code' => 'VNG'],
            ['name' => 'Matosinhos', 'code' => 'MTS'],
            ['name' => 'Maia', 'code' => 'MAI'],
            ['name' => 'Gondomar', 'code' => 'GDM'],
            ['name' => 'Valongo', 'code' => 'VLG'],
            ['name' => 'Póvoa de Varzim', 'code' => 'PVZ'],
        ];
        
        foreach ($portoCounties as $county) {
            $county['district_id'] = $portoDistrict->id;
            County::create($county);
        }
    }
}
