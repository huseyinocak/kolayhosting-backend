<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case EDITOR = 'editor';
    case AUTHOR = 'author';
    case CONTRIBUTORS = 'contributors';
    case MODERATOR = 'moderator';
    case MEMBER = 'member';
    case SUBSCRIBER = 'subscriber';
    case USER = 'user';
}
