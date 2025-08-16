<?php

namespace App\Controller\Admin;

use App\Entity\Team;
use App\Enum\AgeCategoryEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TeamCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Team::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Equipe')
            ->setEntityLabelInPlural('Equipes')
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            // IdField::new('id'),
            TextField::new('name'),
            ChoiceField::new('ageCategory', 'Catégorie d\'âge')
                ->setChoices(fn () => array_combine(
                    array_map(fn(AgeCategoryEnum $case) => $case->name, AgeCategoryEnum::cases()), // Labels
                    AgeCategoryEnum::cases()
                ))
                ->renderAsNativeWidget(false)


                

        ];
    }
    
}
