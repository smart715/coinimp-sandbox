<?php

namespace App\Communications;

use DateTime;

class ActualCurrentTime implements CurrentTime
{
    public function getCurrentTime(): DateTime
    {
        return new DateTime(); // by default has current timestamp of the system
    }
}
