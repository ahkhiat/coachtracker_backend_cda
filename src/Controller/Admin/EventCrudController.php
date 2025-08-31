<?php

namespace App\Controller\Admin;

use DatePeriod;
use DateInterval;
use App\Entity\Goal;
use App\Entity\Event;
use App\Form\GoalType;
use App\Entity\Stadium;
use App\Entity\Presence;
use App\Enum\SeasonEnum;
use App\Entity\Convocation;
use App\Enum\EventTypeEnum;
use App\Service\GoalService;
use App\Entity\VisitorPlayer;
use App\Enum\EventStatusEnum;
use App\Enum\PresenceStatusEnum;
use App\Form\OneConvocationType;
use App\Service\EventUrlService;
use App\Service\PresenceService;
use App\Service\ConvocationService;
use App\Repository\PlayerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormInterface;
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
use Symfony\Component\HttpFoundation\RedirectResponse;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
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
    private $convocationService;
    private $eventUrlService;
    private $playerRepository;
    private $goalService;
    private $presenceService;


    public function __construct(
        EntityManagerInterface $entityManager,
        ConvocationService $convocationService,
        PresenceService $presenceService,
        EventUrlService $eventUrlService,
        AdminUrlGenerator $adminUrlGenerator,
        PlayerRepository $playerRepository,
        GoalService $goalService
        )
    {
        $this->em = $entityManager;
        $this->convocationService = $convocationService;
        $this->presenceService = $presenceService;
        $this->eventUrlService = $eventUrlService;
        $this->adminUrlGenerator = $adminUrlGenerator;
        $this->playerRepository = $playerRepository;
        $this->goalService = $goalService;
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
            ->setDefaultSort(['date' => 'ASC'])
        ;
    }

    public function configureActions(Actions $actions): Actions 
    {
        $show = Action::new('Afficher')->linkToCrudAction('show');

        $createConvocations = Action::new('createConvocations', 'Créer convocations')
        ->linkToCrudAction('createConvocationsAction');

        $createPresences = Action::new('createPresences', 'Créer présences')
        ->linkToCrudAction('createPresencesAction');

        return $actions
            ->add(Crud::PAGE_INDEX, actionNameOrObject: $createPresences)
            ->add(Crud::PAGE_INDEX, $createConvocations)
            ->add(Crud::PAGE_INDEX, actionNameOrObject: $show)
            ;
    }

    
    public function changeStatus($event, $status)
    {
        $event->setStatus(EventStatusEnum::from($status));

        $this->em->flush();

        $this->addFlash('success', "Statut de l'évenement correctement mis à jour");
    }

    public function changePresenceStatus($presence, $status)
    {
        $presence->setStatus(PresenceStatusEnum::from($status));
        $playerName = $presence->getPlayer() && $presence->getPlayer()->getUser() ? $presence->getPlayer()->getUser() : 'Joueur visiteur';

        $this->em->flush();

        $this->addFlash('success', "Présence de {$playerName} correctement mis à jour");
    }

    public function createEntity(string $entityFqcn)
    {
        $event = new Event();

        $defaultStadium = $this->em
            ->getRepository(Stadium::class)
            ->findOneBy(['name' => 'Stade de la Fourragère']);

        $event->setStadium($defaultStadium);

        $year = (int) date('Y');
        $month = (int) date('m');

        if ($month >= 8) {
            $seasonStr = $year . '-' . ($year + 1) % 100; // 2025-26
        } else {
            $seasonStr = ($year - 1) . '-' . $year % 100; // 2024-25
        }

        $seasonEnum = null;
        foreach (SeasonEnum::cases() as $case) {
            if ($case->value === $seasonStr) {
                $seasonEnum = $case;
                break;
            }
        }

        if ($seasonEnum) {
            $event->setSeason($seasonEnum);
        }

        return $event;
    }

    public function persistEntity(EntityManagerInterface $em, $entityInstance): void
    {
        parent::persistEntity($em, $entityInstance);

        if ($entityInstance->getIsRecurring()) {
            $startDate = $entityInstance->getDate();
            $endDate = new \DateTime('2026-06-30'); 

            $dayOfWeek = $startDate->format('l'); 

            $this->createRecurringEvents($entityInstance, $dayOfWeek, $startDate, $endDate);
        }
    }


    function createRecurringEvents(Event $event, string $dayOfWeek, \DateTime $startDate, \DateTime $endDate)
    {
        $interval = new \DateInterval('P1D');
        $period = new \DatePeriod((clone $startDate)->modify('+1 day'), $interval, $endDate);

        $hour = (int) $startDate->format('H');
        $minute = (int) $startDate->format('i');
        $second = (int) $startDate->format('s');

        foreach ($period as $date) {
            if ($date->format('l') === $dayOfWeek) {
                $newEvent = new Event();
                $newEvent->setEventType($event->getEventType());
                $newEvent->setTeam($event->getTeam());
                $newEvent->setStadium($event->getStadium());
                $newEvent->generateUuid();

                $dateWithTime = (clone $date)->setTime($hour, $minute, $second);
                $newEvent->setDate($dateWithTime);

                $newEvent->setSeason($event->getSeason());
                $newEvent->setIsRecurring(false);

                $this->em->persist($newEvent);
            }
        }
        $this->em->flush();
    }

    public function createConvocationsAction(
        AdminContext $context, 
        ConvocationService $convocationService,
        Request $request
        ): RedirectResponse
    {
        $id = $request->query->get('entityId'); 
        $event = $this->em->getRepository(Event::class)->find($id);

        $count = $convocationService->createConvocationsForEvent($event);

        $this->addFlash('success', "$count convocations créées pour tous les joueurs.");

        return $this->redirect($context->getReferrer() ?? $this->adminUrlGenerator
                ->setController(self::class)
                ->setAction('show')
                ->setEntityId($event->getId())
                ->generateUrl());   
    }

    public function deleteConvocationAction(AdminContext $context)
    {
    
        $request = $context->getRequest();
        $convocationId = $request->query->get('convocationId');

        if (!$convocationId) {
            $this->addFlash('danger', 'Convocation introuvable.');
            return $this->redirect($context->getReferrer());
        }

        $convocation = $this->em->getRepository(Convocation::class)->find($convocationId);
        if (!$convocation) {
            $this->addFlash('danger', 'Convocation introuvable.');
            return $this->redirect($context->getReferrer());
        }

        $event = $convocation->getEvent();

        $this->convocationService->deleteConvocation($convocation);
        $this->addFlash('success', 'Convocation supprimée !');

        $showUrl = $this->eventUrlService->generateShowEventUrl($event);
        return $this->redirect($showUrl);
    }

    public function createPresencesAction(
        AdminContext $context,
        PresenceService $presenceService,
        Request $request
        ) : RedirectResponse
    {
        $id = $request->query->get('entityId');
        $event = $this->em->getRepository(Event::class)->find($id);

        $count = $presenceService->createPresencesForEvent($event);

        $this->addFlash('success', "$count présences crées");

        return $this->redirect($context->getReferrer() ?? $this->adminUrlGenerator
                    ->setController(self::class)
                    ->setAction('show')
                    ->setEntityId($event->getId())
                    ->generateUrl());
    }

    public function show(AdminContext $context, Request $request)
    {
        $id = $request->query->get('entityId'); 
        $event = $this->em->getRepository(Event::class)->find($id);

        $convocations = $event->getConvocations();
        $createConvocationsUrl = $this->eventUrlService->generateCreateConvocationsUrl($event);

        $presences = $event->getPresences();
        $createPresencesUrl = $this->eventUrlService->generateCreatePresencesUrl($event);

        $players = $event->getTeam() ? $event->getTeam()->getPlayers() : [];

        $goals = $event->getGoals();

        $statusOptions = [];
        if ($event->getStatus() !== EventStatusEnum::FINISHED) {
            $statusOptions = EventStatusEnum::cases();
        }

        if (!$event) {
            throw $this->createNotFoundException("Événement introuvable");
        }

        if ($request->query->get('status')) {
            $this->changeStatus($event, $request->query->get('status'));
        }
        if ($request->query->get('presence') && $request->query->get('presenceStatus')) {
            $presenceId = $request->query->get('presence');
            $presence = $this->em->getRepository(Presence::class)->find($presenceId);
            if ($presence) {
                $this->changePresenceStatus($presence, $request->query->get('presenceStatus'));
            }
        }

        $goalForm = $this->handleGoalForm($request, $event);

        $oneConvocationForm = $this->handleConvocationForm($request, $event);

        if ($event->getEventType() === EventTypeEnum::MATCH) {
            $template = 'admin/eventMatch.html.twig';
            $variables = [
                'event' => $event,
                'current_url' => $request->getUri(),
                'statusOptions' => $statusOptions,
                'presenceOptions' => PresenceStatusEnum::cases(),
                'homeTeam' => $event->getTeam(),
                'awayTeam' => $event->getVisitorTeam(),
                'homeTeamScore' => $event->getHomeScore() ? $event->getHomeScore() : 0,
                'awayTeamScore' => $event->getVisitorScore() ? $event->getVisitorScore() : 0,
                'goals' => $goals,
                "players" => $players,
                'convocations' => $convocations,
                'presences' => $presences,
                'createConvocationsUrl' => $createConvocationsUrl,
                'goalForm' => $goalForm->createView(),
                'oneConvocationForm' => $oneConvocationForm->createView(),
                'eventUrlService' => $this->eventUrlService

            ];
        } elseif ($event->getEventType() === EventTypeEnum::TRAINING) {
            $template = 'admin/eventTraining.html.twig';
            $variables = [
                'event' => $event,
                'current_url' => $request->getUri(),
                'statusOptions' => EventStatusEnum::cases(),
                'presenceOptions' => PresenceStatusEnum::cases(),
                'convocations' => $convocations,
                'presences' => $presences,
                'createPresencesUrl' => $createPresencesUrl,
            
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

    private function handleGoalForm(Request $request, Event $event): ?FormInterface
    {
        $goal = new Goal();
        $form = $this->createForm(GoalType::class, $goal, [
            'players' => $event->getTeam()->getPlayers(),
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $minuteGoal = $goal->getMinuteGoal();
            $player = $form->get('selectedPlayer')->getData();
            $playerId = $player ? $player->getId() : null;

            if ($playerId) {
                $player = $this->playerRepository->find($playerId);
                $goal->setPlayer($player);
                $isVisitor = false;
            } else {
                $visitorTeam = $event->getVisitorTeam();
                $ghostPlayer = $this->playerRepository->findOneBy([
                    'playsInTeam' => $visitorTeam,
                    'user' => null,
                ]);

                if (!$ghostPlayer) {
                    $ghostPlayer = new VisitorPlayer();
                    $ghostPlayer->generateUuid();
                    $ghostPlayer->setVisitorTeam($visitorTeam);
                    $this->em->persist($ghostPlayer);
                    $this->em->flush();
                }

                $goal->setVisitorPlayer($ghostPlayer);
                $playerId = $ghostPlayer->getId();
                $isVisitor = true;
            }

            $goal->setEvent($event);
            $this->goalService->createGoal($event->getId(), $playerId, $minuteGoal, $isVisitor);
        }

        return $form;
    }

    private function handleConvocationForm(Request $request, Event $event): ?FormInterface
    {
        $allPlayers = $this->playerRepository->findAll();
        $alreadyConvocatedPlayers = $event->getConvocations()
            ->map(fn($c) => $c->getPlayer());

        $availablePlayers = array_filter($allPlayers, function($player) use ($alreadyConvocatedPlayers) {
            return !$alreadyConvocatedPlayers->contains($player);
        });

        $form = $this->createForm(OneConvocationType::class, null, [
            'availablePlayers' => $availablePlayers
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $player = $form->get('player')->getData();
            $convocation = $this->convocationService->createOneConvocation($player, $event->getId());

            if ($convocation) {
                $event->addConvocation($convocation);
                $this->addFlash('success', 'Joueuse convoquée avec succès !');
            }
        }

        return $form;
    }



    
    public function configureFields(string $pageName): iterable
    {
        return [
            DateTimeField::new('date', 'Date et heure')
                ->setFormat('dd/MM/yyyy HH:mm'),            

            BooleanField::new('isRecurring', 'Récurrent')
                ->setHelp('Cocher si l\'événement doit se répéter chaque semaine à la même heure')
                ->onlyOnForms(),

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
                ->setFormTypeOption('choice_label', 'name')
                ->hideOnIndex(),
            TextField::new('score', 'Score')
                ->onlyOnIndex()
                ->formatValue(function ($value, $entity) {
                    return $entity->isMatch() ? $entity->getScore() : '-';
                }),

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
