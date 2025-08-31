<?php

namespace App\Controller\Admin;

use App\Entity\Goal;
use App\Entity\Address;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class GoalCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Goal::class;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
                

            AssociationField::new('player', 'Joueur (club)')
                ->setRequired(false)
                ->hideWhenUpdating(fn (Goal $goal) => $goal->getVisitorPlayer() !== null),

            AssociationField::new('visitorPlayer', 'Joueur (visiteur)')
                ->setRequired(false)
                ->hideWhenUpdating(fn (Goal $goal) => $goal->getPlayer() !== null),

            IntegerField::new('minuteGoal', 'Minute'),

            AssociationField::new('event', 'Match'),
        ];
    }
    
}
