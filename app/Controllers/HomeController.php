<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Models\Map;
use App\Models\GameMode;

class HomeController extends Controller
{
    public function index(): void
    {
        $maps = (new Map())->getAll();
        $modes = (new GameMode())->getAll();

        $this->view('home', [
            'maps' => $maps,
            'modes' => $modes,
        ]);
    }
}