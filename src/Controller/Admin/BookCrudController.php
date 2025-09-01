<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
//            IdField::new('id')->onlyOnIndex(),
            TextField::new('title'),
            TextEditorField::new('description'),
            ImageField::new('coverFilename', 'Photo')
                ->setBasePath('uploads/covers')        // do wyświetlania
                ->setUploadDir('public/uploads/covers') // do zapisu
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false),
            AssociationField::new('tags') ->hideOnIndex(),
        ];
    }
}
