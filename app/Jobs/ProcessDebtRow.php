<?php

namespace App\Jobs;

use App\Repositories\DebtRepository;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessDebtRow implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $data;

    /**
     * Create a new job instance.
     *
     * @param array $data Linha de dados do CSV.
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(DebtRepository $debtRepository)
    {
        try {
            if (!$debtRepository->exists($this->data['debt_id'])) {
                $debtRepository->store($this->data);
                Log::info("Processed debt row: " . $this->data['debt_id']);
            } else {
                Log::info("Skipped duplicate debt row: " . $this->data['debt_id']);
            }
        } catch (\Exception $e) {
            Log::error("Error processing debt row: " . $this->data['debt_id'] . " - " . $e->getMessage());
        }
    }
}
