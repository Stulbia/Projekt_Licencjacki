<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name', 'Imię i nazwisko'),
            EmailField::new('email', 'Email'),
            ImageField::new('avatarFilename', 'Avatar')
                ->setBasePath('uploads/avatars')
                ->setUploadDir('public/uploads/avatars')
                ->setUploadedFileNamePattern('[slug]-[timestamp].[extension]')
                ->setRequired(false),
            ArrayField::new('roles', 'Role')->onlyOnDetail(),
            BooleanField::new('banned', 'Zbanowany'),
            TextEditorField::new('opis', 'Opis')->hideOnIndex(),
            // Pole do zmiany hasła – tylko w formularzu dodawania/edycji!
            TextField::new('plainPassword', 'Nowe hasło')
                ->onlyOnForms()
                ->setRequired(false)
                ->setHelp('Wpisz tylko jeśli chcesz ustawić nowe hasło.'),
          CollectionField::new('accounts', "Odnośniki")
                ->useEntryCrudForm(AccountCrudController::class)
                ->setFormTypeOption('by_reference', false)
//
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->encodePassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function encodePassword($user)
    {
        if (!$user instanceof User) {
            return;
        }

        if ($user->getPlainPassword()) {
            $hashed = $this->passwordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($hashed);
            $user->setPlainPassword(null);
        }
    }
}
