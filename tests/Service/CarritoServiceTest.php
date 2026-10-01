<?php

namespace App\Tests\Service;

use App\Entity\Categoria;
use App\Entity\Producto;
use App\Exception\StockInsuficienteException;
use App\Repository\ProductoRepository;
use App\Service\CarritoService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class CarritoServiceTest extends TestCase
{
    private CarritoService $carrito;

    /** @var list<Producto> Lo que "devuelve" el repositorio falso */
    private array $catalogo = [];

    protected function setUp(): void
    {
        $repositorio = $this->createStub(ProductoRepository::class);
        $repositorio->method('findBy')->willReturnCallback(function (array $criterios) {
            $ids = $criterios['id'];

            return array_values(array_filter($this->catalogo, fn (Producto $p) => in_array($p->getId(), $ids, true)));
        });

        $peticion = new Request();
        $peticion->setSession(new Session(new MockArraySessionStorage()));
        $pila = new RequestStack();
        $pila->push($peticion);

        $this->carrito = new CarritoService($pila, $repositorio);
    }

    public function testAgregarSumaLaCantidadDelMismoProducto(): void
    {
        $gorra = $this->producto(1, precio: 40000, stock: 5);

        $this->carrito->agregar($gorra);
        $this->carrito->agregar($gorra, 2);

        $this->assertSame(3, $this->carrito->cantidadTotal());
    }

    public function testNoSePuedeAgregarMasDeLoQueHayEnStock(): void
    {
        $gorra = $this->producto(1, precio: 40000, stock: 2);
        $this->carrito->agregar($gorra, 2);

        $this->expectException(StockInsuficienteException::class);
        $this->carrito->agregar($gorra);
    }

    public function testElTotalUsaElPrecioDeOfertaCuandoExiste(): void
    {
        $camiseta = $this->producto(1, precio: 50000, stock: 10);
        $gorra = $this->producto(2, precio: 42000, stock: 10, oferta: 35000);

        $this->carrito->agregar($camiseta, 2);   // 100.000
        $this->carrito->agregar($gorra);         //  35.000 (oferta, no 42.000)

        $this->assertSame(135000, $this->carrito->total());
    }

    public function testQuitarYVaciar(): void
    {
        $camiseta = $this->producto(1, precio: 50000, stock: 10);
        $gorra = $this->producto(2, precio: 42000, stock: 10);
        $this->carrito->agregar($camiseta);
        $this->carrito->agregar($gorra);

        $this->carrito->quitar($camiseta);
        $this->assertSame(1, $this->carrito->cantidadTotal());

        $this->carrito->vaciar();
        $this->assertSame([], $this->carrito->lineas());
        $this->assertSame(0, $this->carrito->total());
    }

    public function testNoAceptaCantidadesMenoresAUno(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->carrito->agregar($this->producto(1, precio: 1000, stock: 5), 0);
    }

    /** Crea un producto con id asignado (las entidades sin guardar no tienen id). */
    private function producto(int $id, int $precio, int $stock, ?int $oferta = null): Producto
    {
        $producto = new Producto(
            sku: 'SKU-' . $id,
            nombre: 'Producto ' . $id,
            descripcion: 'Descripcion',
            precio: $precio,
            stock: $stock,
            tallas: ['Unica'],
            categoria: new Categoria('Categoria', 'categoria'),
            precioOferta: $oferta,
        );

        $propiedad = new \ReflectionProperty(Producto::class, 'id');
        $propiedad->setValue($producto, $id);
        $this->catalogo[] = $producto;

        return $producto;
    }
}
