<?php

namespace App\Controller\Api\Admin;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
#[Route('/admin/users/{id<\d+>}', name: 'remove_user', methods: ['DELETE'])]
class RemoveExistingUserAction extends AbstractController
{
    public function __invoke(User $user, EntityManagerInterface $entityManager): Response
    {
        if ($user->getId()) {
            $entityManager->remove($user);
            $entityManager->flush();

            return $this->json('User successfully removed', Response::HTTP_OK);
        }

        return $this->json('User not found', Response::HTTP_NOT_FOUND);
    }
}