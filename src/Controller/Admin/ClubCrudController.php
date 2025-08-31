<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Form\AddressType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class ClubCrudController extends AbstractCrudController
{
    private $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
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

    public function configureActions(Actions $actions): Actions
    {
        $url = $this->adminUrlGenerator
            ->setController(AddressCrudController::class)
            ->setAction(Action::NEW)
            ->generateUrl();

        $addAddress = Action::new('addAddress', '➕ Ajouter une adresse')
            ->linkToUrl($url);

        return $actions
            ->add(Crud::PAGE_NEW, $addAddress)   // bouton visible sur formulaire création stade
            ->add(Crud::PAGE_EDIT, $addAddress); // bouton visible sur formulaire édition stade
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
                ->setBasePath('/uploads/clubs')
                ->setUploadDir('public/uploads/clubs')
                ->setUploadedFileNamePattern('[year]-[month]-[day]-[contenthash].[extension]')
                ->setRequired($required),
            // AssociationField::new('address', 'Adresse')
            //     ->renderAsEmbeddedForm(
            //         AddressCrudController::class
            //     )
            AssociationField::new('address', 'Adresse')
                ->autocomplete()

                ->setCrudController(AddressCrudController::class)
                ->setFormTypeOption('by_reference', true)
        ];
       
    }
    
}
