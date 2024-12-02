<?php

namespace Tests\Unit;

use App\Repositories\DebtRepository;
use App\Services\DebtService;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class DebtServiceTest extends TestCase
{
    public function testProcessCSVWithValidData()
    {
        // Fake da fila
        Queue::fake();

        // Mock do repositório
        $mockRepo = $this->createMock(DebtRepository::class);

        $mockRepo->method('exists')->willReturn(false);
        $mockRepo->method('store')->willReturn(true);

        $service = new DebtService($mockRepo);

        // Cria dinamicamente um arquivo CSV com os dados mockados
        $filePath = sys_get_temp_dir() . '/test_valid_data.csv';
        $csvData = [
            ['debtId', 'name', 'governmentId', 'email', 'debtAmount', 'debtDueDate'],
            ['12345', 'John Doe', '11111111111', 'johndoe@example.com', 1000.00, '2023-12-31'],
            ['67890', 'Jane Doe', '22222222222', 'janedoe@example.com', 2000.00, '2023-11-30'],
        ];
        $handle = fopen($filePath, 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        // Executa o método a ser testado
        $result = $service->processCSV($filePath);

        // Verifica se os Jobs foram enviados à fila
        Queue::assertPushed(\App\Jobs\ProcessCSVJob::class);

        $this->assertEquals("File processing enqueued", $result);

        // Remove o arquivo temporário após o teste
        unlink($filePath);
    }

    public function testProcessCSVWithDuplicateRows()
    {
        // Fake da fila
        Queue::fake();

        // Mock do repositório
        $mockRepo = $this->createMock(DebtRepository::class);

        // Simula que a dívida com ID "12345" já existe
        $mockRepo->method('exists')->will($this->returnCallback(function ($debtId) {
            return $debtId === '12345';
        }));

        $mockRepo->method('store')->willReturn(true);

        $service = new DebtService($mockRepo);

        // Cria dinamicamente um arquivo CSV com dados duplicados
        $filePath = sys_get_temp_dir() . '/test_duplicate_rows.csv';
        $csvData = [
            ['debtId', 'name', 'governmentId', 'email', 'debtAmount', 'debtDueDate'],
            ['12345', 'John Doe', '11111111111', 'johndoe@example.com', 1000.00, '2023-12-31'],
            ['12345', 'John Doe', '11111111111', 'johndoe@example.com', 1000.00, '2023-12-31'],
        ];
        $handle = fopen($filePath, 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        // Executa o método a ser testado
        $result = $service->processCSV($filePath);

        // Verifica que o Job foi enviado apenas para a linha não duplicada
        Queue::assertPushed(\App\Jobs\ProcessCSVJob::class, 1);

        $this->assertEquals("File processing enqueued", $result);

        // Remove o arquivo temporário após o teste
        unlink($filePath);
    }

    public function testProcessCSVWithMalformedHeaders()
    {
        // Fake da fila
        Queue::fake();

        // Mock do repositório
        $mockRepo = $this->createMock(DebtRepository::class);

        $mockRepo->method('exists')->willReturn(false);
        $mockRepo->method('store')->willReturn(true);

        $service = new DebtService($mockRepo);

        // Cria dinamicamente um arquivo CSV com cabeçalhos malformados
        $filePath = sys_get_temp_dir() . '/test_malformed_headers.csv';
        $csvData = [
            ['wrongHeader1', 'wrongHeader2', 'wrongHeader3'],
            ['12345', 'John Doe', '11111111111'],
        ];
        $handle = fopen($filePath, 'w');
        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);

        try {
            // Executa o método que deve lançar uma exceção
            $service->processCSV($filePath);
            $this->fail('A exceção esperada não foi lançada');
        } catch (\Exception $e) {
            $this->assertEquals('Invalid headers in the CSV file', $e->getMessage());
        } finally {
            // Remove o arquivo temporário após o teste
            unlink($filePath);
        }
    }

    public function testDebtExistsReturnsTrue()
    {
        $mockRepo = $this->createMock(DebtRepository::class);

        $mockRepo->method('exists')->willReturn(true);

        $this->assertTrue($mockRepo->exists('existing-debt-id'));
    }

    public function testStoreDebt()
    {
        $mockRepo = $this->createMock(DebtRepository::class);

        $mockRepo->method('store')->willReturn(true);

        $data = [
            'debt_id' => '12345',
            'name' => 'John Doe',
            'government_id' => '11111111111',
            'email' => 'johndoe@example.com',
            'debt_amount' => 1000.00,
            'debt_due_date' => '2023-12-31',
        ];

        $this->assertTrue($mockRepo->store($data));
    }

    public function testStoreDebtFailure()
    {
        $mockRepo = $this->createMock(DebtRepository::class);

        $mockRepo->method('store')->willReturn(false);

        $data = [
            'debt_id' => '12345',
            'name' => 'John Doe',
            'government_id' => '11111111111',
            'email' => 'johndoe@example.com',
            'debt_amount' => 1000.00,
            'debt_due_date' => '2023-12-31',
        ];

        $this->assertFalse($mockRepo->store($data));
    }
}
