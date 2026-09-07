<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class HomeController extends Controller
{
    public function index(): void
    {
        if (Auth::check()) {
            $this->redirect(url('/dashboard'));
        }

        $this->render('home/index', [
            'title'            => 'PRISMA — QR Code, Encurtador de Link e Hub Digital grátis',
            'meta_description' => 'Gere QR Code na hora, sem cadastro. Encurte links, monte seu Hub Digital e use ferramentas gratuitas — tudo em um só lugar.',
        ], 'public');
    }
}
