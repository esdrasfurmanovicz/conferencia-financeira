<?php

namespace App\Services;

class LevyEstimateResult
{
    /** @param array<int, array{levy_id:int, code:string, name:string, percentage:float, amount:float, withheld:bool}> $breakdown */
    public function __construct(
        public readonly bool $ok,
        public readonly ?string $error,
        public readonly array $breakdown,
        public readonly float $leviesTotal,
        public readonly float $withheldTotal,
        public readonly float $netAmount,
    ) {
    }

    public static function failure(string $message): self
    {
        return new self(false, $message, [], 0, 0, 0);
    }
}
