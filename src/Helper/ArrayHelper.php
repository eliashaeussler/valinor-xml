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

namespace EliasHaeussler\ValinorXml\Helper;

use EliasHaeussler\ValinorXml\Exception;

use function array_is_list;
use function array_key_exists;
use function array_shift;
use function array_slice;
use function gettype;
use function implode;
use function is_array;
use function is_string;
use function str_getcsv;
use function trim;

/**
 * ArrayHelper.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 */
final class ArrayHelper
{
    /**
     * @param array<string, mixed> $array
     *
     * @return array<string, mixed>
     *
     * @throws Exception\ArrayPathHasUnexpectedType
     * @throws Exception\ArrayPathIsInvalid
     */
    public static function convertToCollection(array $array, string $path): array
    {
        $reference = &$array;
        $pathSegments = str_getcsv($path, '.');
        $remainingSegments = $pathSegments;
        $currentPathSegments = [];

        foreach ($pathSegments as $pathSegment) {
            $currentPathSegments[] = array_shift($remainingSegments);

            // Validate path segment
            if (!is_string($pathSegment) || '' === trim($pathSegment)) {
                throw new Exception\ArrayPathIsInvalid(implode('.', $currentPathSegments));
            }

            // Handle placeholder for lists
            if ('*' === $pathSegment) {
                $reference = self::convertListToCollection(
                    $reference,
                    implode('.', array_slice($currentPathSegments, 0, -1)),
                    implode('.', $remainingSegments),
                );

                return $array;
            }

            // Create node value if not exists
            if (!array_key_exists($pathSegment, $reference)) {
                $reference[$pathSegment] = [];
            }

            $reference = &$reference[$pathSegment];

            // Handle non-array values
            if (!is_array($reference)) {
                if ([] !== $remainingSegments) {
                    throw new Exception\ArrayPathHasUnexpectedType(implode('.', $currentPathSegments), 'array', gettype($reference));
                }

                // This is actually superfluous, it's just here to make PHPStan happy.
                break;
            }
        }

        // Convert array to list
        if (!is_array($reference) || !array_is_list($reference)) {
            $reference = [$reference];
        }

        return $array;
    }

    /**
     * @param array<mixed> $array
     *
     * @return array<int, mixed>
     *
     * @throws Exception\ArrayPathHasUnexpectedType
     * @throws Exception\ArrayPathIsInvalid
     */
    private static function convertListToCollection(array $array, string $currentPath, string $remainingPath): array
    {
        // Handle non-lists
        if (!array_is_list($array)) {
            throw new Exception\ArrayPathHasUnexpectedType($currentPath, 'list', 'array');
        }

        foreach ($array as $key => $value) {
            $array[$key] = self::convertToCollection($value, $remainingPath);
        }

        return $array;
    }
}
