<?php
namespace App\Classes;

use PDO;

abstract class Model
{
    protected PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}
