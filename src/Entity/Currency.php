<?php

namespace App\Entity;

use App\Repository\CurrencyRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CurrencyRepository::class)]
class Currency
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 3)]
    private ?string $code = null;

    #[ORM\OneToOne(mappedBy: 'currency', cascade: ['persist', 'remove'])]
    private ?Entry $entry = null;

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

    public function getDate(): ?Entry
    {
        return $this->entry;
    }

    public function setEntry(Entry $entry): static
    {
        // set the owning side of the relation if necessary
        if ($entry->getCurrency() !== $this) {
            $entry->setCurrency($this);
        }

        $this->entry = $entry;

        return $this;
    }
}
