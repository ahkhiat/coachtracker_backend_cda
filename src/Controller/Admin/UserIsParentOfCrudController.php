<?php

namespace App\Controller\Admin;

use App\Entity\UserIsParentOf;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserIsParentOfCrudController extends AbstractCrudController
{
    
    public function __construct(private AdminUrlGenerator $adminUrlGenerator)
    {
    }
    public static function getEntityFqcn(): string
    {
        return UserIsParentOf::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Parent')
            ->setEntityLabelInPlural('Parents de joueurs')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('user', 'Parent')
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
            AssociationField::new('user', 'Parent')
                ->setFormTypeOption('choice_label', function($user) {
                    return $user->getFirstname().' '.$user->getLastname();
                })
                ->onlyOnForms(),
                
            TextField::new('child', 'Enfant')
                ->formatValue(function ($value, $entity) {
                    $url = $this->adminUrlGenerator
                        ->setController(UserCrudController::class) 
                        ->setAction('show') 
                        ->setEntityId($entity->getChild()->getId())
                        ->generateUrl();

                    return sprintf(
                        '<a href="%s">%s %s</a>',
                        $url, 
                                $entity->getChild()->getFirstname(),
                                $entity->getChild()->getLastname()
                            );
                }
                )
                ->onlyOnIndex()
                ->renderAsHtml(),
            AssociationField::new('child', 'Enfant')
                ->setFormTypeOption('choice_label', function($user) {
                    return $user->getFirstname().' '.$user->getLastname();
                })
                ->onlyOnForms(),
        ];
    }
}
