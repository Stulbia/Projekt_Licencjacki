<?php

namespace App\Controller\Admin;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\Review;
use App\Entity\ReviewTag;
use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class DashboardController extends AbstractDashboardController
{
    public function __construct(private readonly AdminUrlGenerator $adminUrlGenerator)
    {
    }

    #[Route('/admin', name: 'admin')]
    public function index(): Response
    {
        $url = $this->adminUrlGenerator
            ->setController(BookCrudController::class)
            ->generateUrl();

        return $this->redirect($url);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Panel Administratora');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Strona główna', 'fa fa-home');

        yield MenuItem::section('Zarządzanie treścią');
        yield MenuItem::linkToCrud('Książki', 'fas fa-book', Book::class);
        yield MenuItem::linkToCrud('Autorzy', 'fas fa-user-pen', Author::class);
        yield MenuItem::linkToCrud('Recenzje', 'fas fa-star', Review::class);
        yield MenuItem::linkToCrud('Tagi recenzji', 'fas fa-tags', ReviewTag::class);

        yield MenuItem::section('Użytkownicy');
        yield MenuItem::linkToCrud('Użytkownicy', 'fas fa-users', User::class);

        yield MenuItem::section('Powrót');
        yield MenuItem::linkToUrl('Strona główna', 'fas fa-arrow-left', '/');
    }
}
