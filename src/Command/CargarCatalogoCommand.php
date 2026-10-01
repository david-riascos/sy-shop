<?php

namespace App\Command;

use App\Service\CatalogoImportador;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(name: 'app:cargar-catalogo', description: 'Carga el catalogo ficticio de datos/productos.json')]
class CargarCatalogoCommand extends Command
{
    public function __construct(
        private readonly CatalogoImportador $importador,
        #[Autowire('%kernel.project_dir%/datos/productos.json')]
        private readonly string $rutaCatalogo,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $resultado = $this->importador->importar($this->rutaCatalogo);
        $io->success(sprintf('Productos creados: %d, ya existentes: %d', $resultado['creados'], $resultado['existentes']));

        return Command::SUCCESS;
    }
}
