<?php

namespace App\Controller\Api\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/user/login', name: 'login')]
class GetLoginAction extends AbstractController
{
    public function __invoke(): Response
    {
        return $this->json('U successfully authenticated!!');
    }
}