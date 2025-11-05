<?php

namespace App\Controllers;

class Home extends BaseController
{
    public function index(): string
    {
        return view('home');
    }

    public function dbTest()
    {
        $db = \Config\Database::connect();
        if ($db->connID) {
            echo "Database connection successful!";
        } else {
            echo "Database connection failed!";
        }
    }
}
