<?php

namespace App\Form\Type;

use App\Entity\Author;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class AuthorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'Imię ',
                'required' => false,
            ])
            ->add('pseudonym', TextType::class, [
                'label' => 'Pseudonim ',
                'required' => false,
            ])
            ->add('name', TextType::class, [
                'label' => 'Nazwisko',
                'required' => false,
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Opis autora',
                'required' => false,
                'attr' => ['rows' => 6],
            ])
            ->add('photo', FileType::class, [
                'label' => 'Zdjęcie autora (JPG lub PNG)',
                'mapped' => false, // nie jest bezpośrednio powiązane z encją
                'required' => false,
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => ['image/jpeg', 'image/png'],
                        'mimeTypesMessage' => 'Dozwolone formaty to JPEG i PNG.',
                    ])
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Author::class,
        ]);
    }
}
