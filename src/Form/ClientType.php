<?php

namespace App\Form;

use App\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ClientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Name of the contact',
                ],
            ])
            ->add('email',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Email',
                ],
            ])
            ->add('company',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Company name',
                ],
            ])
            ->add('adress',TextareaType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Company adress',
                ],
            ])
            
            ->add('taxNumber',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Tax Identification Number (tax_number, VAT, ICE, NIF…)',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }
}
