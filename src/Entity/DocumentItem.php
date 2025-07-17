<?php

namespace App\Entity;

use App\Repository\DocumentItemRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: DocumentItemRepository::class)]
class DocumentItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'documentItems')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Document $document = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    private ?string $title = null;



    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\Positive]
    private ?int $quantity = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?float $unitPrice = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(100)]
    private ?int $discount = null;

    #[ORM\Column(type: Types::SMALLINT)]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(100)]
    private ?int $taxe = null;

    #[ORM\Column]
    #[Assert\NotNull]
    #[Assert\GreaterThanOrEqual(0)]
    private ?float $total = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDocument(): ?Document
    {
        return $this->document;
    }

    public function setDocument(?Document $document): static
    {
        $this->document = $document;

        return $this;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

   

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnitPrice(): ?float
    {
        return $this->unitPrice;
    }

    public function setUnitPrice(float $unitPrice): static
    {
        $this->unitPrice = $unitPrice;

        return $this;
    }

    public function getDiscount(): ?int
    {
        return $this->discount;
    }

    public function setDiscount(?int $discount): static
    {
        $this->discount = $discount;

        return $this;
    }

    public function getTaxe(): ?int
    {
        return $this->taxe;
    }

    public function setTaxe(int $taxe): static
    {
        $this->taxe = $taxe;

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

    public function calculateTotal(): void
    {
        $base = $this->unitPrice * $this->quantity;

        $discountRate = $this->discount ?? 0;
        $taxRate = $this->taxe ?? 0;

        $afterDiscount = $base * (1 - $discountRate / 100);
        $withTax = $afterDiscount * (1 + $taxRate / 100);

        $this->total = round($withTax, 2);
    }

}
