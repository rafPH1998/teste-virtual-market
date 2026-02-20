<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Produto extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'descricao',
        'codigo_interno',
        'status',
    ];

    // Relacionamentos
    public function fornecedores(): BelongsToMany
    {
        return $this->belongsToMany(Fornecedor::class, 'produto_fornecedor')
                    ->withTimestamps();
    }

    public function itensPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    // Scopes
    public function scopeAtivo($query)
    {
        return $query->where('status', 'ativo');
    }

    public function scopeSearch($query, $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('nome', 'like', "%{$term}%")
              ->orWhere('codigo_interno', 'like', "%{$term}%")
              ->orWhere('descricao', 'like', "%{$term}%");
        });
    }

    public function getIsAtivoAttribute(): bool
    {
        return $this->status === 'ativo';
    }
}