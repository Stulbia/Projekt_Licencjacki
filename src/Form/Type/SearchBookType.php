<?php

namespace App\Form\Type;

use App\Entity\Author;
use App\Entity\Enum\BookStatus;
use App\Entity\ReviewTag;
use App\Entity\Tag;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;

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
//            ->add('status', ChoiceType::class, [
//                'label' => 'label.book_status',
//                'required' => false,
//                'choices' => [
//                    'label.public' => BookStatus::PUBLIC->value,
//                    'label.private' => BookStatus::PRIVATE->value,
//                ],
//                'placeholder' => 'label.any',
//            ])
            ->add('tag', EntityType::class, [
                'class' => Tag::class,
                'choice_label' => 'title',
                'required' => false,
                'multiple' => true,
                'placeholder' => 'label.any_tag',
                'label' => 'label.tag',
            ])
            ->add('reviewTags', EntityType::class, [
                'class' => ReviewTag::class,
                'choice_label' => 'name',
                'required' => false,
                'multiple' => true,
                'placeholder' => 'label.any_review_tag',
                'label' => 'label.review_tags',
            ])
//            ->add('author', EntityType::class, [
//                'class'        => Author::class,
//                'choice_label' => function (Author $author): string {
//                    return $author->__toString();
//                },
//                'required'    => false,
//                'placeholder' => 'all',
//                'label'       => 'label.author',
//                'attr'        => ['class' => 'customSelect'],
//            ]);
//            ->add('author', SearchType::class, [
//                'label'    => 'label.author',
//                'required' => false,
//                'attr'     => [
//                    'placeholder' => 'label.author_placeholder',
//                    'class'       => 'textInput',
//                    'autocomplete' => 'off',
//                    'constraints' => [new Length(['max' => 50])]
//                ],
//            ]);
            ->add('authorTerm', SearchType::class, [
                'label' => 'Autor',
                'required' => false,
                'attr' => [
                    'placeholder' => '',
                    'autocomplete' => 'off',
                ],
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
