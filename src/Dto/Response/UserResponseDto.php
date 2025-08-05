<?php

namespace App\Dto\Response;

class UserResponseDto
{
    public int $id;
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
