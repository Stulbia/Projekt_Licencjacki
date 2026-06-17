<?php

namespace App\Controller\Admin;

use App\Entity\ReviewTag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;

class ReviewTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReviewTag::class;
    }


    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Tag Recenzji')
            ->setEntityLabelInPlural('Tagi Recenzji');
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'etykieta'),
        ];
    }

}
