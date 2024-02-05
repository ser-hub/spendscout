<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Entity\User;
use App\Entity\Currency;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('/api', name: 'api_')]
class APIController extends AbstractController
{
    private const OBJECT_NOT_FOUND_MESSAGE = 'Object not found.';

    public function __construct(
        private EntityManagerInterface $entityManager,
    ) {
    }

    #[Route('/users/{id<\d+>}', name: 'get_all_user_data', methods: ['GET'])]
    public function getAllUserData(?User $user): JsonResponse
    {
        if (!$user) {
            return $this->json(self::OBJECT_NOT_FOUND_MESSAGE, 404);
        }
        
        return $this->json($user);
    }

    #[Route('/entries', name: 'get_all_user_entries', methods: ['GET'])]
    public function getAllUserEntries(): JsonResponse
    {
        return $this->json($this->getUser()->getEntriesCircularReferenceSafe());
    }

    #[Route('/entries', name: 'add_entry', methods: ['POST'], format: 'json')]
    public function addEntry(#[MapRequestPayload(acceptFormat: 'json')] Entry $entry): JsonResponse
    {
        $currency = $this->entityManager->getRepository(Currency::class)->find($entry->getCurrencyId());
        $tag = $this->entityManager->getRepository(Tag::class)->find($entry->getTagId());

        $entry->setUser($this->getUser());
        $entry->setCurrency($currency);
        $entry->setTag($tag);
        $this->entityManager->persist($entry);
        $this->entityManager->flush();

        return $this->json($entry->circularReferenceSafe());
    }

    #[Route('/entries/{id<\d+>}', name: 'update_entry', methods: ['PUT'], format: 'json')]
    public function updateEntry(?Entry $entry, #[MapRequestPayload(acceptFormat: 'json')] Entry $entryDTO): JsonResponse
    {
        if (!$entry) {
            return $this->json(self::OBJECT_NOT_FOUND_MESSAGE, 404);
        }

        // get the objects for the specific IDs
        $currency = $this->entityManager->getRepository(Currency::class)->find($entryDTO->getCurrencyId());
        $tag = $this->entityManager->getRepository(Tag::class)->find($entryDTO->getTagId());

        $entry->setName($entryDTO->getName());
        $entry->setIsExpense($entryDTO->isIsExpense());
        $entry->setAmount($entryDTO->getAmount());
        $entry->setDate($entryDTO->getDate());
        $entry->setCurrency($currency);
        $entry->setTag($tag);
   
        $this->entityManager->flush();

        return $this->json($entry->circularReferenceSafe());
    }

    #[Route('/entries/{id<\d+>}', name: 'delete_entry', methods: ['DELETE'])]
    public function deleteEntry($entry): JsonResponse
    {
        if (!$entry) {
            return $this->json(self::OBJECT_NOT_FOUND_MESSAGE, 404);
        }

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        return $this->json('Entity deleted successfully.');
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
            return $this->json(self::OBJECT_NOT_FOUND_MESSAGE, 404);
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
            return $this->json(self::OBJECT_NOT_FOUND_MESSAGE, 404);
        }

        $tag->setName($tagDTO->getName());
        $this->entityManager->flush();

        return $this->json($tag->circularReferenceSafe());
    }

    #[Route('/tags/{id<\d+>}', name: 'delete_tag', methods: ['DELETE'])]
    public function deleteTag(?Tag $tag): JsonResponse
    {
        if (!$tag) {
            return $this->json(self::OBJECT_NOT_FOUND_MESSAGE, 404);
        }

        $this->entityManager->remove($tag);
        $this->entityManager->flush();

        return $this->json('Entity deleted successfully.');
    }

    #[Route('/currencies', name: 'get_all_currencies', methods: ['GET'])]
    public function getAllCurrencies(): JsonResponse
    {
        $allCurrencies = $this->entityManager->getRepository(Currency::class)->findAll();

        foreach ($allCurrencies as &$currency) {
            $currency = $currency->circularReferenceSafe();
        }

        return $this->json($allCurrencies);
    }
}
