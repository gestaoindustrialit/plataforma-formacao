<?php
namespace App\Core;

abstract class Model
{
    protected Database $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }
}
