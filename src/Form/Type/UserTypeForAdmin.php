<?php

/**
 * \App\Entity\User type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType.
 */
class UserTypeForAdmin extends AbstractType
{
    /**
     * Constructor.
     *
     * @param FormBuilderInterface $builder The form builder*
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('roles', ChoiceType::class, [
            'label' => 'label.roles',
            'choices' => [
                'label.user' => 'ROLE_USER',
                'label.admin' => 'ROLE_ADMIN',
            ],
            'multiple' => true,
            'expanded' => true,
        ])
            ->add('banned', CheckboxType::class, [
                'label' => 'label.banned',
                'required' => false,
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
