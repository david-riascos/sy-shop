<?php

namespace App\Tests\Controller;

use App\Entity\Categoria;
use App\Entity\Pedido;
use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/** Recorre la tienda como lo haria un cliente: catalogo, carrito, formulario y pedido. */
class TiendaTest extends WebTestCase
{
    private const RUTA_PEDIDO = '/pedido';
    private const BOTON_CONFIRMAR = 'Confirmar pedido (pago contra entrega simulado)';

    private KernelBrowser $cliente;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->cliente = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        // Base de datos de pruebas limpia en cada test.
        $metadatos = $this->em->getMetadataFactory()->getAllMetadata();
        $herramienta = new SchemaTool($this->em);
        $herramienta->dropSchema($metadatos);
        $herramienta->createSchema($metadatos);

        $gorras = new Categoria('Gorras', 'gorras');
        $this->em->persist($gorras);
        $this->em->persist(new Producto('GOR-001', 'Gorra Snapback Negra', 'Gorra plana', 39000, 5, ['Unica'], $gorras));
        $this->em->persist(new Producto('GOR-002', 'Gorra Trucker Roja', 'Gorra trucker', 42000, 3, ['Unica'], $gorras, 35000));
        $this->em->persist(new Producto('GOR-003', 'Gorra Agotada', 'Sin stock', 45000, 0, ['Unica'], $gorras));
        $this->em->flush();
    }

    public function testElCatalogoMuestraProductosOfertasYAgotados(): void
    {
        $this->cliente->request('GET', '/');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorCount(3, 'li.producto');
        $this->assertSelectorTextContains('body', '$35.000');   // precio de oferta
        $this->assertSelectorTextContains('.agotado', 'Agotado');
    }

    public function testUnaCategoriaInexistenteDevuelve404(): void
    {
        $this->cliente->request('GET', '/?categoria=no-existe');

        $this->assertResponseStatusCodeSame(404);
    }

    public function testFlujoCompletoDeCompraDescuentaElStock(): void
    {
        $this->agregarAlCarrito('GOR-002');
        $this->assertSelectorTextContains('.total', '$35.000');

        $this->cliente->request('GET', self::RUTA_PEDIDO);
        $this->cliente->submitForm(self::BOTON_CONFIRMAR, [
            'pedido[nombre]' => 'Cliente de Prueba',
            'pedido[correo]' => 'cliente@ejemplo.test',
            'pedido[direccion]' => 'Calle Falsa 123',
            'pedido[ciudad]' => 'Bogota',
        ]);

        $this->assertResponseRedirects('/pedido/confirmacion');
        $this->cliente->followRedirect();
        $this->assertSelectorTextContains('h1', 'confirmado');

        $this->em->clear();
        $this->assertSame(2, $this->em->getRepository(Producto::class)->findOneBy(['sku' => 'GOR-002'])->getStock());
        $this->assertCount(1, $this->em->getRepository(Pedido::class)->findAll());
    }

    /** Regresion: un formulario vacio antes provocaba un error 500 en vez de mostrar mensajes. */
    public function testElFormularioVacioMuestraErroresYNoUnError500(): void
    {
        $this->agregarAlCarrito('GOR-001');

        $this->cliente->request('GET', self::RUTA_PEDIDO);
        $this->cliente->submitForm(self::BOTON_CONFIRMAR, [
            'pedido[nombre]' => '',
            'pedido[correo]' => '',
            'pedido[direccion]' => '',
            'pedido[ciudad]' => '',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'Escribe tu nombre.');
        $this->assertSelectorTextContains('body', 'Escribe tu correo.');
        $this->assertCount(0, $this->em->getRepository(Pedido::class)->findAll());
    }

    public function testUnCorreoInvalidoSeRechaza(): void
    {
        $this->agregarAlCarrito('GOR-001');

        $this->cliente->request('GET', self::RUTA_PEDIDO);
        $this->cliente->submitForm(self::BOTON_CONFIRMAR, [
            'pedido[nombre]' => 'Cliente',
            'pedido[correo]' => 'esto-no-es-un-correo',
            'pedido[direccion]' => 'Calle 1',
            'pedido[ciudad]' => 'Bogota',
        ]);

        $this->assertResponseStatusCodeSame(422);
        $this->assertSelectorTextContains('body', 'El correo no es valido.');
    }

    public function testNoSePuedeFinalizarConElCarritoVacio(): void
    {
        $this->cliente->request('GET', self::RUTA_PEDIDO);

        $this->assertResponseRedirects('/carrito');
    }

    public function testUnPostSinTokenCsrfSeRechaza(): void
    {
        $this->cliente->request('POST', '/carrito/agregar/GOR-001');

        $this->assertGreaterThanOrEqual(400, $this->cliente->getResponse()->getStatusCode());
        $this->assertLessThan(500, $this->cliente->getResponse()->getStatusCode());
        $this->cliente->request('GET', '/carrito');
        $this->assertSelectorTextContains('body', 'Tu carrito esta vacio');
    }

    public function testLaConfirmacionSoloSeVeConElPedidoRecienHecho(): void
    {
        $this->cliente->request('GET', '/pedido/confirmacion');

        $this->assertResponseRedirects('/');
    }

    private function agregarAlCarrito(string $sku): void
    {
        $this->cliente->request('GET', '/producto/' . $sku);
        $this->cliente->submitForm('Añadir al carrito');
        $this->cliente->followRedirect();
    }
}
