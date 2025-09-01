<?php

namespace App\Form\Type;

use App\Entity\UserBookRelation;
use App\Enum\ReadingStatus;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserBookRelationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('status', ChoiceType::class, [
        'choices' => [
        'Chcę przeczytać' => ReadingStatus::TO_READ,
        'Czytam' => ReadingStatus::READING,
        'Przeczytane' => ReadingStatus::READ,
        ],
        'label' => 'Status czytania',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => UserBookRelation::class,
        ]);
    }
}
