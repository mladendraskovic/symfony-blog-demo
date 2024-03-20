<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class TagType extends AbstractType
{
    public function __construct()
    {
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $constraints = [
            new NotBlank(),
            new Length(['max' => 255]),
        ];

        $builder
            ->add("name_en", TextType::class, [
                'label' => 'Name EN',
                'required' => true,
                'constraints' => $constraints,
            ])
            ->add("name_hr", TextType::class, [
                'label' => 'Name HR',
                'required' => true,
                'constraints' => $constraints,
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => null,
        ]);
    }
}
