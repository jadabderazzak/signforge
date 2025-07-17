<?php

namespace App\Form;

use App\Entity\User;
use App\Entity\Company;
use Symfony\Component\Form\AbstractType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class CompanyType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Company name',
                ],
            ])
            ->add('adress', TextareaType::class, [
                'label' => "Adress",
                
            ])
            ->add('footer', CKEditorType::class, [
                'label' => "Footer",
                'config' => [
                    'toolbar' => [
                        ['Bold', 'Italic', 'Underline'],     
                        ['BulletedList'],                     
                    ],
                     
                      
                ]
            ])
            ->add('phone',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Company phone number',
                ],
            ])
            ->add('currency',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Currency symbol (e.g. $, €, £)',
                ],
            ])
            ->add('TaxIdentification',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Tax Identification Number (tax_number, VAT, ICE, NIF…)',
                ],
            ])
            ->add('RegistrationNumber',TextType::class, [
                'required' => false,
                'attr' => [
                    'placeholder' => 'Company Registration Number (RC)',
                ],
            ])
            
            ->add('BankDetails', CKEditorType::class, [
                'label' => "Bank details",
                'config' => [
                    'toolbar' => [
                        ['Bold', 'Italic', 'Underline'],     
                        ['BulletedList'],                     
                    ],
                     
                      
                ]
            ])
           
           
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Company::class,
        ]);
    }
}
