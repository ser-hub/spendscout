<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin', name: 'app_admin')]
class AdminController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ){
    }

    #[Route('', name: '_index')]
    #[Template('admin/index.html.twig')]
    public function index(): array
    {
        $someUsers = $this->entityManager->getRepository(User::class)->findBy([], null, 15);
        $someUsers = new ArrayCollection($someUsers);
        $someUsers->removeElement($this->getUser());

        return [
            'users' => $someUsers,
        ];
    }

    #[Route('/entries/{id<\d+>}', name: '_get_user_entries', methods: ['GET'])]
    public function getUserEntries(int $id): JsonResponse
    {
        $entries = $this->entityManager->getRepository(Entry::class)->findBy(['user' => $id]);
        $entries = array_map(function ($entry) { return $entry->circularReferenceSafe(); }, $entries);

        return $this->json($this->entityManager->getRepository(Entry::class)->findBy(['user' => $id]));
    }

    #[Route('/search', name: '_search_users', methods: ['GET'])]
    public function searchUsers(#[MapQueryParameter] string $keyword = null): JsonResponse
    {
        if ($keyword == '') {
            return $this->json('Invalid request', 422);
        }

        $searchResults = $this->entityManager->getRepository(User::class)->searchUserDetails($keyword);
        
        foreach ($searchResults as $key => $user) {
            if ($user['email'] == $this->getUser()->getEmail()) {
                unset($searchResults[$key]);
            }
        }

        return $this->json($searchResults);
    }

    #[Route('/tags/{id<\d+>}', name: '_get_all_user_tags', methods: ['GET'])]
    public function getAllUserTags(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        $tags = $this->entityManager->getRepository(Tag::class)->findTagNamesOfUser($user->getId());
        return $this->json($tags);
    }
}
