<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Player;
use App\Enum\EventTypeEnum;
use App\Service\PlayerStatsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserCrudController extends AbstractCrudController
{

    private UserPasswordHasherInterface $passwordHasher;
    private $em;
    private AdminUrlGenerator $adminUrlGenerator;
    private PlayerStatsService $playerStatsService;

    public function __construct(
        UserPasswordHasherInterface $passwordHasher, 
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator,
        PlayerStatsService $playerStatsService
        )
    {
        $this->passwordHasher = $passwordHasher;
        $this->em = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->playerStatsService = $playerStatsService;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Utilisateur')
            ->setEntityLabelInPlural('Utilisateurs')
        ;
    }
    
    public function configureActions(Actions $actions): Actions 
    {
        $show = Action::new('Afficher')->linkToCrudAction('show');

        return $actions
            ->add(Crud::PAGE_INDEX, $show);
    }

    public function show(AdminContext $context, Request $request, AdminUrlGenerator $adminUrlGenerator)
    {
        $id = $request->query->get('entityId'); 
        $user = $this->em->getRepository(User::class)->find($id);

        $editUrl = $adminUrlGenerator
            ->setController(UserCrudController::class) 
            ->setAction('edit')
            ->setEntityId($user->getId())
            ->generateUrl();

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable");
        }

        if($user->getRoles() && in_array('ROLE_PLAYER', $user->getRoles(), true)) {
            $player = $this->em->getRepository(Player::class)->findOneBy(['user' => $user]);
            if($player) {
                $team = $player->getPlaysInTeam();

                
                $stats = $this->playerStatsService->getPlayerStats( $team, $player);
                
                $variables = [
                    'user' => $user,
                    'current_url' => $request->getUri(),
                    'editUrl' => $editUrl,
                    'stats' => $stats,
                ];
            }
                 
        } else {
            $variables = [
                'user' => $user,
                'current_url' => $request->getUri(),
                'editUrl' => $editUrl,
            ];
        }
        return $this->render('admin/user.html.twig', $variables);
    }



    
    public function configureFields(string $pageName): iterable
    {
        $lastnameField = TextField::new('lastname', 'Nom de famille');

        if($pageName === Crud::PAGE_NEW) {
            $lastnameField->setFormTypeOption('data', 'Dupont');
        }

        return [
            TextField::new('firstname', 'Prénom')
                ->formatValue(function ($value, $entity) {
                    $url = $this->adminUrlGenerator
                        ->setController(UserCrudController::class) 
                        ->setAction('show') 
                        ->setEntityId($entity->getId())
                        ->generateUrl();

                    return sprintf(
                        '<a href="%s">%s</a>',
                        $url, 
                                $entity->getFirstname(),
                            );
                })
                ->renderAsHtml(),
            $lastnameField,
            DateField::new('birthdate', 'Date de naissance')
                ->setFormTypeOption('widget', 'single_text')
                ->setFormTypeOption('html5', true)
                ->setFormTypeOption('attr', ['max' => (new \DateTime())->format('Y-m-d')])
                ->setFormTypeOption('data', new \DateTime('2000-01-01'))
                ->setRequired(false)
                ->hideOnIndex(),
            TextField::new('email'),
            TextField::new('phone', 'Numéro de téléphone')
                ->setRequired(false),
            TextField::new('plainPassword', 'Mot de passe')
                ->setFormType(PasswordType::class)
                ->onlyOnForms()
                ->setHelp('Laissez vide en modification pour conserver le mot de passe actuel.')
                ->setRequired($pageName === Crud::PAGE_NEW),
            ChoiceField::new('roles', 'Rôles')
                ->setChoices([
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                    'Coach' => 'ROLE_COACH',
                    'Joueur' => 'ROLE_PLAYER',
                    'Parent' => 'ROLE_PARENT',
                    'Directeur' => 'ROLE_DIRECTOR',
                    'Secrétaire' => 'ROLE_SECRETARY',
                ])->setHelp('Sélectionnez les rôles de l\'utilisateur.')
                ->allowMultipleChoices()
                ->setRequired(true),
            ImageField::new('image_name')
                ->setLabel('Photo utilisateur')
                ->setHelp('Image du produit en 600x600')
                ->setBasePath('/uploads/users')
                ->setUploadDir('public/uploads/users')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                ->setRequired(false)
                
        ];
    }

    // private function getTeams(): array
    // {
    //     $teams = $this->entityManager->getRepository(Team::class)->findAll();
        
    //     $choices = [];
    //     foreach ($teams as $team) {
    //         $choices[$team->getName()] = $team->getId();
    //     }
    //     return $choices;
    // }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPlainPassword()) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword())
            );
        }
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User && $entityInstance->getPlainPassword()) {
            $entityInstance->setPassword(
                $this->passwordHasher->hashPassword($entityInstance, $entityInstance->getPlainPassword())
            );
        }
        parent::updateEntity($entityManager, $entityInstance);
    }


    
    
}
