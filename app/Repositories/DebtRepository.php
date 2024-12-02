<?php

namespace App\Repositories;

use App\Models\Debt;

class DebtRepository
{
    public function store(array $data)
    {
        return Debt::create($data);
    }

    public function exists(string $debtId): bool
    {
        return Debt::where('debt_id', $debtId)->exists();
    }
}
