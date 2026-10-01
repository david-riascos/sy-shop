<?php

namespace App\Service;

use App\Entity\Categoria;
use App\Entity\Producto;
use App\Repository\CategoriaRepository;
use App\Repository\ProductoRepository;

/** Consultas del catalogo: los controladores no hablan con los repositorios directamente. */
class CatalogoService
{
    public function __construct(
        private readonly ProductoRepository $productos,
        private readonly CategoriaRepository $categorias,
    ) {
    }

    /** @return list<Producto> */
    public function listar(?Categoria $categoria = null): array
    {
        return $this->productos->listar($categoria);
    }

    /** @return list<Categoria> */
    public function categorias(): array
    {
        return $this->categorias->todasOrdenadas();
    }

    public function categoriaPorSlug(string $slug): ?Categoria
    {
        return $this->categorias->buscarPorSlug($slug);
    }

    public function productoPorSku(string $sku): ?Producto
    {
        return $this->productos->buscarPorSku($sku);
    }
}
