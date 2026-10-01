<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/** Copia de lo comprado: si el producto cambia de precio o nombre despues, el pedido no se altera. */
#[ORM\Entity]
#[ORM\Table(name: 'pedido_item')]
class PedidoItem
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'items')]
    #[ORM\JoinColumn(nullable: false)]
    private Pedido $pedido;

    #[ORM\Column(length: 20)]
    private string $sku;

    #[ORM\Column(length: 150)]
    private string $nombreProducto;

    #[ORM\Column]
    private int $precioUnitario;

    #[ORM\Column]
    private int $cantidad;

    public function __construct(Pedido $pedido, string $sku, string $nombreProducto, int $precioUnitario, int $cantidad)
    {
        $this->pedido = $pedido;
        $this->sku = $sku;
        $this->nombreProducto = $nombreProducto;
        $this->precioUnitario = $precioUnitario;
        $this->cantidad = $cantidad;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getNombreProducto(): string
    {
        return $this->nombreProducto;
    }

    public function getPrecioUnitario(): int
    {
        return $this->precioUnitario;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function getSubtotal(): int
    {
        return $this->precioUnitario * $this->cantidad;
    }
}
