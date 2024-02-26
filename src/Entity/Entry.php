<?php

namespace App\Entity;

use App\Repository\EntryRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: EntryRepository::class)]
class Entry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Name can not be longer than 255 characters.'
    )]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?bool $isExpense = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Tag $tag = null;

    #[Assert\NotBlank]
    #[ORM\Column]
    private ?float $amount = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'entries')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Currency $currency = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    // properties used for avoiding CIRCULAR_REFERENCE error
    private $userId = null;
    private $tagId = null;
    private $currencyId = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function isIsExpense(): ?bool
    {
        return $this->isExpense;
    }

    public function setIsExpense(bool $isExpense): static
    {
        $this->isExpense = $isExpense;

        return $this;
    }

    public function getTag(): ?Tag
    {
        return $this->tag;
    }

    public function setTag(?Tag $tag): static
    {
        $this->tag = $tag;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): static
    {
        $this->amount = round($amount, 2);

        return $this;
    }

    public function getCurrency(): ?Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static
    {
        $this->date = $date;

        return $this;
    }

    public function circularReferenceSafe(): Entry
    {
        $this->userId = $this->user->getId();
        $this->tagId = $this->tag->getName();
        $this->currencyId = $this->currency->getCode();

        return $this;
    }
    public function getUserId()
    {
        return $this->userId;
    }

    public function getTagId()
    {
        return $this->tagId;
    }

    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    public function setTagId($tagId)
    {
        $this->tagId = $tagId;

        return $this;
    }

    public function setCurrencyId($currencyId)
    {
        $this->currencyId = $currencyId;

        return $this;
    }
}
