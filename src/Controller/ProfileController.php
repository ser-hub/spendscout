<?php

namespace App\Controller;

use App\Model\UserDTO;
use App\Security\EmailVerifier;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/profile', name: 'app_profile')]
class ProfileController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ValidatorInterface $validator,
        private Security $security,
    ) {
    }

    #[Route('', name: '_index')]
    #[Template('profile/index.html.twig')]
    public function index(): array
    {
        return [];
    }

    #[Route('/edit', name: '_edit')]
    public function editProfile(
        #[MapRequestPayload(acceptFormat: 'json')] UserDTO $userDto,
        EmailVerifier $emailVerifier,
    ): JsonResponse {
        $newFirstName = $userDto->firstName;
        $newLastName = $userDto->lastName;
        $newEmail = $userDto->email;
        $oldEmail = $this->getUser()->getUserIdentifier();

        if ($newFirstName != $this->getUser()->getFirstName()) {
            $this->getUser()->setFirstName($newFirstName);
        }

        if ($newLastName != $this->getUser()->getLastName()) {
            $this->getUser()->setLastName($newLastName);
        }

        if ($newEmail != $oldEmail) {
            $this->getUser()->setEmail($newEmail);
        }

        $errors = $this->validator->validate($this->getUser());

        if (count($errors) > 0) {
            $this->getUser()->setEmail($oldEmail);

            return $this->json([
                'title' => 'Validation Failed',
                'detail' => (string) $errors
            ], 422);
        }

        $this->entityManager->flush();

        if ($newEmail != $oldEmail) {
            $this->security->login($this->getUser());

            $emailVerifier->sendEmailConfirmation(
                'app_verify_email',
                $this->getUser(),
                (new TemplatedEmail())
                    ->from(new Address('spendscount.noreply@spendscout.com', 'SpendScout Mail Bot'))
                    ->to($this->getUser()->getEmail())
                    ->subject('Please Confirm your Email')
                    ->htmlTemplate('registration/confirmation_email.html.twig')
            );
        }

        return $this->json([
            'firstName' => $this->getUser()->getFirstName(),
            'lastName' => $this->getUser()->getLastName(),
            'email' => $this->getUser()->getUserIdentifier(),
        ]);
    }

    #[Route('/password', name: '_password')]
    public function editPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        $newPassword = $request->getPayload()->get('password');

        if (!preg_match('/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d\s:])([^\s]){8,}$/', $newPassword) || strlen($newPassword) > 4096) {
            return $this->json([
                'title' => 'Validation Failed',
                'detail' => 'Password does not meet the requirements'
            ], 422);
        }

        $this->getUser()->setPassword(
            $userPasswordHasher->hashPassword(
                $this->getUser(),
                $newPassword
            )
        );

        $this->entityManager->flush();

        return $this->json('Your password has been updated successfully');
    }
}
