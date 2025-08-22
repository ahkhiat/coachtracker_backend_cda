<?php

namespace App\Dto\Response;

use App\Entity\User;
use OpenApi\Attributes as OA;
use Proxies\__CG__\App\Entity\Team;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Serializer\Annotation\IgnoreWhenEmpty;

class UserPublicProfileDto
{
    public Uuid $id;
    public string $email;
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $birthdate = null;
    public ?string $phone = null;
    public array $roles = [];
    public ?array $playsInTeam = null;
    public ?array $isCoachOf = null;
    public array $userIsParentOf = [];
    public array $stats = [];

    public function __construct(User $user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->firstname = $user->getFirstname();
        $this->lastname = $user->getLastname();
        $this->birthdate = $user->getBirthdate()?->format('Y-m-d');
        $this->phone = $user->getPhone();
        $this->roles = $user->getRoles();

        $teamPlayer = $user->getPlayer()?->getPlaysInTeam();
        $this->playsInTeam = $teamPlayer ? [
            'id' => $teamPlayer->getId(),
            'name' => $teamPlayer->getName()
        ] : null;
        ;

        $teamCoach = $user->getCoach()?->getIsCoachOf();
        $this->isCoachOf = $teamCoach ? [
            'id' => $teamCoach->getId(),
            'name' => $teamCoach->getName()
        ] : null;
        ;
        $this->userIsParentOf = $user->getUserIsParentOfs()?->map(function($child) {
            $team = $child->getChild()->getPlayer()?->getPlaysInTeam();

            return [
                'id'        => $child->getChild()->getId(),
                'firstname' => $child->getChild()->getFirstname(),
                'lastname'  => $child->getChild()->getLastname(),
                'plays_in'  => $team ? [
                    'id'   => $team->getId(),
                    'name' => $team->getName()
                ] : null
            ];
        })->toArray() ?? [];

    }

}

