<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class WelcomeController extends AbstractController
{
    #[Route(
        path: '/welcome',
        name: 'app_welcome',
        methods: ['GET'],
    )]
    public function welcome(): Response
    {
        return $this->render('welcome.html.twig', [
            'name' => 'Stockflow',
        ]);
    }
}
