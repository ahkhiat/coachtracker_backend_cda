<?php

namespace App\Service;

use App\Entity\Goal;
use App\Entity\Event;
use App\Entity\Player;
use App\Entity\VisitorPlayer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;

class GoalService
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Crée un but pour un événement et un joueur (local ou visiteur).
     *
     * @param string $eventId
     * @param string $playerId 
     * @param string $visitorPlayerId
     * @param int $minuteGoal
     *
     * @return Goal
     *
     * @throws NotFoundHttpException si l'événement ou le joueur n'existe pas
     * @throws BadRequestException si des données invalides
     */

    public function createGoal(
        string $eventId, 
        string $playerId, 
        int $minuteGoal, 
        bool $isVisitor = false
    ): Goal {

        if ($minuteGoal < 0) {
            throw new BadRequestException('Minute invalide');
        }

        $event = $this->em->getRepository(Event::class)->find($eventId);

        if (!$event) {
            throw new NotFoundHttpException('Événement introuvable');
        }

        $goal = new Goal();
        $goal->setEvent($event);
        $goal->setMinuteGoal($minuteGoal);

        if ($isVisitor) {
            $visitorPlayer = $this->em->getRepository(VisitorPlayer::class)->find($playerId);
            if (!$visitorPlayer) {
                throw new NotFoundHttpException('Joueur visiteur introuvable');
            }
            $goal->setVisitorPlayer($visitorPlayer);
        } else {
            $player = $this->em->getRepository(Player::class)->find($playerId);
            if (!$player) {
                throw new NotFoundHttpException('Joueur introuvable');
            }
            $goal->setPlayer($player);
        }
        $this->em->persist($goal);
        $this->em->flush();

        return $goal;
    }
    
    public function deleteGoal(int $goalId): void
    {
        $goal = $this->em->getRepository(Goal::class)->find($goalId);

        if (!$goal) {
            throw new NotFoundHttpException('But introuvable');
        }

        $this->em->remove($goal);
        $this->em->flush();
    }
}
    