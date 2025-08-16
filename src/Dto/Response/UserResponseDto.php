<?php

namespace App\Dto\Response;

use Symfony\Component\Uid\Uuid;

class UserResponseDto
{
    public Uuid $id;
    public string $email;
    public array $roles;
    public string $firstname;
    public string $lastname;
    public string $birthdate;

    public function __construct($user)
    {
        $this->id = $user->getId();
        $this->email = $user->getEmail();
        $this->roles = $user->getRoles();
        $this->firstname = $user->getFirstname();
        $this->lastname = $user->getLastname();
        $this->birthdate = $user->getBirthdate()->format('Y-m-d');
    }
}
