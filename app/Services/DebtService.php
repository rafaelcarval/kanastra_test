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

    public function processCSV(string|array $filePath): string
    {
        $headerMapping = [
            'debtId' => 'debt_id',
            'name' => 'name',
            'governmentId' => 'government_id',
            'email' => 'email',
            'debtAmount' => 'debt_amount',
            'debtDueDate' => 'debt_due_date',
        ];

         // Valida se o arquivo CSV é acessível
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("File not found or not readable");
        }

        // Valida os cabeçalhos
        if (($handle = fopen($filePath, 'r')) !== false) {
            $headers = fgetcsv($handle); // Lê os cabeçalhos
            fclose($handle);

            foreach ($headerMapping as $requiredHeader => $mappedKey) {
                if (!in_array($requiredHeader, $headers)) {
                    throw new \Exception('Invalid headers in the CSV file');
                }
            }
        } else {
            throw new \Exception("Unable to open the file");
        }

        // Adiciona o processamento do CSV à fila
        \App\Jobs\ProcessCSVJob::dispatch($filePath, $headerMapping);

        return "File processing enqueued";
    }


}
