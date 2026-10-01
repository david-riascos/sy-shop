<?php

namespace App\Entity;

use App\Repository\PedidoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PedidoRepository::class)]
#[ORM\Table(name: 'pedido')]
class Pedido
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 120)]
    private string $nombreCliente;

    #[ORM\Column(length: 180)]
    private string $correo;

    #[ORM\Column(length: 200)]
    private string $direccion;

    #[ORM\Column(length: 80)]
    private string $ciudad;

    #[ORM\Column]
    private int $total = 0;

    #[ORM\Column]
    private \DateTimeImmutable $creadoEn;

    /** @var Collection<int, PedidoItem> */
    #[ORM\OneToMany(targetEntity: PedidoItem::class, mappedBy: 'pedido', cascade: ['persist'])]
    private Collection $items;

    public function __construct(string $nombreCliente, string $correo, string $direccion, string $ciudad)
    {
        $this->nombreCliente = $nombreCliente;
        $this->correo = $correo;
        $this->direccion = $direccion;
        $this->ciudad = $ciudad;
        $this->creadoEn = new \DateTimeImmutable();
        $this->items = new ArrayCollection();
    }

    public function agregarItem(PedidoItem $item): void
    {
        $this->items->add($item);
        $this->total += $item->getSubtotal();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNombreCliente(): string
    {
        return $this->nombreCliente;
    }

    public function getCorreo(): string
    {
        return $this->correo;
    }

    public function getDireccion(): string
    {
        return $this->direccion;
    }

    public function getCiudad(): string
    {
        return $this->ciudad;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function getCreadoEn(): \DateTimeImmutable
    {
        return $this->creadoEn;
    }

    /** @return Collection<int, PedidoItem> */
    public function getItems(): Collection
    {
        return $this->items;
    }
}
