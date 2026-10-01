<?php

namespace App\Service;

use App\Entity\Producto;
use App\Exception\StockInsuficienteException;
use App\Repository\ProductoRepository;
use Symfony\Component\HttpFoundation\RequestStack;

/** Carrito guardado en la sesion: solo se recuerdan ids y cantidades, los precios se leen siempre de la base. */
class CarritoService
{
    private const CLAVE_SESION = 'carrito';

    public function __construct(
        private readonly RequestStack $requestStack,
        private readonly ProductoRepository $productos,
    ) {
    }

    public function agregar(Producto $producto, int $cantidad = 1): void
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad debe ser al menos 1.');
        }

        $cantidades = $this->cantidades();
        $nueva = ($cantidades[$producto->getId()] ?? 0) + $cantidad;

        if ($nueva > $producto->getStock()) {
            throw StockInsuficienteException::paraProducto($producto->getNombre(), $producto->getStock());
        }

        $cantidades[$producto->getId()] = $nueva;
        $this->guardar($cantidades);
    }

    public function quitar(Producto $producto): void
    {
        $cantidades = $this->cantidades();
        unset($cantidades[$producto->getId()]);
        $this->guardar($cantidades);
    }

    public function vaciar(): void
    {
        $this->guardar([]);
    }

    /** @return list<LineaCarrito> */
    public function lineas(): array
    {
        $cantidades = $this->cantidades();
        if ($cantidades === []) {
            return [];
        }

        $lineas = [];
        foreach ($this->productos->findBy(['id' => array_keys($cantidades)]) as $producto) {
            $lineas[] = new LineaCarrito($producto, $cantidades[$producto->getId()]);
        }

        return $lineas;
    }

    public function total(): int
    {
        return array_sum(array_map(fn (LineaCarrito $linea) => $linea->subtotal(), $this->lineas()));
    }

    public function cantidadTotal(): int
    {
        return array_sum($this->cantidades());
    }

    /** @return array<int, int> id de producto => cantidad */
    private function cantidades(): array
    {
        return $this->requestStack->getSession()->get(self::CLAVE_SESION, []);
    }

    /** @param array<int, int> $cantidades */
    private function guardar(array $cantidades): void
    {
        $this->requestStack->getSession()->set(self::CLAVE_SESION, $cantidades);
    }
}
