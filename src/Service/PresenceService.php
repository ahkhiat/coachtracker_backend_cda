<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Presence;
use App\Enum\PresenceStatusEnum;
use Doctrine\ORM\EntityManagerInterface;

class PresenceService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function createPresencesForEvent(Event $event): int
    {
        $count = 0;
        $team = $event->getTeam();
        if (!$team) {
            return $count;
        }

        foreach($team->getPlayers() as $player) {
            $existing = $this->em->getRepository(Presence::class)->findOneBy([
                'player' => $player,
                'event' => $event
            ]);
        

            if($existing) continue;

            $presence = new Presence();
            $presence->generateUuid();
            $presence->setPlayer($player);
            $presence->setEvent($event);
            $presence->setStatus(PresenceStatusEnum::PENDING);

            $this->em->persist($presence);
            $count++;
        }
        $this->em->flush();

        return $count;
    }
    



}