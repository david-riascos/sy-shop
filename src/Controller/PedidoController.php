<?php

namespace App\Controller;

use App\Dto\DatosPedido;
use App\Exception\StockInsuficienteException;
use App\Form\PedidoType;
use App\Repository\PedidoRepository;
use App\Service\CarritoService;
use App\Service\PedidoService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/pedido')]
class PedidoController extends AbstractController
{
    private const SESION_ULTIMO_PEDIDO = 'ultimo_pedido';

    public function __construct(
        private readonly CarritoService $carrito,
        private readonly PedidoService $pedidos,
    ) {
    }

    #[Route('', name: 'pedido', methods: ['GET', 'POST'])]
    public function finalizar(Request $request): Response
    {
        $lineas = $this->carrito->lineas();
        if ($lineas === []) {
            $this->addFlash('error', 'Tu carrito esta vacio.');

            return $this->redirectToRoute('carrito');
        }

        $formulario = $this->createForm(PedidoType::class, new DatosPedido());
        $formulario->handleRequest($request);

        if ($formulario->isSubmitted() && $formulario->isValid()) {
            try {
                $pedido = $this->pedidos->crear($formulario->getData(), $lineas);
            } catch (StockInsuficienteException $e) {
                $this->addFlash('error', $e->getMessage());

                return $this->redirectToRoute('carrito');
            }

            $this->carrito->vaciar();
            // Solo se puede ver la confirmacion del pedido recien hecho: los ids no se pueden adivinar en la URL.
            $request->getSession()->set(self::SESION_ULTIMO_PEDIDO, $pedido->getId());

            return $this->redirectToRoute('pedido_confirmacion');
        }

        return $this->render('pedido/finalizar.html.twig', [
            'formulario' => $formulario,
            'lineas' => $lineas,
            'total' => $this->carrito->total(),
        ]);
    }

    #[Route('/confirmacion', name: 'pedido_confirmacion', methods: ['GET'])]
    public function confirmacion(Request $request, PedidoRepository $repositorio): Response
    {
        $id = $request->getSession()->get(self::SESION_ULTIMO_PEDIDO);
        $pedido = $id !== null ? $repositorio->find($id) : null;

        if ($pedido === null) {
            return $this->redirectToRoute('catalogo');
        }

        return $this->render('pedido/confirmacion.html.twig', ['pedido' => $pedido]);
    }
}
