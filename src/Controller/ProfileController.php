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

    #[Route('/info', name: '_edit')]
    public function editProfile(Request $request): JsonResponse {
        $newFirstName = $request->getPayload()->get('firstName');
        $newLastName = $request->getPayload()->get('lastName');

        if ($newFirstName != $this->getUser()->getFirstName()) {
            $this->getUser()->setFirstName($newFirstName);
        }

        if ($newLastName != $this->getUser()->getLastName()) {
            $this->getUser()->setLastName($newLastName);
        }

        $errors = $this->validator->validate($this->getUser());

        if (count($errors) > 0) {
            return $this->json([
                'title' => 'Validation Failed',
                'detail' => (string) $errors
            ], 422);
        }

        $this->entityManager->flush();

        return $this->json([
            'message' => 'Profile updated',
            'firstName' => $this->getUser()->getFirstName(),
            'lastName' => $this->getUser()->getLastName(),
        ]);
    }

    #[Route('/credentials', name: '_credentials')]
    public function editCredentials(
        Request $request,
        UserPasswordHasherInterface $userPasswordHasher,
        EmailVerifier $emailVerifier
    ): JsonResponse {
        $newPassword = $request->getPayload()->get('password');
        $newEmail = $request->getPayload()->get('email');
        $oldEmail = $this->getUser()->getUserIdentifier();

        if ($newPassword && $newPassword != '') {
            if (!preg_match('/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d\s:])([^\s]){8,}$/', $newPassword) || strlen($newPassword) > 4096) {
                return $this->json([
                    'title' => 'Validation Failed',
                    'detail' => 'The new password does not meet the requirements'
                ], 422);
            }

            $this->getUser()->setPassword(
                $userPasswordHasher->hashPassword(
                    $this->getUser(),
                    $newPassword
                )
            );
        }

        if ($newEmail && $newEmail != '') {
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
        }

        $this->entityManager->flush();

        if ($this->getUser()->getEmail() != $oldEmail) {
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
            'message' => 'Credentials updated',
            'email' => $this->getUser()->getEmail()
        ]);
    }
}
