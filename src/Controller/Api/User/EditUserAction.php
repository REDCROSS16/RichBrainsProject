<?php

namespace App\Controller\Api\User;

use App\Entity\ENUM\Roles;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;


#[Route('/admin/users/{id<\d+>}', name: 'update_user', methods: ['POST'])]
class EditUserAction extends AbstractController
{
    public function __construct(
        readonly private EntityManagerInterface $entityManager,
        readonly private UserPasswordHasherInterface $hasher,
        readonly private ValidatorInterface $validator,
        readonly private Security $security
    )
    {
    }

    public function __invoke(User $user, Request $request): Response
    {

        if ($this->security->getUser() !== $user ||
            array_key_exists(Roles::ROLE_ADMIN->value, array_flip($this->security->getUser()->getRoles())) === false)
        {
            return $this->json('FORBIDDEN', Response::HTTP_FORBIDDEN);
        }

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

        $errors = $this->validator->validate($user);

        foreach ($form->getErrors() as $key => $error) {
            if (!$form->isRoot()) {
                $errors .= $error->getMessage() . '\r\n';
            }
            $errors .= $error->getMessage();
        }

        return new Response($errors, status: 424);

    }
}