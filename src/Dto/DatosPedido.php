<?php

namespace App\Dto;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Datos que escribe el cliente al finalizar la compra (ficticios: no se envia nada a ningun lado).
 *
 * Las propiedades son nulables a proposito: un campo vacio llega como null desde el formulario y es
 * NotBlank quien lo rechaza con un mensaje. Con un tipo "string" a secas el formulario fallaria con un 500.
 */
class DatosPedido
{
    #[Assert\NotBlank(message: 'Escribe tu nombre.')]
    #[Assert\Length(max: 120)]
    public ?string $nombre = null;

    #[Assert\NotBlank(message: 'Escribe tu correo.')]
    #[Assert\Email(message: 'El correo no es valido.')]
    #[Assert\Length(max: 180)]
    public ?string $correo = null;

    #[Assert\NotBlank(message: 'Escribe tu direccion.')]
    #[Assert\Length(max: 200)]
    public ?string $direccion = null;

    #[Assert\NotBlank(message: 'Escribe tu ciudad.')]
    #[Assert\Length(max: 80)]
    public ?string $ciudad = null;
}
