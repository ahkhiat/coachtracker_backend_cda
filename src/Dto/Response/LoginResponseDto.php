<?php

namespace App\Dto\Response;

use App\Dto\Response\UserResponseDto;

class LoginResponseDto
{
   public string $accessToken;
    public UserResponseDto $user;

    public function __construct(string $accessToken, UserResponseDto $user)
    {
        $this->accessToken = $accessToken;
        $this->user = $user;
    }
}