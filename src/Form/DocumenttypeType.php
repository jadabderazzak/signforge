<?php

namespace App\Form;

use App\Entity\TypeDocument;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class DocumenttypeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter the name of the document type',
                ],
            ])
            ->add('label',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Enter the display label for the document type',
                ],
            ])
            
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => TypeDocument::class,
        ]);
    }
}
