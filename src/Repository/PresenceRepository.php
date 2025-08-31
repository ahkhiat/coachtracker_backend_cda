<?php

namespace App\Repository;

use App\Entity\Player;
use App\Entity\Presence;
use App\Enum\EventTypeEnum;
use App\Enum\EventStatusEnum;
use App\Enum\PresenceStatusEnum;
use Composer\XdebugHandler\Status;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Presence>
 */
class PresenceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Presence::class);
    }

    public function countMatchesPresencesByPlayer(Player $player): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->innerJoin('p.event', 'e')
            ->andWhere('p.player = :playerId')
            ->andWhere('p.status IN (:statuses)')
            ->andWhere('e.eventType = :eventType')
            ->setParameter('playerId', $player->getId(), "uuid")
            ->setParameter('statuses', [
                PresenceStatusEnum::ON_TIME,
                PresenceStatusEnum::LATE
                ])
            ->setParameter('eventType', EventTypeEnum::MATCH)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTrainingSessionsPresencesByPlayer(Player $player): int
    {
        return $this->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->innerJoin('p.event', 'e')
            ->andWhere('p.player = :playerId')
            ->andWhere('p.status IN (:statuses)')
            ->andWhere('e.eventType = :eventType')
            ->setParameter('playerId', $player->getId(), "uuid")
            ->setParameter('statuses', [
                PresenceStatusEnum::ON_TIME,
                PresenceStatusEnum::LATE
                ])
            ->setParameter('eventType', EventTypeEnum::TRAINING)
            ->getQuery()
            ->getSingleScalarResult();
    }
   
}
