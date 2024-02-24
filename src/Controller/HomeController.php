<?php

namespace App\Controller;

use App\Entity\Entry;
use App\Entity\Tag;
use App\Entity\Currency;
use App\Entity\User;
use App\Utilities\ErrorMessages;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;

#[Route('', name: 'app_home')]
class HomeController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ErrorMessages $errorMessages
    ) {
    }

    #[Route('', name: '_index')]
    #[Template('home/index.html.twig')]
    public function index()
    {
        return [
            'entries' => $this->entityManager->getRepository(Entry::class)->findAll(),
            'user_tags' => $this->getUser()->getTags(),
            'default_tags' => $this->entityManager->getRepository(Tag::class)->findDefaultTags(),
            'currencies' => $this->entityManager->getRepository(Currency::class)->findAll(),
        ];
    }

    #[Route('/entries', name: '_get_all_user_entries', methods: ['GET'])]
    public function getAllUserEntries(): JsonResponse
    {
        return $this->json($this->getUser()->getEntriesCircularReferenceSafe());
    }

    #[Route('/tags', name: '_get_all_user_tags', methods: ['GET'])]
    public function getAllUserTags(): JsonResponse
    {
        $tags = $this->entityManager->getRepository(Tag::class)->findTagNamesOfUser($this->getUser()->getId());
        return $this->json($tags);
    }

    #[Route('/entries/{id<\d+>}', name: '_get_entry', methods: ['GET'])]
    public function getEntry(?Entry $entry): JsonResponse
    {
        if (!$entry) {
            return $this->json($this->errorMessages::OBJECT_NOT_FOUND_MESSAGE, 404);
        } 
        if ($entry->getUser()->getId() != $this->getUser()->getId()) {
            return $this->json($this->errorMessages::ACCESS_TO_RESOURCE_DENIED_MESSAGE, 403);
        }

        return $this->json($entry->circularReferenceSafe());
    }

    #[Route('/entries', name: '_add_entry', methods: ['POST'], format: 'json')]
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

    #[Route('/entries/{id<\d+>}', name: '_update_entry', methods: ['PUT'], format: 'json')]
    public function updateEntry(?Entry $entry, #[MapRequestPayload(acceptFormat: 'json')] Entry $entryDTO): JsonResponse
    {
        if (!$entry) {
            return $this->json($this->errorMessages::OBJECT_NOT_FOUND_MESSAGE, 404);
        }
        if ($entry->getUser()->getId() != $this->getUser()->getId()) {
            return $this->json($this->errorMessages::ACCESS_TO_RESOURCE_DENIED_MESSAGE, 403);
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

    #[Route('/entries/{id<\d+>}', name: '_delete_entry', methods: ['DELETE'])]
    public function deleteEntry(?Entry $entry): JsonResponse
    {
        if (!$entry) {
            return $this->json($this->errorMessages::OBJECT_NOT_FOUND_MESSAGE, 404);
        }
        if ($entry->getUser()->getId() != $this->getUser()->getId()) {
            return $this->json($this->errorMessages::ACCESS_TO_RESOURCE_DENIED_MESSAGE, 403);
        }

        $this->entityManager->remove($entry);
        $this->entityManager->flush();

        return $this->json('Entity deleted successfully.');
    }
}
