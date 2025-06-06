<?php

namespace App\Form\Type;

use App\Entity\Book;
use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\ReviewTagAssignment;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ReviewType.
 */
class ReviewType extends AbstractType
{
    /**
     * Builds the form.
     *
     * @param FormBuilderInterface $builder The form builder
     * @param array<string, mixed> $options Form options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('book', EntityType::class, [
                'class' => Book::class,
                'choice_label' => 'title',
                'label' => 'label.book',
                'placeholder' => 'label.select_book',
                'required' => true,
            ])
            ->add('rating', IntegerType::class, [
                'label' => 'label.rating',
                'attr' => ['min' => 1, 'max' => 10],
                'required' => true,
            ])
            ->add('comment', TextareaType::class, [
                'label' => 'label.comment',
                'required' => true,
                'attr' => ['rows' => 5],
            ])
            ->add('reviewTags', EntityType::class, [
                'class' => ReviewTag::class,
                'choice_label' => 'name',
                'label' => 'label.review_tags',
                'required' => false,
                'multiple' => true,
                'expanded' => true,
                'mapped' => false, // <- ważne! nie powiązane bezpośrednio z encją Review
            ]);

    }

    /**
     * Configures the options for this type.
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Review::class]);
    }

    /**
     * Returns the prefix of the template block name for this type.
     */
    public function getBlockPrefix(): string
    {
        return 'review';
    }
}
