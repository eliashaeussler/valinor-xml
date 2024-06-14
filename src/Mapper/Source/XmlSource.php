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

namespace EliasHaeussler\ValinorXml\Mapper\Source;

use ArrayObject;
use EliasHaeussler\ValinorXml\Exception;
use EliasHaeussler\ValinorXml\Helper;
use Mtownsend\XmlToArray;
use Throwable;

use function restore_error_handler;
use function set_error_handler;

/**
 * XmlSource.
 *
 * @author Elias Häußler <elias@haeussler.dev>
 * @license GPL-3.0-or-later
 *
 * @extends ArrayObject<string, mixed>
 */
final class XmlSource extends ArrayObject
{
    /**
     * @param array<string, mixed> $source
     */
    public function __construct(array $source)
    {
        parent::__construct($source);
    }

    /**
     * @throws Exception\XmlIsMalformed
     */
    public static function fromXmlString(string $xml): self
    {
        set_error_handler(static fn (int $code, string $message) => self::handleParseError($xml, $message));

        try {
            $source = XmlToArray\XmlToArray::convert($xml);
        } catch (Throwable $exception) {
            self::handleParseError($xml, $exception->getMessage());
        } finally {
            restore_error_handler();
        }

        return new self($source);
    }

    /**
     * @throws Exception\FileDoesNotExist
     * @throws Exception\FileIsNotReadable
     * @throws Exception\XmlIsMalformed
     */
    public static function fromXmlFile(string $file): self
    {
        if (!file_exists($file)) {
            throw new Exception\FileDoesNotExist($file);
        }

        if (!is_readable($file)) {
            throw new Exception\FileIsNotReadable($file);
        }

        $xml = file_get_contents($file);

        if (false === $xml) {
            throw new Exception\FileIsNotReadable($file);
        }

        return self::fromXmlString($xml);
    }

    /**
     * @throws Exception\ArrayPathHasUnexpectedType
     * @throws Exception\ArrayPathIsInvalid
     */
    public function asCollection(string $node): self
    {
        $clone = clone $this;
        $clone->exchangeArray(
            Helper\ArrayHelper::convertToCollection((array) $clone, $node),
        );

        return $clone;
    }

    /**
     * @throws Exception\XmlIsMalformed
     */
    private static function handleParseError(string $xml, string $message): never
    {
        throw new Exception\XmlIsMalformed($xml, $message);
    }
}
