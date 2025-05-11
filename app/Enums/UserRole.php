<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Trainer = 'trainer';
    case Student = 'student';
    case Employee = 'Employee';

}