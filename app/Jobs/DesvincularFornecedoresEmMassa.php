<?php

namespace App\Jobs;

use App\Models\Produto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class VincularFornecedoresEmMassa implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;

    public function __construct(
        public readonly int $produtoId,
        public readonly array $fornecedorIds
    ) {}

    public function handle(): void
    {
        $produto = Produto::findOrFail($this->produtoId);

        // syncWithoutDetaching mantém vínculos existentes e adiciona novos
        $produto->fornecedores()->syncWithoutDetaching($this->fornecedorIds);

        Log::info("VincularFornecedoresEmMassa: produto {$this->produtoId} vinculado a " . count($this->fornecedorIds) . " fornecedores.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("VincularFornecedoresEmMassa FAILED produto {$this->produtoId}: {$exception->getMessage()}");
    }
}