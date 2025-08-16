<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\SeasonEnum;
use App\Enum\EventTypeEnum;
use App\Enum\EventStatusEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EventCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Event::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Evénement')
            ->setEntityLabelInPlural('Evénements')
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateField::new('date'),
            AssociationField::new('team')
                ->setLabel('Equipe')
                ->setFormTypeOption('choice_label', 'name'),
            AssociationField::new('visitorTeam')
                ->setLabel('Visiteur')
                // ->setFormTypeOption('choice_label', 'name')
                ,
            AssociationField::new('stadium')
                ->setLabel('Lieu')
                ->setFormTypeOption('choice_label', 'name'),
            ChoiceField::new('eventType', 'Type d\'événement')
                ->setChoices(fn () => array_combine(
                    array_map(fn(EventTypeEnum $case) => $case->label(), EventTypeEnum::cases()), 
                    EventTypeEnum::cases()
                ))
                ->renderAsNativeWidget(false),

            ChoiceField::new('season', 'Saison')
                ->setChoices(fn () => array_combine(
                    array_map(fn(SeasonEnum $case) => $case->value, SeasonEnum::cases()), 
                    SeasonEnum::cases()
                ))
                ->renderAsNativeWidget(false),

            ChoiceField::new('status', 'Statut')
                ->setChoices(fn () => array_combine(
                    array_map(fn(EventStatusEnum $case) => $case->label(), EventStatusEnum::cases()), 
                    EventStatusEnum::cases()
                ))
                ->renderAsNativeWidget(false)
                ->onlyWhenUpdating(),
            Field::new('status', 'Statut', )
                    ->formatValue(function ($value) {
                        return $value?->label();
                    }   )
            //     ->formatValue(fn($value) => $value?->label())
            //     ->hideOnForm(),

            // L'affichage de la propriété status ne marche pas dans la page View

            

            

        ];
    }
    
}
