<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AuthorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Author::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName'),
            TextField::new('name'),
            TextField::new('pseudonym'),
            TextEditorField::new('description'),
            ImageField::new('photoFilename', 'Photo')
                ->setBasePath('uploads/authors')
                ->setUploadDir('public/uploads/authors')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false) ->hideOnIndex(),
            ArrayField::new('books') ->hideOnIndex(),
        ];
    }
}
