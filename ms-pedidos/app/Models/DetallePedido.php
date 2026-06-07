<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetallePedido extends Model
{
    protected $table = 'detalles_pedidos';

    protected $fillable = [
        'pedido_id',
        'producto_id',
        'nombre_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected $casts = [
        'cantidad'        => 'integer',
        'precio_unitario' => 'float',
        'subtotal'        => 'float',
        'pedido_id'       => 'integer',
        'producto_id'     => 'integer',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }
}