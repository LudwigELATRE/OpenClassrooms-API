<?php

namespace App\Entity;

use App\Repository\AuthorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: AuthorRepository::class)]
class Author
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $firstName = null;

    #[ORM\Column(length: 255)]
    private ?string $lastName = null;

    #[ORM\ManyToOne(targetEntity: self::class, inversedBy: 'Book')]
    private ?self $author = null;

    #[ORM\OneToMany(mappedBy: 'author', targetEntity: self::class)]
    private Collection $Book;

    public function __construct()
    {
        $this->Book = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getAuthor(): ?self
    {
        return $this->author;
    }

    public function setAuthor(?self $author): self
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return Collection<int, self>
     */
    public function getBook(): Collection
    {
        return $this->Book;
    }

    public function addBook(self $book): self
    {
        if (!$this->Book->contains($book)) {
            $this->Book->add($book);
            $book->setAuthor($this);
        }

        return $this;
    }

    public function removeBook(self $book): self
    {
        if ($this->Book->removeElement($book)) {
            // set the owning side to null (unless already changed)
            if ($book->getAuthor() === $this) {
                $book->setAuthor(null);
            }
        }

        return $this;
    }
}
