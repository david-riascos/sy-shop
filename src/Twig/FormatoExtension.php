<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class FormatoExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [new TwigFilter('cop', $this->cop(...))];
    }

    /** 129000 => "$129.000" (pesos colombianos, sin decimales). */
    public function cop(int $valor): string
    {
        return '$' . number_format($valor, 0, ',', '.');
    }
}
