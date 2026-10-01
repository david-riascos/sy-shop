<?php

namespace App\Controller;

use App\Exception\StockInsuficienteException;
use App\Service\CarritoService;
use App\Service\CatalogoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/carrito')]
class CarritoController extends AbstractController
{
    private const TOKEN_CSRF = 'carrito';

    public function __construct(
        private readonly CarritoService $carrito,
        private readonly CatalogoService $catalogo,
    ) {
    }

    #[Route('', name: 'carrito', methods: ['GET'])]
    public function ver(): Response
    {
        return $this->render('carrito/index.html.twig', [
            'lineas' => $this->carrito->lineas(),
            'total' => $this->carrito->total(),
        ]);
    }

    #[Route('/agregar/{sku}', name: 'carrito_agregar', methods: ['POST'])]
    public function agregar(string $sku, Request $request): RedirectResponse
    {
        $this->validarCsrf($request);
        $producto = $this->catalogo->productoPorSku($sku) ?? throw $this->createNotFoundException();

        try {
            $this->carrito->agregar($producto);
            $this->addFlash('exito', sprintf('"%s" se agrego al carrito.', $producto->getNombre()));
        } catch (StockInsuficienteException $e) {
            $this->addFlash('error', $e->getMessage());
        }

        return $this->redirectToRoute('carrito');
    }

    #[Route('/quitar/{sku}', name: 'carrito_quitar', methods: ['POST'])]
    public function quitar(string $sku, Request $request): RedirectResponse
    {
        $this->validarCsrf($request);
        $producto = $this->catalogo->productoPorSku($sku) ?? throw $this->createNotFoundException();
        $this->carrito->quitar($producto);

        return $this->redirectToRoute('carrito');
    }

    private function validarCsrf(Request $request): void
    {
        if (!$this->isCsrfTokenValid(self::TOKEN_CSRF, $request->request->getString('_token'))) {
            throw $this->createAccessDeniedException('Token CSRF invalido.');
        }
    }
}
