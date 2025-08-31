<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Entity\Goal;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Coach;
use App\Entity\Event;
use App\Entity\Player;
use App\Entity\Address;
use App\Entity\Stadium;
use App\Entity\VisitorTeam;
use App\Entity\VisitorPlayer;
use App\Entity\UserIsParentOf;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        $usersCount = 120;  
        $ordersCount = 54;  
        $revenue = 2340;  

        return $this->render('admin/dashboard.html.twig', [
            'users' => $usersCount,
            'orders' => $ordersCount,
            'revenue' => $revenue,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Coachtracker Backend CDA');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion des évenements', 'fas fa-calendar-alt');
        yield MenuItem::linkToCrud('Evénements', 'fas fa-list', entityFqcn: Event::class);
        yield MenuItem::linkToCrud('Buts', 'fas fa-futbol', entityFqcn: Goal::class);

        yield MenuItem::section('Gestion des utilisateurs', 'fas fa-users');

        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-user', entityFqcn: User::class)
            // ->setPermission('ROLE_ADMIN')
            ->setAction('index')
            ->setDefaultSort([
                'roles' => 'ASC',
                'firstname' => 'ASC'])
            ;
        yield MenuItem::linkToCrud('Coachs', 'fas fa-user', entityFqcn: Coach::class);
        yield MenuItem::linkToCrud('Joueurs', 'fas fa-user', entityFqcn: Player::class);
        yield MenuItem::linkToCrud('Parents', 'fas fa-user', entityFqcn: UserIsParentOf::class);

        yield MenuItem::section('Gestion des équipes');

        yield MenuItem::linkToCrud('Equipes', 'fas fa-users', entityFqcn: Team::class);
        yield MenuItem::linkToCrud('Stades', 'fas fa-users', entityFqcn: Stadium::class);
        yield MenuItem::linkToCrud('Clubs', 'fas fa-users', entityFqcn: Club::class);   

        yield MenuItem::section('Gestion des visiteurs');

        yield MenuItem::linkToCrud('Equipes visiteuses', 'fas fa-users', entityFqcn: VisitorTeam::class);
        yield MenuItem::linkToCrud('Joueurs visiteurs', 'fas fa-user', entityFqcn: VisitorPlayer::class);

        yield MenuItem::section('Gestion des adresses');
        yield MenuItem::linkToCrud('Adresses', 'fas fa-map-marker-alt', entityFqcn: Address::class);
    }
}
