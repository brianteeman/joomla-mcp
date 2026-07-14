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
 * Represents a descriptor for an API operation, encapsulating metadata and configuration
 * required to define and execute an API endpoint within the system.
 *
 * This class provides critical details such as the operation's unique identifier, HTTP method,
 * endpoint path, controller, associated task, and descriptive metadata to ensure proper handling
 * and documentation of the operation. It also includes schema definitions for input and output
 * data, access control configurations, annotations, and additional metadata.
 *
 * @since __DEPLOY_VERSION__
 */
final readonly class ApiOperationDescriptor
{
    /**
     * Constructor for ApiOperationDescriptor.
     *
     * @param string $operationId: Unique identifier for the API operation.
     * @param string $method: HTTP method associated with the operation (e.g., GET, POST).
     * @param string $path: URL path defining the operation's endpoint.
     * @param string $controller: Fully-qualified name of the controller responsible for handling the operation.
     * @param string $task: Logical task or handler method to execute for the operation.
     * @param string $title: Human-readable title for the operation.
     * @param string $description: Detailed description providing context and usage information for the operation.
     * @param array $inputSchema: Schema defining the expected structure and validation rules for input data.
     * @param array|null $outputSchema: Schema defining the structure of the response data, if applicable.
     * @param array $acl: Access control configuration determining permissions required to invoke the operation.
     * @param array $annotations: Additional metadata or attributes associated with the operation.
     * @param bool $exposeToMcp: Flag indicating if the operation should be exposed to an MCP (Management Control Plane).
     * @param array $tags: Array of tags or labels categorizing the operation for organizational purposes.
     *
     * @since __DEPLOY_VERSION__
     */
    public function __construct(
        public string $operationId,
        public string $method,
        public string $path,
        public string $controller,
        public string $task,
        public string $title,
        public string $description,
        public array  $inputSchema,
        public ?array $outputSchema,
        public array  $acl,
        public array  $annotations,
        public bool   $exposeToMcp = false,
        public array  $tags = [],
    )
    {
    }
}
