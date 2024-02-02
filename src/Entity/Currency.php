<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\Regex(
        paatern: '/^[A-Z]{0,3}$/',
        message: 'Invalid currency code. Use 3 uppercase letters.'
    )]
    #[ORM\Column(length: 3)]
    private ?string $code = null;

    #[ORM\OneToMany(mappedBy: 'currency', targetEntity: Entry::class)]
    private Collection $entries;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return Collection<int, Entry>
     */
    public function getEntries(): Collection
    {
        return $this->entries;
    }

    public function addEntry(Entry $entry): static
    {
        if (!$this->entries->contains($entry)) {
            $this->entries->add($entry);
            $entry->setCurrency($this);
        }

        return $this;
    }

    public function circularReferenceSafe(): Currency
    {
        $this->entries->map(function ($entry) { return $entry->circularReferenceSafe(); });

        return $this;
    }
}
