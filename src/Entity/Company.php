<?php

namespace App\Entity;

use App\Repository\CompanyRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

#[ORM\Entity(repositoryClass: CompanyRepository::class)]
class Company
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
#[Assert\NotBlank(message: 'company.name.required')]
#[Assert\Length(
    max: 255,
    maxMessage: 'company.name.max_length'
)]
private ?string $name = null;

#[ORM\Column(length: 500, nullable: true)]
#[Assert\Length(
    max: 500,
    maxMessage: 'company.address.max_length'
)]
private ?string $adress = null;

#[ORM\Column(length: 255, nullable: true)]
#[Assert\Length(
    max: 20,
    maxMessage: 'company.phone.max_length'
)]
private ?string $phone = null;

#[ORM\Column(length: 255, nullable: true)]
#[Assert\Length(
    max: 50,
    maxMessage: 'company.tax_id.max_length'
)]
private ?string $TaxIdentification = null;

#[ORM\Column(length: 255, nullable: true)]
#[Assert\Length(
    max: 50,
    maxMessage: 'company.registration_number.max_length'
)]
private ?string $RegistrationNumber = null;

#[ORM\Column(type: Types::TEXT, nullable: true)]
private ?string $BankDetails = null;

#[ORM\Column(length: 255, nullable: true)]
private ?string $logo = null;

#[ORM\ManyToOne]
#[ORM\JoinColumn(nullable: false)]
private ?User $user = null;

#[ORM\Column(type: Types::TEXT, nullable: true)]
private ?string $footer = null;

#[ORM\Column(length: 255)]
#[Gedmo\Slug(fields:["name"])]
private ?string $slug = null;

#[ORM\Column(length: 10, nullable: true)]
#[Assert\NotBlank(message: 'company.currency.required')]
private ?string $currency = null;


   

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

    public function getAdress(): ?string
    {
        return $this->adress;
    }

    public function setAdress(?string $adress): static
    {
        $this->adress = $adress;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    public function getTaxIdentification(): ?string
    {
        return $this->TaxIdentification;
    }

    public function setTaxIdentification(?string $TaxIdentification): static
    {
        $this->TaxIdentification = $TaxIdentification;

        return $this;
    }

    public function getRegistrationNumber(): ?string
    {
        return $this->RegistrationNumber;
    }

    public function setRegistrationNumber(?string $RegistrationNumber): static
    {
        $this->RegistrationNumber = $RegistrationNumber;

        return $this;
    }

    public function getBankDetails(): ?string
    {
        return $this->BankDetails;
    }

    public function setBankDetails(?string $BankDetails): static
    {
        $this->BankDetails = $BankDetails;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): static
    {
        $this->logo = $logo;

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

    public function getFooter(): ?string
    {
        return $this->footer;
    }

    public function setFooter(?string $footer): static
    {
        $this->footer = $footer;

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

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(?string $currency): static
    {
        $this->currency = $currency;

        return $this;
    }
}
