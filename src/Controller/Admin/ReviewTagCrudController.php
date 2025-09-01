<?php

namespace App\Controller\Admin;

use App\Entity\ReviewTag;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ReviewTagCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ReviewTag::class;
    }


    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name'),
        ];
    }

}
