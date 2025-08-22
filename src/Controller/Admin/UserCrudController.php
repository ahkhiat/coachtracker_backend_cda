<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
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
    private $adminUrlGenerator;

    public function __construct(UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->em = $entityManager;
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
            ->add(Crud::PAGE_INDEX, $show)
            ;
    }
    public function show(AdminContext $context, Request $request)
    {
        $id = $request->query->get('entityId'); 
        $user = $this->em->getRepository(User::class)->find($id);

        if (!$user) {
            throw $this->createNotFoundException("Utilisateur introuvable");
        }
   
        return $this->render('admin/user.html.twig', [
            'user' => $user,
            'current_url' => $request->getUri()
        ]);
    }

    
    public function configureFields(string $pageName): iterable
    {
        
        return [
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom de famille'),
            DateField::new('birthdate', 'Date de naissance'),
            TextField::new('email'),
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
