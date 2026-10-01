<?php

namespace App\Form;

use App\Dto\DatosPedido;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PedidoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('nombre', TextType::class, ['label' => 'Nombre completo'])
            ->add('correo', EmailType::class, ['label' => 'Correo electronico'])
            ->add('direccion', TextType::class, ['label' => 'Direccion'])
            ->add('ciudad', TextType::class, ['label' => 'Ciudad'])
            ->add('confirmar', SubmitType::class, ['label' => 'Confirmar pedido (pago contra entrega simulado)']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => DatosPedido::class]);
    }
}
