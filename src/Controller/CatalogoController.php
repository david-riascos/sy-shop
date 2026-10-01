<?php

namespace App\Controller;

use App\Service\CatalogoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class CatalogoController extends AbstractController
{
    public function __construct(private readonly CatalogoService $catalogo)
    {
    }

    #[Route('/', name: 'catalogo', methods: ['GET'])]
    public function index(Request $request): Response
    {
        $slug = $request->query->getString('categoria');
        $categoria = null;

        if ($slug !== '') {
            $categoria = $this->catalogo->categoriaPorSlug($slug) ?? throw $this->createNotFoundException();
        }

        return $this->render('catalogo/index.html.twig', [
            'productos' => $this->catalogo->listar($categoria),
            'categorias' => $this->catalogo->categorias(),
            'categoriaActual' => $categoria,
        ]);
    }

    #[Route('/producto/{sku}', name: 'producto', methods: ['GET'])]
    public function mostrar(string $sku): Response
    {
        $producto = $this->catalogo->productoPorSku($sku) ?? throw $this->createNotFoundException();

        return $this->render('catalogo/producto.html.twig', ['producto' => $producto]);
    }
}
