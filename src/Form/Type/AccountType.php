<?php

namespace App\Form\Type;

use App\Entity\Account;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;


class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('portalSpolecznosciowy', ChoiceType::class, [
                'label' => 'label.social_platform',
                'choices' => [
                    'TikTok'    => 'tiktok',
                    'YouTube'   => 'youtube',
                    'Instagram' => 'instagram',
                    'Facebook'  => 'facebook',
                ],
                'placeholder' => 'label.select_platform',
            ])
            ->add('link', UrlType::class, [
                'label' => 'label.link',
            ])
            ->add('displayAs', \Symfony\Component\Form\Extension\Core\Type\TextType::class, [
                'label' => 'label.display_as',
                'required' => false,
                'attr' => [
                    'maxlength' => 100,
                    'placeholder' => 'np. @twoj_nick lub „Mój Instagram”',
                ],
                'help' => 'form.account.display_as.help',
                'constraints' => [
                    new Length(max: 100),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Account::class,
        ]);
    }
}