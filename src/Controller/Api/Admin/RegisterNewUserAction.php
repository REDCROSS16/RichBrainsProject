<?php

namespace App\Controller\Api\Admin;

use App\Component\FormErrorsChecker;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Post(
 *     tags={"Register new user"},
 *     summary="Create and register new user",
 *     @OA\Response(response="201", description="User successfully created"),
 *     @OA\Response(response="404", description="User not created, exist errors"),
 * )
 */
#[Route('/admin/users', name: 'create_user', methods: ['POST'])]
class RegisterNewUserAction extends AbstractController
{
    public function __construct(
        readonly private EntityManagerInterface $entityManager,
        readonly private UserPasswordHasherInterface $hasher,
        readonly private ValidatorInterface $validator,
        readonly private FormErrorsChecker $checker
    )
    {
    }

    public function __invoke(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserFormType::class, $user);

        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $request->request->get('password');
            $user->setPassword($this->hasher->hashPassword($user, $password));

            if (count($this->validator->validate($user)) === 0) {
                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return new Response(
                    \sprintf('User %s successfully created', $user->getFirstName()),
                    Response::HTTP_CREATED
                );
            }
        }

        $errors = $this->checker->check($user, $form);

        return new Response($errors, Response::HTTP_NOT_FOUND);
    }
}