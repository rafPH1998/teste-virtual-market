<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    use HasFactory;

    protected $table = 'itens_pedido';

    protected $fillable = [
        'pedido_id',
        'produto_id',
        'quantidade',
        'valor_unitario',
        'valor_total',
    ];

    protected $casts = [
        'quantidade'    => 'integer',
        'valor_unitario' => 'float',
        'valor_total'   => 'float',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    // Boot: calcula valor_total automaticamente
    protected static function boot()
    {
        parent::boot();

        static::saving(function (self $item) {
            $item->valor_total = $item->quantidade * $item->valor_unitario;
        });

        static::saved(function (self $item) {
            $item->pedido->recalcularTotal();
        });

        static::deleted(function (self $item) {
            $item->pedido->recalcularTotal();
        });
    }
}