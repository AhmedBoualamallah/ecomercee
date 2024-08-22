<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\ContactRepository;

#[ORM\Entity(repositoryClass: ContactRepository::class)]
class Contact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $nom = null;

    #[ORM\Column(type: 'string', length: 255)]
    private $email;

    #[ORM\Column(length: 255)]
    private ?string $message = null;

    #[ORM\Column(type: 'datetime')]
    private $createdAt;

  
    
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }


    public function setNom(string $nom): static
    {
        $this->nom = $nom;

        return $this;
    }
    public function getEmail(): ?string
    {
        return $this->email;
    }


    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }
    

    public function getMessage(): ?float
    {
        return $this->message;
    }

    public function setMessage(float $message): static
    {
        $this->message = $message;

        return $this;
    }

    

    
    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Constructor to set the createdAt field automatically.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }
}
