<?php

namespace App\Controller\Admin;

use App\Entity\UserIsParentOf;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class UserIsParentOfCrudController extends AbstractCrudController
{
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
            AssociationField::new('user', 'Parent'),
            AssociationField::new('child', 'Enfant'),
        ];
    }
}
