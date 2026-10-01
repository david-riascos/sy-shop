<?php

namespace App\Repository;

use App\Entity\Categoria;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/** @extends ServiceEntityRepository<Categoria> */
class CategoriaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Categoria::class);
    }

    public function buscarPorSlug(string $slug): ?Categoria
    {
        return $this->findOneBy(['slug' => $slug]);
    }

    /** @return list<Categoria> */
    public function todasOrdenadas(): array
    {
        return $this->findBy([], ['nombre' => 'ASC']);
    }
}
