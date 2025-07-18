<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\TypeDocument;

class TypeDocumentTest extends TestCase
{
    public function testInitialState(): void
    {
        $type = new TypeDocument();

        $this->assertNull($type->getId());
        $this->assertNull($type->getName());
        $this->assertNull($type->getLabel());
        $this->assertNull($type->getCreatedAt());
        $this->assertNull($type->getSlug());
    }

    public function testSettersAndGetters(): void
    {
        $type      = new TypeDocument();
        $now       = new \DateTime('2025-07-18 12:00:00');
        $type
            ->setName('Invoice')
            ->setLabel('INV')
            ->setCreatedAt($now)
            ->setSlug('invoice');

        $this->assertSame('Invoice', $type->getName());
        $this->assertSame('INV',     $type->getLabel());
        $this->assertSame($now,      $type->getCreatedAt());
        $this->assertSame('invoice', $type->getSlug());
    }
}
