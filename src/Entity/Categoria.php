<?php

namespace App\Entity;

use App\Repository\CategoriaRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CategoriaRepository::class)]
#[ORM\Table(name: 'categoria')]
class Categoria
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 80, unique: true)]
    private string $nombre;

    #[ORM\Column(length: 80, unique: true)]
    private string $slug;

    /** @var Collection<int, Producto> */
    #[ORM\OneToMany(targetEntity: Producto::class, mappedBy: 'categoria')]
    private Collection $productos;

    public function __construct(string $nombre, string $slug)
    {
        $this->nombre = $nombre;
        $this->slug = $slug;
        $this->productos = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    /** @return Collection<int, Producto> */
    public function getProductos(): Collection
    {
        return $this->productos;
    }
}
