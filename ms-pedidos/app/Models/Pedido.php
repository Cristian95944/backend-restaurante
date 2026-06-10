<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pedido extends Model
{
    protected $table = 'pedidos';

    protected $fillable = [
        'mesa_id',
        'fecha',
        'hora',
        'subtotal',
        'total',
        'estado'
    ];

    protected $casts = [
        'subtotal' => 'float',
        'total' => 'float'
    ];

    public function detalles()
    {
        return $this->hasMany(DetallePedido::class, 'pedido_id');
    }
}