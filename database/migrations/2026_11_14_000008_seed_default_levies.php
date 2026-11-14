<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Catálogo básico de impostos que quase todo escritório usa. Só cria o que ainda não existe
     * (pelo código), então não sobrescreve nada que a pessoa já tenha cadastrado ou ajustado.
     * Os pisos seguem a regra de retenção federal: PIS, COFINS e CSLL só são retidos juntos
     * quando somam mais de R$ 10, e o IRRF não é retido abaixo de R$ 10.
     */
    public function up(): void
    {
        $now = now();

        $levies = [
            ['ISS', 'ISS (Imposto sobre Serviços)', null, null],
            ['PIS', 'PIS', 'federais', 10],
            ['COFINS', 'COFINS', 'federais', 10],
            ['CSLL', 'CSLL', 'federais', 10],
            ['IRRF', 'IRRF (Imposto de Renda Retido na Fonte)', 'irrf', 10],
            ['INSS', 'INSS', null, null],
            ['IRPJ', 'IRPJ (Imposto de Renda Pessoa Jurídica)', null, null],
            ['ICMS', 'ICMS', null, null],
            ['IPI', 'IPI', null, null],
            ['DAS', 'Simples Nacional (DAS)', null, null],
        ];

        foreach ($levies as [$code, $name, $group, $floor]) {
            if (DB::table('levies')->where('code', $code)->exists()) {
                continue;
            }

            DB::table('levies')->insert([
                'code' => $code,
                'name' => $name,
                'withholding_group' => $group,
                'withholding_floor' => $floor,
                'active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        // Mantém os impostos: podem já estar em tabelas de alíquota e perfis de retenção.
    }
};
