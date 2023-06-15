<?php

declare(strict_types=1);

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class HomePageController
 * @package App\Controller
 */
class HomePageController extends AbstractController
{
    #[Route(path: '/', name: 'healt_check', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return new JsonResponse(
            [
                'code' => 200,
                'message' => 'Running'
            ],
            JsonResponse::HTTP_OK
        );
    }
}
