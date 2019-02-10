<?php

namespace App\Communications;

use DateTime;

interface CurrentTime
{
    public function getCurrentTime(): DateTime;
}
