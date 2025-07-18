<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\Company;
use App\Entity\User;

class CompanyTest extends TestCase
{
    public function testInitialState(): void
    {
        $company = new Company();

        $this->assertNull($company->getId());
        $this->assertNull($company->getName());
        $this->assertNull($company->getAdress());
        $this->assertNull($company->getPhone());
        $this->assertNull($company->getTaxIdentification());
        $this->assertNull($company->getRegistrationNumber());
        $this->assertNull($company->getBankDetails());
        $this->assertNull($company->getLogo());
        $this->assertNull($company->getFooter());
        $this->assertNull($company->getSlug());
        $this->assertNull($company->getCurrency());
        $this->assertNull($company->getUser());
    }

    public function testSettersAndGetters(): void
    {
        $company = new Company();
        $user    = new User();

        $company
            ->setName('My Company')
            ->setAdress('456 Business Rd')
            ->setPhone('+123456789')
            ->setTaxIdentification('TAX-999')
            ->setRegistrationNumber('REG-123')
            ->setBankDetails('Bank XYZ, IBAN 0000')
            ->setLogo('logo.png')
            ->setFooter('© 2025 My Company')
            ->setSlug('my-company')
            ->setCurrency('USD')
            ->setUser($user);

        $this->assertSame('My Company',        $company->getName());
        $this->assertSame('456 Business Rd',    $company->getAdress());
        $this->assertSame('+123456789',         $company->getPhone());
        $this->assertSame('TAX-999',            $company->getTaxIdentification());
        $this->assertSame('REG-123',            $company->getRegistrationNumber());
        $this->assertSame('Bank XYZ, IBAN 0000',$company->getBankDetails());
        $this->assertSame('logo.png',           $company->getLogo());
        $this->assertSame('© 2025 My Company',  $company->getFooter());
        $this->assertSame('my-company',         $company->getSlug());
        $this->assertSame('USD',                $company->getCurrency());
        $this->assertSame($user,                $company->getUser());
    }
}
