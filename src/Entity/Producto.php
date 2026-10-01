<?php

namespace App\Entity;

use App\Exception\StockInsuficienteException;
use App\Repository\ProductoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
#[ORM\Table(name: 'producto')]
class Producto
{
    #[ORM\Id, ORM\GeneratedValue, ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 20, unique: true)]
    private string $sku;

    #[ORM\Column(length: 150)]
    private string $nombre;

    #[ORM\Column(type: Types::TEXT)]
    private string $descripcion;

    /** Precio en pesos colombianos (COP), sin decimales. */
    #[ORM\Column]
    private int $precio;

    #[ORM\Column(nullable: true)]
    private ?int $precioOferta = null;

    #[ORM\Column]
    private int $stock;

    /** @var list<string> */
    #[ORM\Column(type: Types::JSON)]
    private array $tallas;

    #[ORM\ManyToOne(inversedBy: 'productos')]
    #[ORM\JoinColumn(nullable: false)]
    private Categoria $categoria;

    /** @param list<string> $tallas */
    public function __construct(
        string $sku,
        string $nombre,
        string $descripcion,
        int $precio,
        int $stock,
        array $tallas,
        Categoria $categoria,
        ?int $precioOferta = null,
    ) {
        $this->sku = $sku;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
        $this->precio = $precio;
        $this->stock = $stock;
        $this->tallas = $tallas;
        $this->categoria = $categoria;
        $this->precioOferta = $precioOferta;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getSku(): string
    {
        return $this->sku;
    }

    public function getNombre(): string
    {
        return $this->nombre;
    }

    public function getDescripcion(): string
    {
        return $this->descripcion;
    }

    public function getPrecio(): int
    {
        return $this->precio;
    }

    public function getPrecioOferta(): ?int
    {
        return $this->precioOferta;
    }

    public function getStock(): int
    {
        return $this->stock;
    }

    /** @return list<string> */
    public function getTallas(): array
    {
        return $this->tallas;
    }

    public function getCategoria(): Categoria
    {
        return $this->categoria;
    }

    public function estaEnOferta(): bool
    {
        return $this->precioOferta !== null && $this->precioOferta < $this->precio;
    }

    /** Precio que realmente se cobra hoy. */
    public function getPrecioActual(): int
    {
        return $this->estaEnOferta() ? $this->precioOferta : $this->precio;
    }

    public function estaDisponible(): bool
    {
        return $this->stock > 0;
    }

    public function descontarStock(int $cantidad): void
    {
        if ($cantidad > $this->stock) {
            throw StockInsuficienteException::paraProducto($this->nombre, $this->stock);
        }
        $this->stock -= $cantidad;
    }
}
