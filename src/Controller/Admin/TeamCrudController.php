<?php

namespace App\Controller\Admin;

use App\Entity\Club;
use App\Entity\Team;
use App\Enum\AgeCategoryEnum;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class TeamCrudController extends AbstractCrudController
{
    private $em;

    public function __construct(
        EntityManagerInterface $entityManager,
        AdminUrlGenerator $adminUrlGenerator
    )
    {
        $this->em = $entityManager;
        $this->adminUrlGenerator = $adminUrlGenerator;
    }
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

    public function configureActions(Actions $actions): Actions 
    {
        $show = Action::new('Afficher')->linkToCrudAction('show');

        return $actions
            ->add(Crud::PAGE_INDEX, $show);
    }

    public function show(AdminContext $context, Request $request)
    {
        $id = $request->query->get('entityId'); 
        $team = $this->em->getRepository(Team::class)->find($id);

        if (!$team) {
            throw $this->createNotFoundException("Equipe introuvable");
        }
   
        return $this->render('admin/team.html.twig', [
            'team' => $team,
            'current_url' => $request->getUri()
        ]);
    }

    public function createEntity(string $entityFqcn)
    {
        $team = new Team();

        $defaultClub = $this->em
            ->getRepository(Club::class)
            ->findOneBy(['name' => 'FA Marseille Féminin']);

        $team->setClub($defaultClub);

        return $team;
    }

    
    public function configureFields(string $pageName): iterable
    {
        return [
            AssociationField::new('club')
                ->setLabel('Club')
                ->setFormTypeOption('choice_label', 'name')
                ->setFormTypeOption('disabled', true),
            TextField::new('name', 'Nom de l\'équipe')
                ->formatValue(function ($value, $entity) {
                    $url = $this->adminUrlGenerator
                        ->setController(TeamCrudController::class) 
                        ->setAction('show') 
                        ->setEntityId($entity->getId())
                        ->generateUrl();

                    return sprintf(
                        '<a href="%s">%s</a>',
                        $url, 
                                $entity->getName(),
                            );
                })
                ->renderAsHtml(),

            ChoiceField::new('ageCategory', 'Catégorie d\'âge')
                ->setChoices(fn () => array_combine(
                    array_map(fn(AgeCategoryEnum $case) => $case->name, AgeCategoryEnum::cases()), // Labels
                    AgeCategoryEnum::cases()
                ))
                ->renderAsNativeWidget(false)


                

        ];
    }
    
}
