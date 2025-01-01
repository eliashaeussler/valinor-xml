<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/valinor-xml".
 *
 * Copyright (C) 2024-2025 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\ValinorXml\Tests\Exception;

use EliasHaeussler\ValinorXml as Src;
use PHPUnit\Framework;

/**
 * XmlIsMalformedTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\XmlIsMalformed::class)]
final class XmlIsMalformedTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorReturnsMalformedXmlException(): void
    {
        $actual = new Src\Exception\XmlIsMalformed('input', 'error');

        self::assertSame('The string "input" does not contain valid XML: error', $actual->getMessage());
        self::assertSame(1718372740, $actual->getCode());
    }
}
