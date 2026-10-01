<?php

namespace App\Service;

use App\Dto\DatosPedido;
use App\Entity\Pedido;
use App\Entity\PedidoItem;
use App\Exception\StockInsuficienteException;
use Doctrine\ORM\EntityManagerInterface;

class PedidoService
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
    }

    /**
     * Crea el pedido y descuenta el stock en una sola transaccion.
     *
     * @param list<LineaCarrito> $lineas
     *
     * @throws StockInsuficienteException si alguna linea supera el stock disponible
     */
    public function crear(DatosPedido $datos, array $lineas): Pedido
    {
        if ($lineas === []) {
            throw new \DomainException('El carrito esta vacio.');
        }

        // Las lineas se validan ANTES de abrir la transaccion: si una transaccion falla, Doctrine cierra el EntityManager.
        foreach ($lineas as $linea) {
            if ($linea->cantidad > $linea->producto->getStock()) {
                throw StockInsuficienteException::paraProducto($linea->producto->getNombre(), $linea->producto->getStock());
            }
        }

        return $this->entityManager->wrapInTransaction(function () use ($datos, $lineas): Pedido {
            // Los datos ya pasaron la validacion (NotBlank), por eso aqui nunca son null.
            $pedido = new Pedido((string) $datos->nombre, (string) $datos->correo, (string) $datos->direccion, (string) $datos->ciudad);

            foreach ($lineas as $linea) {
                $producto = $linea->producto;
                $producto->descontarStock($linea->cantidad);
                $pedido->agregarItem(new PedidoItem(
                    $pedido,
                    $producto->getSku(),
                    $producto->getNombre(),
                    $producto->getPrecioActual(),
                    $linea->cantidad,
                ));
            }

            $this->entityManager->persist($pedido);

            return $pedido;
        });
    }
}
