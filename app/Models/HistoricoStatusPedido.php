<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HistoricoStatusPedido extends Model
{
    protected $table = 'historico_status_pedido';

    protected $fillable = [
        'pedido_id',
        'status_anterior',
        'status_novo',
        'observacao',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}