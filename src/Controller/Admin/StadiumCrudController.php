<?php

namespace App\Controller\Admin;

use App\Entity\Stadium;
use App\Controller\Admin\AddressCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class StadiumCrudController extends AbstractCrudController
{
    private $adminUrlGenerator;

    public function __construct(AdminUrlGenerator $adminUrlGenerator)
    {
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
    
    public static function getEntityFqcn(): string
    {
        return Stadium::class;
    }
    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Stade')
            ->setEntityLabelInPlural('Stades')
        ;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            TextField::new('name', 'Nom du stade'),
            // AssociationField::new('address', 'Adresse')
            //     ->renderAsEmbeddedForm(
            //         AddressCrudController::class
            //     )

            // AssociationField::new('address', 'Adresse')
            //     ->autocomplete()

            AssociationField::new('address', 'Adresse')
                ->autocomplete()

                ->setCrudController(AddressCrudController::class)
                ->setFormTypeOption('by_reference', true)


        ];
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
}
