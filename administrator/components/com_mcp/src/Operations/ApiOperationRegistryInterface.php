<?php
/**
 * @package     Joomla.Administrator
 * @subpackage  com_mcp
 *
 * @copyright   (C) 2026 Open Source Matters, Inc.
 * <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

namespace Joomla\Component\MCP\Administrator\Operations;

/**
 * Interface for managing a registry of API operations.
 * Provides methods for registering, retrieving all operations,
 * and fetching a specific operation by its identifier.
 *
 * @since  __DEPLOY_VERSION__
 */
interface ApiOperationRegistryInterface
{
    /**
     * Register a new API operation.
     *
     * @param ApiOperationDescriptor $operation
     *
     * @return void
     * @since  __DEPLOY_VERSION__
     */
    public function register(ApiOperationDescriptor $operation): void;

    /**
     * Retrieve all registered API operations.
     *
     * @return iterable<ApiOperationDescriptor>
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getOperations(): iterable;

    /**
     * Retrieve the API operation associated with the given operation ID.
     *
     * @param string $operationId The unique identifier for the API operation.
     *
     * @return ApiOperationDescriptor|null The API operation descriptor if found, or null if it does not exist.
     */
    public function getOperation(string $operationId): ?ApiOperationDescriptor;
}
