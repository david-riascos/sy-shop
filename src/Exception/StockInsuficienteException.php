<?php

namespace App\Exception;

/** Se intenta comprar mas unidades de las que hay disponibles. */
class StockInsuficienteException extends \DomainException
{
    public static function paraProducto(string $nombre, int $disponible): self
    {
        return new self(sprintf('No hay suficiente stock de "%s" (disponible: %d).', $nombre, $disponible));
    }
}
