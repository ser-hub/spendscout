<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Utilities\ErrorMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/mytags', name: 'app_my_tags')]
class MyTagsController extends AbstractController
{

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: '_index')]
    #[Template('mytags/index.html.twig')]
    public function index(): array
    {
        return [
            'user_tags' => $this->getUser()->getTags(),
        ];
    }

    #[Route('/tags', name: 'get_all_user_tags', methods: ['GET'])]
    public function getAllUserTags(): JsonResponse
    {
        return $this->json($this->getUser()->getTagsCircularReferenceSafe());
    }

    #[Route('/tags/{id<\d+>}', name: 'get_tag', methods: ['GET'])]
    public function getTag(?Tag $tag): JsonResponse
    {
        if (!$tag) {
            return $this->json(ErrorMessages::OBJECT_NOT_FOUND_MESSAGE, 404);
        }
        if ($tag->getUser() && $tag->getUser()->getId() != $this->getUser()->getId()) {
            return $this->json(ErrorMessages::ACCESS_TO_RESOURCE_DENIED_MESSAGE, 403);
        }

        return $this->json($tag);
    }

    #[Route('/tags', name: 'add_tag', methods: ['POST'], format: 'json')]
    public function addTag(#[MapRequestPayload(acceptFormat: 'json')] Tag $tag): JsonResponse
    {
        $tag->setUser($this->getUser());
        $this->entityManager->persist($tag);
        $this->entityManager->flush();

        return $this->json($tag->circularReferenceSafe());
    }

    #[Route('/tags/{id<\d+>}', name: 'update_tag', methods: ['PUT'], format: 'json')]
    public function updateTag(Tag $tag, #[MapRequestPayload(acceptFormat: 'json')] Tag $tagDTO): JsonResponse
    {
        if (!$tag) {
            return $this->json(ErrorMessages::OBJECT_NOT_FOUND_MESSAGE, 404);
        }
        if ($tag->getUser() && $tag->getUser()->getId() != $this->getUser()->getId()) {
            return $this->json(ErrorMessages::ACCESS_TO_RESOURCE_DENIED_MESSAGE, 403);
        }

        $tag->setName($tagDTO->getName());
        $this->entityManager->flush();

        return $this->json($tag->circularReferenceSafe());
    }

    #[Route('/tags/{id<\d+>}', name: 'delete_tag', methods: ['DELETE'])]
    public function deleteTag(?Tag $tag): JsonResponse
    {
        if (!$tag) {
            return $this->json(ErrorMessages::OBJECT_NOT_FOUND_MESSAGE, 404);
        }
        if ($tag->getUser() && $tag->getUser()->getId() != $this->getUser()->getId()) {
            return $this->json(ErrorMessages::ACCESS_TO_RESOURCE_DENIED_MESSAGE, 403);
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return $this->json('Entity deleted successfully.');
    }
}
