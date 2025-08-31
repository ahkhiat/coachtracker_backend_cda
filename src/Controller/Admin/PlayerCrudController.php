<?php

namespace App\Controller\Admin;

use App\Entity\Player;
use Doctrine\ORM\QueryBuilder;
use App\Repository\UserRepository;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class PlayerCrudController extends AbstractCrudController
{

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
    public static function getEntityFqcn(): string
    {
        return Player::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Joueur')
            ->setEntityLabelInPlural('Joueurs')
        ;
    }

    // public function configureFields(string $pageName): iterable
    // {
    //     return [
    //         TextField::new('user', 'Utilisateur')
    //             ->formatValue(function ($value, $entity) {
    //                 $url = $this->adminUrlGenerator
    //                     ->setController(UserCrudController::class) 
    //                     ->setAction('show') 
    //                     ->setEntityId($entity->getUser()->getId())
    //                     ->generateUrl();

    //                 return sprintf(
    //                     '<a href="%s">%s %s</a>',
    //                     $url, 
    //                             $entity->getUser()->getFirstname(),
    //                             $entity->getUser()->getLastname()
    //                         );
    //             })
    //             ->onlyOnIndex()
    //             ->renderAsHtml(),
            
    //         AssociationField::new('user', 'Utilisateur')
    //             ->setQueryBuilder(function (QueryBuilder $qb) {
    //                 return $qb->leftJoin('entity.player', 'p')
    //                     ->leftJoin('entity.coach', 'c')
    //                     ->where('p.id IS NULL')
    //                     ->andWhere('c.id IS NULL'); 

    //             })
    //             ->onlyOnForms()
    //             ,
    
    //         AssociationField::new('playsInTeam', 'Joue dans l\'équipe')
    //             ->setRequired(true)
    //         ,
    //     ];
    // }

     public function configureFields(string $pageName): iterable
    {
        $userField = AssociationField::new('user', 'Utilisateur')
            ->onlyOnForms();

        if ($pageName === Crud::PAGE_NEW) {
            $userField = $userField->setQueryBuilder(function (QueryBuilder $qb) {
                $qb->leftJoin('entity.player', 'p')
                ->leftJoin('entity.coach', 'c')
                ->where('p.id IS NULL')
                ->andWhere('c.id IS NULL');

                return $qb;
            });
        }

        return [
            TextField::new('user', 'Utilisateur')
                ->formatValue(function ($value, $entity) {
                    $url = $this->adminUrlGenerator
                        ->setController(UserCrudController::class) 
                        ->setAction('show') 
                        ->setEntityId($entity->getUser()->getId())
                        ->generateUrl();

                    return sprintf(
                        '<a href="%s">%s %s</a>',
                        $url, 
                        $entity->getUser()->getFirstname(),
                        $entity->getUser()->getLastname()
                    );
                })
                ->onlyOnIndex()
                ->renderAsHtml(),

            $userField,

            TextField::new('playsInTeam', 'Nom de l\'équipe')
                ->formatValue(function ($value, $entity) {
                    $url = $this->adminUrlGenerator
                        ->setController(TeamCrudController::class) 
                        ->setAction('show') 
                        ->setEntityId($entity->getPlaysInTeam()->getId())
                        ->generateUrl();

                    return sprintf(
                        '<a href="%s">%s</a>',
                        $url, 
                                $entity->getPlaysInTeam()->getName(),
                            );
                })
                ->renderAsHtml(),
        ];
    }

  
    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('playsInTeam')
                ->setLabel('Équipe')
            );
    }
    
}
