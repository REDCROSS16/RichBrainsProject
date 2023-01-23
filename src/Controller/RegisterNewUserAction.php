<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/register', name: 'register', methods: ['POST'])]
class RegisterNewUserAction extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $hasher,
        private readonly ValidatorInterface $validator
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
                    201
                );
            }
        }

//        dd($form->getErrors());

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