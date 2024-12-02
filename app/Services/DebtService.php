<?php

namespace App\Services;

use App\Repositories\DebtRepository;
use Illuminate\Support\Facades\Log;


class DebtService
{
    protected DebtRepository $debtRepository;

    public function __construct(DebtRepository $debtRepository)
    {
        $this->debtRepository = $debtRepository;
    }

    public function processCSV(string $filePath): string
    {
        $headerMapping = [
            'debtId' => 'debt_id',
            'name' => 'name',
            'governmentId' => 'government_id',
            'email' => 'email',
            'debtAmount' => 'debt_amount',
            'debtDueDate' => 'debt_due_date',
        ];

        // Adiciona o processamento do CSV à fila
        \App\Jobs\ProcessCSVJob::dispatch($filePath, $headerMapping);

        return "File processing enqueued";
    }


}
