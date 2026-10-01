<?php

namespace App\Service;

use App\Entity\Producto;

/** Un producto del carrito con la cantidad elegida. */
final class LineaCarrito
{
    public function __construct(
        public readonly Producto $producto,
        public readonly int $cantidad,
    ) {
    }

    public function subtotal(): int
    {
        return $this->producto->getPrecioActual() * $this->cantidad;
    }
}
