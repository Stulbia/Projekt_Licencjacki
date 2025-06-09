<?php

namespace App\Form\Type;

use App\Entity\ReviewTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Dto\ReviewSearchFiltersDto;

class ReviewSearchType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('tagIds', EntityType::class, [
        'class' => ReviewTag::class,
        'choice_label' => 'name',
        'multiple' => true,
        'expanded' => true,
        'required' => false,
        ])
        ->add('search', SearchType::class, [
        'required' => false,
        'label' => 'Treść recenzji zawiera',
        ])
        ->add('minRating', IntegerType::class, [
        'required' => false,
        'label' => 'Minimalna ocena',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
        'data_class' => ReviewSearchFiltersDto::class,
        'method' => 'GET',
        'csrf_protection' => false,
        ]);
    }
}
