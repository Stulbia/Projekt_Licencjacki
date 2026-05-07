<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\Tag;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly AdminUrlGenerator $adminUrlGenerator
    ) {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        // redirect od razu do listy książek
        $url = $this->adminUrlGenerator
            ->setController(BookCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Panel administracyjny');
    }


    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Strona główna', 'fa fa-home');

        yield MenuItem::section('Zasoby główne');
        yield MenuItem::linkToCrud('Książki', 'fas fa-book', Book::class);
        yield MenuItem::linkToCrud('Tagi Książek', 'fas fa-tags', Tag::class);
        yield MenuItem::linkToCrud('Autorzy', 'fas fa-user-pen', Author::class);

        yield MenuItem::section('Recenzje i tagi');
        yield MenuItem::linkToCrud('Recenzje', 'fas fa-star', Review::class);
        yield MenuItem::linkToCrud('Tagi recenzji', 'fas fa-tags', ReviewTag::class);

        yield MenuItem::section('Zarządzanie użytkownikami');
        yield MenuItem::linkToCrud('Użytkownicy', 'fas fa-users', User::class);

        yield MenuItem::section();
        yield MenuItem::linkToRoute('Powrót do aplikacji', 'fas fa-arrow-left', 'homepage');
    }
}
