<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\File;

class UserAvatarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('avatar', FileType::class, [
            'label' => 'Wybierz avatar',
            'mapped' => false,
            'required' => true,
            'constraints' => [
                new File([
                    'maxSize' => '2M',
                    'mimeTypes' => ['image/jpeg', 'image/png'],
                    'mimeTypesMessage' => 'Dozwolone tylko pliki JPG lub PNG.',
                ])
            ],
        ]);
    }
}
