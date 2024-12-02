<?php

namespace Tests\Unit;

use App\Repositories\DebtRepository;
use App\Services\DebtService;
use PHPUnit\Framework\TestCase;

class DebtServiceTest extends TestCase
{
    public function testProcessCSV()
    {
        // Cria o mock para o DebtRepository
        $mockRepo = $this->createMock(DebtRepository::class);

        // Define o comportamento dos métodos mockados
        $mockRepo->method('exists')->willReturn(false);
        $mockRepo->method('store')->willReturn(true);

        // Passa o mock para o serviço
        $service = new DebtService($mockRepo);

        // Executa o método a ser testado
        $result = $service->processCSV(base_path('tests/stubs/input.csv'));

        // Verifica os resultados
        $this->assertCount(1, $result);
        $this->assertStringContainsString('Processed', $result[0]);
    }
}
