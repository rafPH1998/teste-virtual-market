<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Fornecedor extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'fornecedores';

    protected $fillable = [
        'nome',
        'cnpj',
        'email',
        'telefone',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    public function produtos(): BelongsToMany
    {
        return $this->belongsToMany(Produto::class, 'produto_fornecedor')
                    ->withTimestamps();
    }

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

    public function scopeAtivo($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nome', 'like', "%{$term}%")
              ->orWhere('cnpj', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    public function getIsAtivoAttribute(): bool
    {
        return $this->status === 'ativo';
    }
}