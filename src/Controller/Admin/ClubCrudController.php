<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Form\AddressType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ClubCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Club::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Club')
            ->setEntityLabelInPlural('Clubs')
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        $required = true;

        if ($pageName == 'edit') {
            $required = false;
        }

        return [
            TextField::new('name', 'Nom du club'),
            ImageField::new('image_name')
                ->setLabel('Logo du club')
                ->setHelp('Image du produit en 600x600')
                ->setBasePath('/uploads')
                ->setUploadDir('public/uploads')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                ->setRequired($required),
            AssociationField::new('address', 'Adresse')
                ->renderAsEmbeddedForm(
                    AddressCrudController::class
                )
        ];
       
    }
    
}
