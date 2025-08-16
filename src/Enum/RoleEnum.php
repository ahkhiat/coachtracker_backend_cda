<?php

namespace App\Enum;

enum RoleEnum: string
{
    case ROLE_ADMIN = 'ROLE_ADMIN';
    case ROLE_DIRECTOR = 'ROLE_DIRECTOR';
    case ROLE_SECRETARY = 'ROLE_SECRETARY';
    case ROLE_COACH = 'ROLE_COACH';
    case ROLE_PLAYER = 'ROLE_PLAYER';
    case ROLE_PARENT = 'ROLE_PARENT';
    case ROLE_USER = 'ROLE_USER';
}