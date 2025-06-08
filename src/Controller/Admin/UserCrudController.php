<?php
namespace App\Controller\Admin {
    use App\Entity\User;
    use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
    use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
    use Doctrine\ORM\EntityManagerInterface;
    use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
    use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
    use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
    use Symfony\Component\Form\Extension\Core\Type\PasswordType;
    use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

    class UserCrudController extends AbstractCrudController
    {
        public function __construct(private readonly UserPasswordHasherInterface $passwordHasher)
        {
        }

        public static function getEntityFqcn(): string
        {
            return User::class;
        }

        public function configureFields(string $pageName): iterable
        {
            return [
                IdField::new('id')->onlyOnIndex(),
                EmailField::new('email'),
                Field::new('plainPassword')
                    ->setFormType(PasswordType::class)
                    ->onlyOnForms()
                    ->setLabel('Nowe hasło')
                    ->setRequired($pageName === Crud::PAGE_NEW), // tylko przy tworzeniu wymagane
            ];
        }

        public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
        {
            $this->handlePassword($entityInstance);
            parent::persistEntity($entityManager, $entityInstance);
        }

        public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
        {
            $this->handlePassword($entityInstance);
            parent::updateEntity($entityManager, $entityInstance);
        }

        private function handlePassword(object $entity): void
        {
            if (!$entity instanceof User) {
                return;
            }

            if ($entity->getPlainPassword()) {
                $hashed = $this->passwordHasher->hashPassword($entity, $entity->getPlainPassword());
                $entity->setPassword($hashed);
                $entity->setPlainPassword(null); // wyczyść dla bezpieczeństwa
            }
        }
    }
}
