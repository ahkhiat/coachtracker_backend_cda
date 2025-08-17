<?php

namespace App\Controller\Admin;

use App\Entity\Event;
use App\Enum\SeasonEnum;
use App\Enum\EventTypeEnum;
use App\Enum\EventStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class EventCrudController extends AbstractCrudController
{
    private $em;
    private $adminUrlGenerator;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->em = $entityManager;
    }

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

    public function configureActions(Actions $actions): Actions 
    {
        $show = Action::new('Afficher')->linkToCrudAction('show');

        return $actions
            ->add(Crud::PAGE_INDEX, $show)
            ;
    }

    
    public function changeStatus($event, $status)
    {
        $event->setStatus(EventStatusEnum::from($status));

        $this->em->flush();

        $this->addFlash('success', "Statut de l'évenement correctement mis à jour");

    }


   public function show(AdminContext $context, Request $request)
    {
        $id = $request->query->get('entityId'); 
        $event = $this->em->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException("Événement introuvable");
        }

        // Gestion du changement de statut
        if ($request->query->get('status')) {
            $this->changeStatus($event, $request->query->get('status'));
        }

        // Sélection du template et des variables
        if ($event->getEventType() === EventTypeEnum::MATCH) {
            $template = 'admin/eventMatch.html.twig';
            $variables = [
                'match' => $event,
                'current_url' => $request->getUri(),
                'statusOptions' => EventStatusEnum::cases(),
            ];
        } elseif ($event->getEventType() === EventTypeEnum::TRAINING) {
            $template = 'admin/eventTraining.html.twig';
            $variables = [
                'training' => $event,
                'current_url' => $request->getUri(),
                'statusOptions' => EventStatusEnum::cases(),
            ];
        } else {
            $template = 'admin/event.html.twig';
            $variables = [
                'event' => $event,
                'current_url' => $request->getUri(),
                'statusOptions' => EventStatusEnum::cases(),
            ];
        }

        return $this->render($template, $variables);
    }


    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('date', 'Date et heure')
                ->setFormat('dd/MM/yyyy HH:mm'),            

            ChoiceField::new('eventType', 'Type d\'événement')
                ->setChoices(fn () => array_combine(
                    array_map(fn(EventTypeEnum $case) => $case->label(), EventTypeEnum::cases()), 
                    EventTypeEnum::cases()
                ))
                ->renderAsNativeWidget(false)
                ->formatValue(fn ($value): mixed => $value?->label()),

            AssociationField::new('team')
                ->setLabel('Equipe')
                ->setFormTypeOption('choice_label', 'name'),

            AssociationField::new('visitorTeam')
                ->setLabel('Visiteur'),
                // ->setFormTypeOption('choice_label', 'name')
            
            AssociationField::new('stadium')
                ->setLabel('Lieu')
                ->setFormTypeOption('choice_label', 'name'),


            ChoiceField::new('season', 'Saison')
                ->setChoices(fn () => array_combine(
                    array_map(fn(SeasonEnum $case) => $case->value, SeasonEnum::cases()), 
                    SeasonEnum::cases()
                ))
                ->renderAsNativeWidget(false)
                ->formatValue(fn ($value): mixed => $value?->value)
                ,

            ChoiceField::new('status', 'Statut')
                ->setChoices(fn () => array_combine(
                    array_map(fn(EventStatusEnum $case) => $case->label(), EventStatusEnum::cases()), 
                    EventStatusEnum::cases()
                ))
                ->renderAsNativeWidget(false)
                // ->formatValue(fn ($value): mixed => $value?->label())
                ->setTemplatePath('admin/eventStatusBadge.html.twig')
        ];

        
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('eventType')
                ->setChoices([
                    'Match' => EventTypeEnum::MATCH,
                    'Training' => EventTypeEnum::TRAINING,
                ])
            );
    }

    
}
