<?php

namespace App\Controller\Admin;

use App\Entity\Coach;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class CoachCrudController extends AbstractCrudController
{
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }
    public static function getEntityFqcn(): string
    {
        return Coach::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Coach')
            ->setEntityLabelInPlural('Coachs')
        ;
    }
    
    public function configureFields(string $pageName): iterable
    {
        return [
            // AssociationField::new('user', 'Utilisateur')
            //     ->setQueryBuilder(function (QueryBuilder $qb) {
            //         return $qb
            //             ->leftJoin('entity.player', 'p')
            //             ->leftJoin('entity.coach', 'c')
            //             ->where('p.id IS NULL')
            //             ->andWhere('c.id IS NULL'); 
            //     }),
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
            
            AssociationField::new('user', 'Utilisateur')
                ->setQueryBuilder(function (QueryBuilder $qb) {
                    return $qb
                        ->leftJoin('entity.player', 'p')
                        ->leftJoin('entity.coach', 'c')
                        ->where('p.id IS NULL')
                        ->andWhere('c.id IS NULL'); 
                })
                ->onlyOnForms()
                ,
            // AssociationField::new('isCoachOf', 'est coach de'),
             TextField::new('isCoachOf', 'Nom de l\'équipe')
                ->formatValue(function ($value, $entity) {
                    $url = $this->adminUrlGenerator
                        ->setController(TeamCrudController::class) 
                        ->setAction('show') 
                        ->setEntityId($entity->getIsCoachOf()->getId())
                        ->generateUrl();

                    return sprintf(
                        '<a href="%s">%s</a>',
                        $url, 
                                $entity->getIsCoachOf()->getName(),
                            );
                })
                ->renderAsHtml(),
        ];
    }
    
}
