<?php

namespace App\Controller\Api\User;

use App\Component\FormErrorsChecker;
use App\Entity\ENUM\Roles;
use App\Entity\User;
use App\Form\UserFormType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use OpenApi\Annotations as OA;

// TODO: method 'PATCH' don't work with request
/**
 * @OA\Post(
 *     tags={"Update yourself"},
 *     summary="Update yourself",
 *     @OA\Response(response="200", description="User successfully update"),
 *     @OA\Response(response="404", description="User not update, exist errors"),
 * )
 */
#[Route('/user', name: 'edit_yourself', methods: ['POST'])]
class EditYourselfAction extends AbstractController
{
    public function __construct(
        readonly private Security $security,
        readonly private UserPasswordHasherInterface $hasher,
        readonly private FormErrorsChecker $checker,
        readonly private EntityManagerInterface $entityManager
    )
    {
    }

    public function __invoke(Request $request)
    {
        /** @var User $user */
        $user = $this->security->getUser();

        $form = $this->createForm(UserFormType::class, $user);
        $form->submit($request->request->all());

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setPassword($this->hasher->hashPassword($user, $request->request->get('password')));
            $this->entityManager->persist($user);
            $this->entityManager->flush();

            return $this->json('Successfully update');
        }

        $errors = $this->checker->check($user, $form);

        return $this->json($errors, Response::HTTP_NOT_FOUND);
    }
}