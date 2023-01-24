<?php

namespace App\Controller\Api\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use OpenApi\Annotations as OA;

/**
 * @OA\Get(
 *     tags={"Simple login"},
 *     summary="Check JWT token",
 *     @OA\Response(response="200", description="Check for login"),
 * )
 */
#[Route('/user', name: 'login', methods: ['GET'])]
class GetLoginAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->json('U successfully authenticated!!');
    }
}