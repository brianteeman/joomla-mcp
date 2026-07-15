<?php

/**
 * @package     Joomla.Platform
 * @subpackage  WebService
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\WebService\Operation;

/**
 * Maps canonical MCP-style arguments to REST path, query and request-body input.
 *
 * @since  __DEPLOY_VERSION__
 */
final class OperationArgumentMapper
{
    /**
     * @param array<string, mixed> $arguments
     *
     * @since  __DEPLOY_VERSION__
     */
    public function map(OperationDefinition $operation, array $arguments): OperationInput
    {
        $path  = [];
        $query = [];
        $body  = [];

        foreach ($operation->pathParameters as $transportName => $parameter) {
            $argumentName = $parameter['argument'] ?? $transportName;

            if (\array_key_exists($argumentName, $arguments)) {
                $path[$transportName] = $arguments[$argumentName];
            }
        }

        foreach ($operation->queryParameters as $transportName => $parameter) {
            $argumentName = $parameter['argument'] ?? $transportName;

            if (\array_key_exists($argumentName, $arguments)) {
                $query[$transportName] = $this->transportValue(
                    $arguments[$argumentName],
                    $parameter['schema'] ?? [],
                    $argumentName,
                );
            }
        }

        foreach ($operation->requestBodySchema['properties'] ?? [] as $name => $schema) {
            if (!\array_key_exists($name, $arguments)) {
                continue;
            }

            $body[$name] = $this->transportValue($arguments[$name], $schema, $name);
        }

        return new OperationInput($path, $query, $body);
    }

    /**
     * Converts a contract value to the representation the established Joomla transport expects.
     *
     * @param array<string, mixed> $schema
     *
     * @since  __DEPLOY_VERSION__
     */
    private function transportValue(mixed $value, array $schema, string $argumentName): mixed
    {
        if (($schema['format'] ?? null) !== 'date-time' || $value === null || $value === '') {
            return $value;
        }

        return $this->sqlDateTime($value, $argumentName);
    }

    /**
     * Formats a date-time argument the way Joomla stores it: UTC, without an offset. A value carrying an explicit
     * offset is converted; a value without one is read as UTC. The database truncates an RFC 3339 string to a warning
     * and a silently wrong comparison rather than an error, so the offset has to be resolved here.
     *
     * @throws \InvalidArgumentException  If the argument is not a usable date-time value.
     *
     * @since  __DEPLOY_VERSION__
     */
    private function sqlDateTime(mixed $value, string $argumentName): string
    {
        if ($value instanceof \DateTimeInterface) {
            $date = \DateTimeImmutable::createFromInterface($value);
        } elseif (\is_string($value)) {
            try {
                $date = new \DateTimeImmutable($value, new \DateTimeZone('UTC'));
            } catch (\Exception $e) {
                throw new \InvalidArgumentException(
                    \sprintf('Argument %s is not a valid date-time value.', $argumentName),
                    0,
                    $e,
                );
            }
        } else {
            throw new \InvalidArgumentException(
                \sprintf('Argument %s must be a date-time string.', $argumentName),
            );
        }

        return $date->setTimezone(new \DateTimeZone('UTC'))->format('Y-m-d H:i:s');
    }
}
