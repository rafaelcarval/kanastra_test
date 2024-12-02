<?php

namespace App\Jobs;

use App\Jobs\ProcessDebtRow;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessCSVJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePath;
    protected $headerMapping;

    /**
     * Create a new job instance.
     *
     * @param string $filePath
     * @param array $headerMapping
     */
    public function __construct(string $filePath, array $headerMapping)
    {
        $this->filePath = $filePath;
        $this->headerMapping = $headerMapping;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (($handle = fopen($this->filePath, 'r')) !== false) {
            $header = fgetcsv($handle); // Lê o cabeçalho do CSV

            // Mapeamento do cabeçalho para os campos do banco de dados
            $headerMapping = [
                'debtId' => 'debt_id',
                'name' => 'name',
                'governmentId' => 'government_id',
                'email' => 'email',
                'debtAmount' => 'debt_amount',
                'debtDueDate' => 'debt_due_date',
            ];
            $mappedHeader = array_map(function ($column) use ($headerMapping) {
                return $headerMapping[$column] ?? $column;
            }, $header);

            // Processa cada linha do CSV
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($mappedHeader, $row);
                ProcessDebtRow::dispatch($data); // Enfileira cada linha
            }

            fclose($handle);
        }

        // Log quando o processamento terminar
        Log::info("Finished processing file: {$this->filePath}");
    }
}
