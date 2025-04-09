<?php

namespace Database\Seeders;

use App\Models\OrganizationType;
use Illuminate\Database\Seeder;

class OrganizationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            [
                'name' => 'Sociedade Anónima',
                'abbreviation' => 'S.A.',
                'description' => 'Sociedade comercial cujo capital está dividido em ações e a responsabilidade dos sócios é limitada ao valor das ações subscritas.'
            ],
            [
                'name' => 'Sociedade por Quotas',
                'abbreviation' => 'Lda.',
                'description' => 'Sociedade comercial em que o capital está dividido em quotas e os sócios são solidariamente responsáveis por todas as entradas convencionadas.'
            ],
            [
                'name' => 'Sociedade Unipessoal por Quotas',
                'abbreviation' => 'Unipessoal Lda.',
                'description' => 'Sociedade por quotas com um único sócio, pessoa singular ou coletiva.'
            ],
            [
                'name' => 'Sociedade Gestora de Participações Sociais',
                'abbreviation' => 'SGPS',
                'description' => 'Sociedade que tem por objeto contratual a gestão de participações sociais noutras sociedades.'
            ],
            [
                'name' => 'Entidade Pública Empresarial',
                'abbreviation' => 'E.P.E.',
                'description' => 'Pessoa coletiva de direito público, com natureza empresarial.'
            ],
            [
                'name' => 'Sociedade Anónima de Responsabilidade Limitada',
                'abbreviation' => 'S.A.R.L.',
                'description' => 'Forma societária de responsabilidade limitada, menos comum em Portugal.'
            ],
            [
                'name' => 'Empresário em Nome Individual',
                'abbreviation' => 'ENI',
                'description' => 'Pessoa singular que exerce uma atividade económica sem constituir uma sociedade.'
            ],
            [
                'name' => 'Sociedade em Comandita',
                'abbreviation' => 'S.C.',
                'description' => 'Sociedade em que pelo menos um dos sócios responde ilimitadamente pelas dívidas sociais e os restantes limitadamente.'
            ],
            [
                'name' => 'Cooperativa',
                'abbreviation' => 'Coop.',
                'description' => 'Associação autónoma de pessoas que se unem voluntariamente para satisfazer necessidades económicas, sociais e culturais comuns.'
            ],
            [
                'name' => 'Instituição Particular de Solidariedade Social',
                'abbreviation' => 'IPSS',
                'description' => 'Instituição constituída por iniciativa privada, sem finalidade lucrativa, com o propósito de dar expressão ao dever moral de solidariedade.'
            ],
        ];

        foreach ($types as $type) {
            OrganizationType::create($type);
        }
    }
}
