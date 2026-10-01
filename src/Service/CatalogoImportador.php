<?php

namespace App\Service;

use App\Entity\Categoria;
use App\Entity\Producto;
use App\Repository\CategoriaRepository;
use App\Repository\ProductoRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;

/** Carga el catalogo ficticio desde un JSON. Es idempotente: un SKU ya cargado se omite. */
class CatalogoImportador
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly CategoriaRepository $categorias,
        private readonly ProductoRepository $productos,
    ) {
    }

    /** @return array{creados: int, existentes: int} */
    public function importar(string $ruta): array
    {
        if (!is_file($ruta)) {
            throw new \RuntimeException(sprintf('No existe el archivo de catalogo: %s', $ruta));
        }

        $datos = json_decode((string) file_get_contents($ruta), true, flags: JSON_THROW_ON_ERROR);
        $slugger = new AsciiSlugger();
        $creados = 0;
        $existentes = 0;

        foreach ($datos['productos'] as $item) {
            if ($this->productos->buscarPorSku($item['sku']) !== null) {
                ++$existentes;
                continue;
            }

            $slug = strtolower($slugger->slug($item['categoria'])->toString());
            $categoria = $this->categorias->buscarPorSlug($slug);
            if ($categoria === null) {
                $categoria = new Categoria($item['categoria'], $slug);
                $this->entityManager->persist($categoria);
                $this->entityManager->flush();
            }

            $this->entityManager->persist(new Producto(
                $item['sku'],
                $item['nombre'],
                $item['descripcion'],
                $item['precio'],
                $item['stock'],
                $item['tallas'],
                $categoria,
                $item['precio_oferta'] ?? null,
            ));
            ++$creados;
        }

        $this->entityManager->flush();

        return ['creados' => $creados, 'existentes' => $existentes];
    }
}
