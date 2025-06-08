<?php

namespace App\Controller\Admin;

use App\Entity\Book;
use App\Entity\Enum\BookStatus;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;

class BookCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Book::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->onlyOnIndex(),

            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title')->onlyOnForms(),

            TextEditorField::new('description')->hideOnIndex(),

            ChoiceField::new('status')
                ->setChoices([
                    'status.public' => BookStatus::PUBLIC,
                    'status.private' => BookStatus::PRIVATE,
                ]),

            AssociationField::new('author'),

            DateTimeField::new('createdAt')->onlyOnDetail(),
            DateTimeField::new('updatedAt')->onlyOnDetail(),
        ];
    }
}
