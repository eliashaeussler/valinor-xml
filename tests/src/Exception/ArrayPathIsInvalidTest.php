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

namespace EliasHaeussler\ValinorXml\Tests\Exception;

use EliasHaeussler\ValinorXml as Src;
use PHPUnit\Framework;

/**
 * ArrayPathIsInvalidTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Exception\ArrayPathIsInvalid::class)]
final class ArrayPathIsInvalidTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function constructorReturnsExceptionForInvalidPathSegment(): void
    {
        $actual = new Src\Exception\ArrayPathIsInvalid('foo..baz');

        self::assertSame('The array path segment "foo..baz" is not valid.', $actual->getMessage());
        self::assertSame(1718373174, $actual->getCode());
    }
}
