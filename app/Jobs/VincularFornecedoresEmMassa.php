<?php

namespace App\Jobs;

use App\Models\Produto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class DesvincularFornecedoresEmMassa implements ShouldQueue
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

        $produto->fornecedores()->detach($this->fornecedorIds);

        Log::info("DesvincularFornecedoresEmMassa: produto {$this->produtoId} desvinculado de " . count($this->fornecedorIds) . " fornecedores.");
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("DesvincularFornecedoresEmMassa FAILED produto {$this->produtoId}: {$exception->getMessage()}");
    }
}