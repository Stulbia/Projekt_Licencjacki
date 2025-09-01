<?php

namespace App\Form\Type;

use App\Entity\Account;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('portalSpolecznosciowy', ChoiceType::class, [
        'label'   => 'label.social_platform',
        'choices' => [
        'TikTok'    => 'tiktok',
        'YouTube'   => 'youtube',
        'Instagram' => 'instagram',
        'Facebook'  => 'facebook',
        ],
        'placeholder' => 'label.select_platform',
        ])
        ->add('link', UrlType::class, [
        'label'    => 'label.link',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => Account::class,
        ]);
    }
}
