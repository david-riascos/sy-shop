<?php

namespace App\Repository;

use App\Entity\Categoria;
use App\Entity\Producto;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Producto> */
class ProductoRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Producto::class);
    }

    /** @return list<Producto> */
    public function listar(?Categoria $categoria = null): array
    {
        $criterios = $categoria !== null ? ['categoria' => $categoria] : [];

        return $this->findBy($criterios, ['nombre' => 'ASC']);
    }

    public function buscarPorSku(string $sku): ?Producto
    {
        return $this->findOneBy(['sku' => $sku]);
    }
}
