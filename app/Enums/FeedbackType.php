<?php

namespace App\Enums;

enum FeedbackType: string
{
    case Waiter = 'waiter';
    case Food = 'food';
    case Restaurant = 'restaurant';
}
