<?php

declare(strict_types=1);

/*
 * This file is part of the Composer package "eliashaeussler/valinor-xml".
 *
 * Copyright (C) 2024 Elias Häußler <elias@haeussler.dev>
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

namespace EliasHaeussler\ValinorXml\Tests\Helper;

use EliasHaeussler\ValinorXml as Src;
use Generator;
use PHPUnit\Framework;

/**
 * ArrayHelperTest.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
#[Framework\Attributes\CoversClass(Src\Helper\ArrayHelper::class)]
final class ArrayHelperTest extends Framework\TestCase
{
    #[Framework\Attributes\Test]
    public function convertToCollectionThrowsExceptionOnInvalidPathSegments(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ArrayPathIsInvalid('foo.'),
        );

        Src\Helper\ArrayHelper::convertToCollection([], 'foo..baz');
    }

    #[Framework\Attributes\Test]
    public function convertToCollectionThrowsExceptionOnNonListValues(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ArrayPathHasUnexpectedType('foo', 'list', 'array'),
        );

        $array = [
            'foo' => [
                'baz' => null,
            ],
        ];

        Src\Helper\ArrayHelper::convertToCollection($array, 'foo.*.baz');
    }

    #[Framework\Attributes\Test]
    public function convertToCollectionRespectsListPlaceholders(): void
    {
        $array = [
            'foo' => [
                [
                    'baz' => [
                        'hello' => 'world',
                    ],
                ],
                [
                    'baz' => [
                        'hello' => 'world',
                    ],
                ],
            ],
        ];

        $expected = [
            'foo' => [
                [
                    'baz' => [
                        [
                            'hello' => 'world',
                        ],
                    ],
                ],
                [
                    'baz' => [
                        [
                            'hello' => 'world',
                        ],
                    ],
                ],
            ],
        ];

        self::assertSame($expected, Src\Helper\ArrayHelper::convertToCollection($array, 'foo.*.baz'));
    }

    #[Framework\Attributes\Test]
    public function convertToCollectionThrowsExceptionOnNonArrayValues(): void
    {
        $this->expectExceptionObject(
            new Src\Exception\ArrayPathHasUnexpectedType('foo', 'array', 'NULL'),
        );

        $array = [
            'foo' => null,
        ];

        Src\Helper\ArrayHelper::convertToCollection($array, 'foo.baz');
    }

    /**
     * @param array<string, mixed> $expected
     */
    #[Framework\Attributes\Test]
    #[Framework\Attributes\DataProvider('convertToCollectionConvertsGivenPathToCollectionDataProvider')]
    public function convertToCollectionConvertsGivenPathToCollection(string $path, array $expected): void
    {
        $array = [
            'foo' => [
                'baz' => [
                    'hello' => 'world',
                ],
            ],
        ];

        self::assertSame($expected, Src\Helper\ArrayHelper::convertToCollection($array, $path));
    }

    /**
     * @return Generator<string, array{string, array<string, mixed>}>
     */
    public static function convertToCollectionConvertsGivenPathToCollectionDataProvider(): Generator
    {
        yield 'array to list' => [
            'foo.baz',
            [
                'foo' => [
                    'baz' => [
                        [
                            'hello' => 'world',
                        ],
                    ],
                ],
            ],
        ];
        yield 'non-array to list' => [
            'foo.baz.hello',
            [
                'foo' => [
                    'baz' => [
                        'hello' => [
                            'world',
                        ],
                    ],
                ],
            ],
        ];
    }
}
