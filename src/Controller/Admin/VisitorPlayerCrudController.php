<?php

namespace App\Controller\Admin;

use App\Entity\Address;
use App\Entity\VisitorPlayer;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class VisitorPlayerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return VisitorPlayer::class;
    }

    public function configureCrud(Crud $crud):Crud
    {
        return $crud
            ->setEntityLabelInSingular('Joueur visiteur')
            ->setEntityLabelInPlural('Joueurs visiteurs')
            ->setHelp('index', 'Ce sont des joueurs fantomes, non enregistrés dans le système, qui permettent de suivre les statistiques des joueurs d\'équipes visiteuses.')
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('visitorTeam', 'Équipe'),
            AssociationField::new('visitorTeam', 'Équipe')
                ->setFormTypeOptions([
                    'query_builder' => function (EntityRepository $er) {
                        return $er->createQueryBuilder('t')
                            ->leftJoin('t.visitorPlayers', 'vp')
                            ->andWhere('vp.id IS NULL');
                    },
                ])
                ->onlyOnForms(),
        ];
    }
    
}