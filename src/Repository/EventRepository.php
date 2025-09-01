<?php

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Event;
use App\Entity\Player;
use App\Enum\EventStatusEnum;
use App\Enum\EventTypeEnum;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Event>
 */
class EventRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Event::class);
    }

    public function countMatchesByTeam(Team $team): int
    {

        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.team = :teamId')
            ->andWhere('e.eventType = :type')
            ->andWhere('e.status = :status')
            ->setParameter('type', EventTypeEnum::MATCH)
            ->setParameter('teamId', $team->getId(), "uuid")
            ->setParameter('status', EventStatusEnum::FINISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countTrainingSessionsByTeam(Team $team): int
    {
        return $this->createQueryBuilder('e')
            ->select('COUNT(e.id)')
            ->andWhere('e.team = :teamId')
            ->andWhere('e.eventType = :type')
            ->andWhere('e.status = :status')
            ->setParameter('teamId', $team->getId(), "uuid")
            ->setParameter('type', EventTypeEnum::TRAINING)
            ->setParameter('status', EventStatusEnum::FINISHED)
            ->getQuery()
            ->getSingleScalarResult();
    }

    

    //    /**
    //     * @return Event[] Returns an array of Event objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Event
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
