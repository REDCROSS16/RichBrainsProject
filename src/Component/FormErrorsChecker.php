<?php

namespace App\Component;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class FormErrorsChecker
{
    public function __construct(
        readonly private ValidatorInterface $validator
    )
    {
    }

    public function check(User $user, FormInterface $form): string
    {
        $errors = $this->validator->validate($user);

        foreach ($form->getErrors() as $key => $error) {
            if (!$form->isRoot()) {
                $errors .= $error->getMessage() . '\r\n';
            }
            $errors .= $error->getMessage();
        }

        return $errors;
    }
}