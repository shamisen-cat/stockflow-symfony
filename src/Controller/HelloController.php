<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HelloController extends AbstractController
{
    #[Route(
        path: '/hello',
        name: 'app_hello',
        methods: ['GET'],
    )]
    public function hello(): Response
    {
        return new Response(
            content: 'hello, world',
            status: Response::HTTP_OK,
            headers: [
                'Content-Type' => 'text/plain; charset=utf-8',
            ],
        );
    }
}
