<?php

namespace App\Entity;

use App\Repository\DocumentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentRepository::class)]
class Document
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotBlank(message: 'Type document cannot be empty.')]
    private ?TypeDocument $type = null;

  

    #[ORM\Column]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?bool $status = null;

    #[ORM\ManyToOne(inversedBy: 'documents')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $pdfPath = null;

    #[ORM\Column(length: 255)]
    #[Gedmo\Slug(fields:["documentNumber"])]
    private ?string $slug = null;

    /**
     * @var Collection<int, DocumentItem>
     */
    #[ORM\OneToMany(targetEntity: DocumentItem::class, mappedBy: 'document')]
    private Collection $documentItems;

 

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $documentNumber = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    #[ORM\Column]
    private ?float $total = null;

    public function __construct()
    {
        $this->documentItems = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getType(): ?TypeDocument
    {
        return $this->type;
    }

    public function setType(?TypeDocument $type): static
    {
        $this->type = $type;

        return $this;
    }

 

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function isStatus(): ?bool
    {
        return $this->status;
    }

    public function setStatus(bool $status): static
    {
        $this->status = $status;

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

    public function getPdfPath(): ?string
    {
        return $this->pdfPath;
    }

    public function setPdfPath(?string $pdfPath): static
    {
        $this->pdfPath = $pdfPath;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): static
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * @return Collection<int, DocumentItem>
     */
    public function getDocumentItems(): Collection
    {
        return $this->documentItems;
    }

    public function addDocumentItem(DocumentItem $documentItem): static
    {
        if (!$this->documentItems->contains($documentItem)) {
            $this->documentItems->add($documentItem);
            $documentItem->setDocument($this);
        }

        return $this;
    }

    public function removeDocumentItem(DocumentItem $documentItem): static
    {
        if ($this->documentItems->removeElement($documentItem)) {
            // set the owning side to null (unless already changed)
            if ($documentItem->getDocument() === $this) {
                $documentItem->setDocument(null);
            }
        }

        return $this;
    }

   

    public function getDocumentNumber(): ?string
    {
        return $this->documentNumber;
    }

    public function setDocumentNumber(?string $documentNumber): static
    {
        $this->documentNumber = $documentNumber;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

        return $this;
    }

    public function getTotal(): ?float
    {
        return $this->total;
    }

    public function setTotal(float $total): static
    {
        $this->total = $total;

        return $this;
    }
}
