<?php

namespace App\Form\Type;

use App\Entity\Enum\BookStatus;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SearchBookType.
 */
class SearchBookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->setMethod('GET')
            ->add('title', SearchType::class, [
                'label' => 'label.title',
                'required' => false,
            ])
            ->add('description', SearchType::class, [
                'label' => 'label.description',
                'required' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => 'label.book_status',
                'required' => false,
                'choices' => [
                    'label.public' => BookStatus::PUBLIC->value,
                    'label.private' => BookStatus::PRIVATE->value,
                ],
                'placeholder' => 'label.any',
            ])
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'title',
                'required' => false,
                'placeholder' => 'label.any_tag',
                'label' => 'label.tag',
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix(): string
    {
        return '';
    }
}
