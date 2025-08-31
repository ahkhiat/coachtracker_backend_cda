<?php

namespace App\Service;

use App\Entity\Event;
use App\Entity\Player;
use App\Entity\Presence;
use App\Entity\Convocation;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use Symfony\Component\Uid\Uuid;
use App\Enum\PresenceStatusEnum;
use App\Enum\ConvocationStatusEnum;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ConvocationService
{
    public function __construct(private EntityManagerInterface $em) {}

    public function createConvocationsForEvent(Event $event): int
    {
        $count = 0;
        $team = $event->getTeam();
        if (!$team) {
            return $count;
        }

        foreach ($team->getPlayers() as $player) {
            $existing = $this->em->getRepository(Convocation::class)->findOneBy([
                'player' => $player,
                'event' => $event,
            ]);

            if ($existing) continue;

            $convocation = new Convocation();
            $convocation->generateUuid();
            $convocation->setPlayer($player);
            $convocation->setEvent($event);
            $convocation->setStatus(ConvocationStatusEnum::PENDING);

            $presence = new Presence();
            $presence->generateUuid();
            $presence->setPlayer($player);
            $presence->setEvent($event);
            $presence->setStatus(PresenceStatusEnum::PENDING);

            $this->em->persist($convocation);
            $this->em->persist($presence);
            
            $count++;
        }

        $this->em->flush();

        return $count;
    }

    public function changeConvocationStatus(Convocation $convocation, ConvocationStatusEnum $status): Convocation
    {
        $convocation->setStatus($status);
        $this->em->persist($convocation);
        $this->em->flush();

        return $convocation;
    }

    public function createOneConvocation(Player $player, string $eventId): ?Convocation
    {
        $event = $this->em->getRepository(Event::class)->find($eventId);

        if (!$event) {
            throw new NotFoundHttpException('Événement introuvable');
        }
        
        $existing = $this->em->getRepository(Convocation::class)->findOneBy([
            'player' => $player,
            'event' => $event
        ]);

        if($existing) {
            return null;
        }

        $convocation = new Convocation();
        $convocation->generateUuid();
        $convocation->setPlayer($player);
        $convocation->setEvent($event);
        $convocation->setStatus(ConvocationStatusEnum::PENDING);

        $presence = new Presence();
        $presence->generateUuid();
        $presence->setPlayer($player);
        $presence->setEvent($event);
        $presence->setStatus(PresenceStatusEnum::PENDING);

        $this->em->persist($convocation);
        $this->em->persist($presence);
        $this->em->flush();

        return $convocation;
    }

    public function deleteConvocation(Convocation $convocation): bool
    {
        if (!$convocation) {
            return false;
        }

        $event = $convocation->getEvent();
        $event->removeConvocation($convocation);
        $this->em->remove($convocation);
        $this->em->flush();

        return true;
    }

}
