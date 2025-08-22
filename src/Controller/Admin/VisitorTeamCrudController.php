<?php

namespace App\Controller\Admin;

use App\Entity\VisitorTeam;
use App\Enum\AgeCategoryEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class VisitorTeamCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorTeam::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Equipe visiteuse')
            ->setEntityLabelInPlural('Equipes visiteuses')
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('club')
                ->setLabel('Club')
                ->setFormTypeOption('choice_label', 'name'),
            ChoiceField::new('ageCategory', 'Catégorie d\'âge')
                ->setChoices(fn () => array_combine(
                    array_map(fn(AgeCategoryEnum $case) => $case->name, AgeCategoryEnum::cases()), // Labels
                    AgeCategoryEnum::cases()
                ))
                ->renderAsNativeWidget(false)
        ];
    }
    
}
