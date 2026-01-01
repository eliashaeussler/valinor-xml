<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/valinor-xml".
 *
 * Copyright (C) 2024-2026 Elias Häußler <elias@haeussler.dev>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <https://www.gnu.org/licenses/>.
 */

namespace EliasHaeussler\ValinorXml\Tests\Mapper\Source;

use EliasHaeussler\ValinorXml as Src;
use EliasHaeussler\ValinorXml\Mapper\Source\XmlSource;
use PHPUnit\Framework;

use function dirname;

/**
 * XmlSourceTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(XmlSource::class)]
final class XmlSourceTest extends Framework\TestCase
{
    private XmlSource $subject;

    public function setUp(): void
    {
        $this->subject = new XmlSource([
            'foo' => [
                'baz' => 1,
            ],
        ]);
    }

    #[Framework\Attributes\Test]
    public function fromXmlStringThrowsExceptionOnMalformedXml(): void
    {
        $this->expectException(Src\Exception\XmlIsMalformed::class);

        XmlSource::fromXmlString('');
    }

    #[Framework\Attributes\Test]
    public function fromXmlStringReturnsSourceForGivenXml(): void
    {
        $xml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<root>
    <foo>
        <baz>1</baz>
    </foo>
</root>
XML;

        self::assertEquals($this->subject, XmlSource::fromXmlString($xml));
    }

    #[Framework\Attributes\Test]
    public function fromXmlFileThrowExceptionIfGivenFileDoesNotExist(): void
    {
        $this->expectException(Src\Exception\FileDoesNotExist::class);

        XmlSource::fromXmlFile('foo');
    }

    #[Framework\Attributes\Test]
    public function fromXmlFileReturnsSourceForGivenXmlFile(): void
    {
        self::assertEquals($this->subject, XmlSource::fromXmlFile(dirname(__DIR__, 2).'/Fixtures/dummy.xml'));
    }

    #[Framework\Attributes\Test]
    public function asCollectionConvertsGivenNodePathToCollection(): void
    {
        $expected = new XmlSource([
            'foo' => [
                [
                    'baz' => 1,
                ],
            ],
        ]);

        self::assertEquals($expected, $this->subject->asCollection('foo'));
    }
}
