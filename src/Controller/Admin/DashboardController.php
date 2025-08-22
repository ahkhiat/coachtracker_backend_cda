<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\Club;
use App\Entity\Team;
use App\Entity\User;
use App\Entity\Coach;
use App\Entity\Event;
use App\Entity\Player;
use App\Entity\Stadium;
use App\Entity\VisitorTeam;
use App\Entity\UserIsParentOf;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

#[AdminDashboard(routePath: '/admin', routeName: 'admin')]
class DashboardController extends AbstractDashboardController
{
    public function index(): Response
    {
        // return parent::index();
        return $this->render(view: 'admin/dashboard.html.twig');


        // Option 1. You can make your dashboard redirect to some common page of your backend
        //
        // 1.1) If you have enabled the "pretty URLs" feature:
        // return $this->redirectToRoute('admin_user_index');
        //
        // 1.2) Same example but using the "ugly URLs" that were used in previous EasyAdmin versions:
        // $adminUrlGenerator = $this->container->get(AdminUrlGenerator::class);
        // return $this->redirect($adminUrlGenerator->setController(OneOfYourCrudController::class)->generateUrl());

        // Option 2. You can make your dashboard redirect to different pages depending on the user
        //
        // if ('jane' === $this->getUser()->getUsername()) {
        //     return $this->redirectToRoute('...');
        // }

        // Option 3. You can render some custom template to display a proper dashboard with widgets, etc.
        // (tip: it's easier if your template extends from @EasyAdmin/page/content.html.twig)
        //
        // return $this->render('some/path/my-dashboard.html.twig');
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

        yield MenuItem::section('Gestion des utilisateurs', 'fas fa-users');

        yield MenuItem::linkToCrud('Utilisateurs', 'fas fa-list', entityFqcn: User::class)
            // ->setPermission('ROLE_ADMIN')
            ->setAction('index')
            ->setDefaultSort([
                'roles' => 'ASC',
                'firstname' => 'ASC'])
            ;
        yield MenuItem::linkToCrud('Coachs', 'fas fa-list', entityFqcn: Coach::class);
        yield MenuItem::linkToCrud('Joueurs', 'fas fa-list', entityFqcn: Player::class);
        yield MenuItem::linkToCrud('Parents', 'fas fa-list', entityFqcn: UserIsParentOf::class);

        yield MenuItem::section('Gestion des équipes');

        yield MenuItem::linkToCrud('Equipes', 'fas fa-users', entityFqcn: Team::class);
        yield MenuItem::linkToCrud('Equipes visiteuses', 'fas fa-users', entityFqcn: VisitorTeam::class);
        yield MenuItem::linkToCrud('Stades', 'fas fa-users', entityFqcn: Stadium::class);
        yield MenuItem::linkToCrud('Clubs', 'fas fa-users', entityFqcn: Club::class);   

        yield MenuItem::section('Gestion des adresses');
        yield MenuItem::linkToCrud('Adresses', 'fas fa-map-marker-alt', entityFqcn: Address::class);
    }
}
