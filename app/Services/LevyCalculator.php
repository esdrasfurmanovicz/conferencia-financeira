<?php

namespace App\Services;

use App\Models\RateCard;
use App\Models\WithholdingProfile;

/**
 * Turns a rate card (which levies apply to a billing line, and at what percentage) and a
 * withholding profile (which of those levies this particular client has withheld) into a
 * breakdown for a given amount.
 *
 * Levies that share a `withholding_group` are evaluated together: their candidate withheld
 * amounts are summed first, and only if that group total reaches the group's floor does any of
 * them actually get withheld. A levy with no group is its own group of one. This models the
 * common real-world rule where a handful of small contribution levies are only withheld once,
 * combined, they pass a minimum amount.
 */
class LevyCalculator
{
    public function estimate(?RateCard $rateCard, ?WithholdingProfile $profile, float $baseAmount): LevyEstimateResult
    {
        if (!$rateCard || !$rateCard->active) {
            return LevyEstimateResult::failure('Selecione uma tabela de alíquotas ativa.');
        }

        if (!$profile || !$profile->active) {
            return LevyEstimateResult::failure('Selecione um perfil de retenção ativo.');
        }

        $items = $rateCard->activeItems()->with('levy')->get();
        if ($items->isEmpty()) {
            return LevyEstimateResult::failure('Essa tabela de alíquotas não tem nenhum imposto configurado.');
        }

        $rules = $profile->rules()->get()->keyBy('levy_id');

        $candidates = [];
        foreach ($items as $item) {
            $levy = $item->levy;
            if (!$levy || !$levy->active) {
                continue;
            }

            $rule = $rules->get($levy->id);
            if (!$rule || !$rule->applies) {
                continue;
            }

            $amount = round($baseAmount * $item->percentage / 100, 2);

            $candidates[] = [
                'levy_id' => $levy->id,
                'code' => $levy->code,
                'name' => $levy->name,
                'percentage' => $item->percentage,
                'amount' => $amount,
                'withheld' => $rule->withheld,
                'group' => $levy->withholding_group ?: ('__solo_' . $levy->id),
                'floor' => $levy->withholding_floor,
            ];
        }

        if (empty($candidates)) {
            return LevyEstimateResult::failure('Nenhum imposto se aplica a essa combinação de tabela e perfil.');
        }

        $groupTotals = [];
        foreach ($candidates as $candidate) {
            if (!$candidate['withheld']) {
                continue;
            }
            $groupTotals[$candidate['group']] = ($groupTotals[$candidate['group']] ?? 0) + $candidate['amount'];
        }

        $leviesTotal = 0;
        $withheldTotal = 0;
        $breakdown = [];

        foreach ($candidates as $candidate) {
            $leviesTotal += $candidate['amount'];

            $withheld = $candidate['withheld'];
            if ($withheld && $candidate['floor'] !== null) {
                $groupTotal = $groupTotals[$candidate['group']] ?? 0;
                if ($groupTotal < $candidate['floor']) {
                    $withheld = false;
                }
            }

            if ($withheld) {
                $withheldTotal += $candidate['amount'];
            }

            $breakdown[] = [
                'levy_id' => $candidate['levy_id'],
                'code' => $candidate['code'],
                'name' => $candidate['name'],
                'percentage' => $candidate['percentage'],
                'amount' => $candidate['amount'],
                'withheld' => $withheld,
            ];
        }

        return new LevyEstimateResult(
            ok: true,
            error: null,
            breakdown: $breakdown,
            leviesTotal: round($leviesTotal, 2),
            withheldTotal: round($withheldTotal, 2),
            netAmount: round($baseAmount - $withheldTotal, 2),
        );
    }
}
