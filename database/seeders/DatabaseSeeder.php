<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Levy;
use App\Models\RateCard;
use App\Models\User;
use App\Models\WithholdingProfile;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Demo data for the portfolio build: a demo login, a small catalog of common Brazilian
     * service-invoice levies, one rate card, one withholding profile and a couple of clients
     * already wired together so the reconciliation screen has something to show immediately.
     */
    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => 'demo@conferencia.app'],
            ['name' => 'Usuária Demo', 'password' => bcrypt('demo1234')]
        );

        if (! $user->isAdmin()) {
            $user->forceFill(['role' => User::ROLE_ADMIN])->save();
        }

        $iss = Levy::firstOrCreate(['code' => 'ISS'], ['name' => 'ISS', 'active' => true]);
        $pis = Levy::firstOrCreate(['code' => 'PIS'], ['name' => 'PIS', 'withholding_group' => 'federais', 'withholding_floor' => 10, 'active' => true]);
        $cofins = Levy::firstOrCreate(['code' => 'COFINS'], ['name' => 'COFINS', 'withholding_group' => 'federais', 'withholding_floor' => 10, 'active' => true]);
        $csll = Levy::firstOrCreate(['code' => 'CSLL'], ['name' => 'CSLL', 'withholding_group' => 'federais', 'withholding_floor' => 10, 'active' => true]);
        $irrf = Levy::firstOrCreate(['code' => 'IRRF'], ['name' => 'IRRF', 'withholding_group' => 'irrf', 'withholding_floor' => 10, 'active' => true]);

        $rateCard = RateCard::firstOrCreate(['name' => 'Consultoria (padrão)'], ['description' => 'Alíquotas padrão para serviços de consultoria.', 'active' => true]);
        foreach ([
            [$iss, 5.0], [$pis, 0.65], [$cofins, 3.0], [$csll, 1.0], [$irrf, 1.5],
        ] as [$levy, $rate]) {
            $rateCard->items()->firstOrCreate(['levy_id' => $levy->id], ['percentage' => $rate]);
        }

        $profile = WithholdingProfile::firstOrCreate(['name' => 'Pessoa jurídica padrão'], ['active' => true]);
        foreach ([$iss, $pis, $cofins, $csll, $irrf] as $levy) {
            $profile->rules()->firstOrCreate(['levy_id' => $levy->id], ['applies' => true, 'withheld' => $levy->code !== 'ISS']);
        }

        foreach (['Ateliê Nova Fase Ltda', 'Construtora Horizonte S.A.', 'Studio Marim Design'] as $name) {
            Client::firstOrCreate(['name' => $name], ['withholding_profile_id' => $profile->id, 'active' => true]);
        }
    }
}
