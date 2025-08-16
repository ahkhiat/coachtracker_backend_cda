<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Entity\User;
use App\Entity\Player;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserCrudController extends AbstractCrudController
{

    public function __construct(private EntityManagerInterface $entityManager)
    {
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

    
    public function configureFields(string $pageName): iterable
    {
        
        return [
            TextField::new('firstname', 'Prénom'),
            TextField::new('lastname', 'Nom de famille'),
            DateField::new('birthdate', 'Date de naissance'),
            TextField::new('email'),
            
            ChoiceField::new('team', 'Equipe')
                ->setChoices($this->getTeams())
                ->setRequired(false)
                ->onlyOnForms()


        ];
    }

    private function getTeams(): array
    {
        $teams = $this->entityManager->getRepository(Team::class)->findAll();
        
        $choices = [];
        foreach ($teams as $team) {
            $choices[$team->getName()] = $team->getId();
        }
        return $choices;
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance instanceof User) {
            $request = $this->getContext()->getRequest();
            $selectedTeamId = $request->request->all('team');

            if (!empty($selectedTeamId)) {
                $player = $this->entityManager->getRepository(Player::class)->findOneBy(['user' => $entityInstance]);

                if ($player) {
                    $team = $this->entityManager->getRepository(Team::class)->find($selectedTeamId);
                    if ($team) {
                        $player->setPlaysInTeam($team);
                        $this->entityManager->persist($player);
                        $this->entityManager->flush();
                    }
                }
            }
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
    
}
