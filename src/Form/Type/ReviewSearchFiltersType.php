<?php

namespace App\Form\Type;

use App\Dto\ReviewSearchFiltersDto;
use App\Entity\ReviewTag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;

class ReviewSearchFiltersType extends AbstractType
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
            ->add('search', TextType::class, [
                'required' => false,
                'label' => 'Treść recenzji zawiera',
            ])
            ->add('minRating', IntegerType::class, [
                'required' => false,
                'label' => 'Minimalna ocena',
            ]);
    }
}
