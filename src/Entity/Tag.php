<?php

namespace App\Entity;

use App\Repository\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Ignore;

#[ORM\Entity(repositoryClass: TagRepository::class)]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank]
    #[Assert\Length(
        max: 255,
        maxMessage: 'Name contains too many characters',
    )]
    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[Ignore]
    #[ORM\ManyToOne(inversedBy: 'tags')]
    private ?User $user = null;

    #[ORM\OneToMany(mappedBy: 'tag', targetEntity: Entry::class)]
    private Collection $entries;

    // properties used for avoiding CIRCULAR_REFERENCE error
    private $userId = null;

    public function __construct()
    {
        $this->entries = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
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
            $entry->setTag($this);
        }

        return $this;
    }

    public function removeEntry(Entry $entry): static
    {
        if ($this->entries->removeElement($entry)) {
            // set the owning side to null (unless already changed)
            if ($entry->getTag() === $this) {
                $entry->setTag(null);
            }
        }

        return $this;
    }

    public function clearEntries(): static
    {
        $this->entries->clear();

        return $this;
    }

    public function circularReferenceSafe(): Tag 
    {
        $this->userId = $this->user->getId();
        $this->entries->map(function ($entry) {return $entry->circularReferenceSafe(); });

        return $this;
    }

    public function getUserId()
    {
        return $this->userId;
    }
}
