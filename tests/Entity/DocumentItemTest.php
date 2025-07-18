<?php

namespace App\Tests\Entity;

use PHPUnit\Framework\TestCase;
use App\Entity\DocumentItem;
use App\Entity\Document;

class DocumentItemTest extends TestCase
{
    public function testInitialState(): void
    {
        $item = new DocumentItem();

        $this->assertNull($item->getId());
        $this->assertNull($item->getDocument());
        $this->assertNull($item->getTitle());
        $this->assertNull($item->getQuantity());
        $this->assertNull($item->getUnitPrice());
        $this->assertNull($item->getDiscount());
        $this->assertNull($item->getTaxe());
        $this->assertNull($item->getTotal());
    }

    public function testSettersAndGetters(): void
    {
        $doc  = new Document();
        $item = (new DocumentItem())
            ->setDocument($doc)
            ->setTitle('Test Item')
            ->setQuantity(3)
            ->setUnitPrice(10.0)
            ->setDiscount(10)
            ->setTaxe(20);

        $this->assertSame($doc, $item->getDocument());
        $this->assertSame('Test Item', $item->getTitle());
        $this->assertSame(3, $item->getQuantity());
        $this->assertSame(10.0, $item->getUnitPrice());
        $this->assertSame(10, $item->getDiscount());
        $this->assertSame(20, $item->getTaxe());
    }

    public function testCalculateTotalWithoutDiscountOrTax(): void
    {
        $item = (new DocumentItem())
            ->setQuantity(2)
            ->setUnitPrice(15.0)
            ->setDiscount(null)
            ->setTaxe(0);

        $item->calculateTotal();
        // base = 30, no discount, no tax → total = 30
        $this->assertSame(30.0, $item->getTotal());
    }

    public function testCalculateTotalWithDiscountAndTax(): void
    {
        $item = (new DocumentItem())
            ->setQuantity(2)
            ->setUnitPrice(10.0)
            ->setDiscount(10)   // 10% discount
            ->setTaxe(20);      // 20% tax

        $item->calculateTotal();
        // base = 20, after discount = 18, after tax = 21.6
        $this->assertSame(21.60, $item->getTotal());
    }
}
