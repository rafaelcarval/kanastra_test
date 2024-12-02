<?php

namespace App\Http\Controllers;

use App\Services\DebtService;
use Illuminate\Http\Request;

class DebtController extends Controller
{
    protected DebtService $debtService;

    public function __construct(DebtService $debtService)
    {
        $this->debtService = $debtService;
    }

    public function upload(Request $request)
    {
        $request->validate(['file' => 'required|file|mimes:csv,txt']);

        // Armazena o arquivo CSV
        $filePath = $request->file('file')->storeAs('uploads', uniqid() . '.csv');
        $fullPath = storage_path('app/' . $filePath);

        // Conta o número de linhas no arquivo CSV
        $linesCount = 0;
        if (($handle = fopen($fullPath, 'r')) !== false) {
            while (($line = fgetcsv($handle)) !== false) {
                $linesCount++;
            }
            fclose($handle);
        }

        // Remove a linha do cabeçalho do total
        $linesCount--;

        // Delegar o processamento do CSV ao serviço
        $this->debtService->processCSV($fullPath);

        // Retorna o número de linhas ao cliente
        return response()->json([
            'message' => 'File uploaded and processing started',
            'lines' => $linesCount,
        ]);
    }
}
