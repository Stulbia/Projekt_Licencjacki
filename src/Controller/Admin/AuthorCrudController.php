<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class AuthorCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Author::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Autor')
            ->setEntityLabelInPlural('Autorzy');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('firstName', 'Imię'),
            TextField::new('name', 'Nazwisko'),
            TextField::new('pseudonym', 'pseudonim'),
            TextEditorField::new('description', 'opis'),
            ImageField::new('photoFilename', 'Zdjęcie')
                ->setBasePath('uploads/authors')
                ->setUploadDir('public/uploads/authors')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false) ->hideOnIndex(),
            AssociationField::new('books') ->hideOnIndex(),
        ];
    }
}
