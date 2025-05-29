<?php

/**
 * \App\Entity\User type.
 */

namespace App\Form\Type;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class UserType.
 */
class UserType extends AbstractType
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
        ->add('email', EmailType::class, [
            'label' => 'label.email',
            'constraints' => [
                new NotBlank([
                    'message' => 'message.email.not_blank',
                ]),
            ],
        ])
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'label.name',
                'attr' => ['max_length' => 60],
                'constraints' => [
                    new NotBlank([
                        'message' => 'message.name.not_blank',
                    ]),
                ],
            ])
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options' => [
                    'constraints' => [
                        new NotBlank([
                            'message' => 'message.enter_password',
                        ]),
                    ],
                    'label' => 'label.password',
                ],
                'second_options' => [
                    'label' => 'label.confirm_password',
                ],
                'invalid_message' => 'message.confirm_password',
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
