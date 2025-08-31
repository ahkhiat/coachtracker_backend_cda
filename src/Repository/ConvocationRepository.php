<?php

namespace App\Repository;

use App\Entity\Event;
use App\Entity\Player;
use App\Entity\Convocation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Convocation>
 */
class ConvocationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Convocation::class);
    }

        public function countConvocatedMatchesByPlayer(Player $player): int
        {
            return $this->createQueryBuilder('c')
                ->select('COUNT(c.id)')
                ->andWhere('c.player = :playerId')
                ->setParameter('playerId', $player->getId(), "uuid")
                ->getQuery()
                ->getSingleScalarResult();
        }
    
}
