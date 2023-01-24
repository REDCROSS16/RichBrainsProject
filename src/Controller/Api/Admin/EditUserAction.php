<?php

namespace App\Controller\Api\Admin;

use App\Component\FormErrorsChecker;
use App\Entity\ENUM\Roles;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use OpenApi\Annotations as OA;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

// TODO: method 'PATCH' doesn't work with request
/**
 * @OA\Post(
 *     tags={"Admin user editing"},
 *     summary="Update user info by ID",
 *     @OA\Response(response="200", description="User successfully updated"),
 *     @OA\Response(response="404", description="User not update, exist errors"),
 * )
 */
#[Route('/admin/users/{id<\d+>}', name: 'edit_user', methods: ['POST'])]
class EditUserAction extends AbstractController
{
    public function __construct(
        readonly private EntityManagerInterface $entityManager,
        readonly private UserPasswordHasherInterface $hasher,
        readonly private ValidatorInterface $validator,
        readonly private FormErrorsChecker $checker
    )
    {
    }

    public function __invoke(User $user, Request $request): Response
    {
        $data = $request->request->all();
        $form = $this->createForm(UserFormType::class, $user);
        $form->submit($data);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $data['password'];
            $user->setPassword($this->hasher->hashPassword($user, $password));

            $role = $data['roles'];

            if (Roles::tryFrom($role)){
                $roles = $user->getRoles();
                $roles[] = $role;
                $user->setRoles($roles);
            }

            if (count($this->validator->validate($user)) === 0) {

                $this->entityManager->persist($user);
                $this->entityManager->flush();

                return new Response(
                    \sprintf('User %s successfully updated', $user->getFirstName()),
                    Response::HTTP_OK
                );
            }
        }
        $errors = $this->checker->check($user, $form);

        return new Response($errors, status: 424);

    }
}