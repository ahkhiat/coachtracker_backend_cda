<?php

namespace App\Repository;

use App\Entity\Goal;
use App\Entity\Event;
use App\Entity\Player;
use App\Entity\Presence;
use App\Entity\Convocation;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<Player>
 */
class PlayerRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    



}
