<?php

/**
 * Change Password type.
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class changePasswordType.
 */
class ChangePasswordType extends AbstractType
{
    /**
     * Constructor.
     *
     * @param FormBuilderInterface $builder FormBuilderInterface
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('newPassword', PasswordType::class, [
                'label' => 'label.newPassword',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'message.enter_password']),
                ],
            ])
            ->add('confirmPassword', PasswordType::class, [
                'label' => 'label.confirmPassword',
                'mapped' => false,
                'constraints' => [
                    new NotBlank(['message' => 'message.confirm_password']),
                ],
            ]);
    }

    /**
     * Configures the options for this type.
     *
     * @param OptionsResolver $resolver The resolver for the options
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([]);
    }
}
