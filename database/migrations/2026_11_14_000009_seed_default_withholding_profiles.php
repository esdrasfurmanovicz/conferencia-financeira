<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Perfis de retenção mais comuns, pelo tipo de cliente. São o caso geral: cada pessoa
     * ajusta o que for diferente na sua realidade. Só cria o perfil se o nome ainda não existir,
     * e só cria regra para imposto que existe no catálogo.
     *
     * Em cada regra: [código do imposto, incide?, é retido?]
     */
    public function up(): void
    {
        $profiles = [
            [
                'Pessoa física',
                'Cliente pessoa física: não retém impostos federais. Incidem ISS, PIS e COFINS, pagos por você.',
                [['ISS', true, false], ['PIS', true, false], ['COFINS', true, false]],
            ],
            [
                'Lucro Real',
                'Cliente no Lucro Real: retém PIS, COFINS, CSLL e IRRF. O ISS fica por sua conta, salvo se o município exigir retenção.',
                [['ISS', true, false], ['PIS', true, true], ['COFINS', true, true], ['CSLL', true, true], ['IRRF', true, true]],
            ],
            [
                'Lucro Presumido',
                'Cliente no Lucro Presumido: retém PIS, COFINS, CSLL e IRRF. O ISS fica por sua conta, salvo se o município exigir retenção.',
                [['ISS', true, false], ['PIS', true, true], ['COFINS', true, true], ['CSLL', true, true], ['IRRF', true, true]],
            ],
            [
                'Simples Nacional',
                'Cliente no Simples Nacional: não retém tributos federais. Os impostos seguem no DAS.',
                [['DAS', true, false]],
            ],
            [
                'Órgão público',
                'Órgão público: retém IRRF, PIS, COFINS e CSLL. Confira com o contrato se o ISS também é retido.',
                [['ISS', true, false], ['PIS', true, true], ['COFINS', true, true], ['CSLL', true, true], ['IRRF', true, true]],
            ],
        ];

        $now = now();
        $levyIds = DB::table('levies')->pluck('id', 'code');

        foreach ($profiles as [$name, $description, $rules]) {
            if (DB::table('withholding_profiles')->where('name', $name)->exists()) {
                continue;
            }

            $profileId = DB::table('withholding_profiles')->insertGetId([
                'name' => $name,
                'description' => $description,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            foreach ($rules as [$code, $applies, $withheld]) {
                if (! isset($levyIds[$code])) {
                    continue;
                }

                DB::table('withholding_profile_rules')->insert([
                    'withholding_profile_id' => $profileId,
                    'levy_id' => $levyIds[$code],
                    'applies' => $applies,
                    'withheld' => $withheld,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Mantém os perfis: podem já estar ligados a clientes.
    }
};
