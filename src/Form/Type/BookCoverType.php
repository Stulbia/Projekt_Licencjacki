<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class BookCoverType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('cover', FileType::class, [
        'label' => 'Wybierz okładkę',
        'mapped' => false,
        'required' => true,
        'constraints' => [
        new File([
        'maxSize' => '5M',
        'mimeTypes' => ['image/jpeg', 'image/png'],
        'mimeTypesMessage' => 'Dozwolone tylko pliki JPG lub PNG.',
        ])
        ],
        ]);
    }
}
