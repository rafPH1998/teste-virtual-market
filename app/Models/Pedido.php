<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedidos';

    protected $fillable = [
        'fornecedor_id',
        'data_pedido',
        'status',
        'observacoes',
        'valor_total',
    ];

    protected $casts = [
        'data_pedido' => 'date',
        'valor_total' => 'float',
    ];

    const STATUS_ABERTO      = 'aberto';
    const STATUS_PROCESSANDO = 'processando';
    const STATUS_CONCLUIDO   = 'concluido';
    const STATUS_CANCELADO   = 'cancelado';

    const STATUS_LIST = [
        self::STATUS_ABERTO,
        self::STATUS_PROCESSANDO,
        self::STATUS_CONCLUIDO,
        self::STATUS_CANCELADO,
    ];

    // Pedidos concluídos/cancelados não podem ser editados
    public function isBloqueado(): bool
    {
        return in_array($this->status, [self::STATUS_CONCLUIDO, self::STATUS_CANCELADO]);
    }

    public function fornecedor(): BelongsTo
    {
        return $this->belongsTo(Fornecedor::class);
    }

    public function itens(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function historicoStatus(): HasMany
    {
        return $this->hasMany(HistoricoStatusPedido::class)->orderByDesc('created_at');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where('id', 'like', "%{$term}%");
    }

    public function scopePorStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePorFornecedor($query, $fornecedorId)
    {
        return $query->where('fornecedor_id', $fornecedorId);
    }

    // Recalcula valor_total com base nos itens
    public function recalcularTotal(): void
    {
        $total = $this->itens()->sum('valor_total');
        $this->update(['valor_total' => $total]);
    }
}