<?php

namespace App\Models;

use App\Models\Point;

class ShotRoute
{
    private $from = null;
    private $to = null;

    public function __construct()
    {
        $this->from = new Point();
        $this->to = new Point();
    }

    public function setFrom($col, $row)
    {
        $this->from->col = $col;
        $this->from->row = $row;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setTo($col, $row)
    {
        $this->to->col = $col;
        $this->to->row = $row;
    }

    public function getTo()
    {
        return $this->to;
    }
}
