<?php

/**
 * Joomla! Content Management System
 *
 * @copyright  (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\CMS\Mcp\Tool;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Result of an MCP tool execution.
 *
 * Value object shielding tool implementations from the underlying MCP SDK types;
 * the endpoint converts it to the wire format.
 *
 * @since  __DEPLOY_VERSION__
 */
final readonly class ToolResult
{
    /**
     * Content type for plain text items
     *
     * @since  __DEPLOY_VERSION__
     */
    public const string TYPE_TEXT = 'text';

    /**
     * Content type for image items
     *
     * @since  __DEPLOY_VERSION__
     */
    public const string TYPE_IMAGE = 'image';

    /**
     * Content type for audio items
     *
     * @since  __DEPLOY_VERSION__
     */
    public const string TYPE_AUDIO = 'audio';

    /**
     * Content type for embedded resource items
     *
     * @since  __DEPLOY_VERSION__
     */
    public const string TYPE_RESOURCE = 'resource';

    /**
     * Content type for resource link items
     *
     * @since  __DEPLOY_VERSION__
     */
    public const string TYPE_RESOURCE_LINK = 'resource_link';

    /**
     * Constructor.
     *
     * @param array   $content            List of content items, each ['type' => self::TYPE_*, ...]
     * @param boolean $error              Whether the result represents an error
     * @param mixed   $structuredContent  Structured output matching the tool's output schema
     *
     * @since  __DEPLOY_VERSION__
     */
    private function __construct(
        private array $content,
        private bool $error,
        private mixed $structuredContent = null
    ) {
    }

    /**
     * Create a successful text result
     *
     * @param string $text  The result text
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function text(string $text): self
    {
        return new self([['type' => self::TYPE_TEXT, 'text' => $text]], false);
    }

    /**
     * Create an error text result
     *
     * @param string $text  The error message
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function error(string $text): self
    {
        return new self([['type' => self::TYPE_TEXT, 'text' => $text]], true);
    }

    /**
     * Create a result with structured data matching the tool's output schema.
     *
     * A plain text fallback is included for clients that do not support
     * structured content; it defaults to the JSON encoded data.
     *
     * @param mixed   $data  The structured data
     * @param ?string $text  Optional fallback text, defaults to the JSON encoded data
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function structured(mixed $data, ?string $text = null): self
    {
        return new self(
            [['type' => self::TYPE_TEXT, 'text' => $text ?? json_encode($data)]],
            false,
            $data
        );
    }

    /**
     * Create an image result
     *
     * @param string $data      The base64 encoded image data
     * @param string $mimeType  The image MIME type, e.g. "image/png"
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function image(string $data, string $mimeType): self
    {
        return new self([['type' => self::TYPE_IMAGE, 'data' => $data, 'mimeType' => $mimeType]], false);
    }

    /**
     * Create an audio result
     *
     * @param string $data      The base64 encoded audio data
     * @param string $mimeType  The audio MIME type, e.g. "audio/wav"
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function audio(string $data, string $mimeType): self
    {
        return new self([['type' => self::TYPE_AUDIO, 'data' => $data, 'mimeType' => $mimeType]], false);
    }

    /**
     * Create a resource link result; the client may fetch the resource via resources/read.
     *
     * @param string  $uri          The resource URI
     * @param string  $name         The resource name
     * @param ?string $description  Optional description of the resource
     * @param ?string $mimeType     Optional MIME type of the resource
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function resourceLink(string $uri, string $name, ?string $description = null, ?string $mimeType = null): self
    {
        $item = ['type' => self::TYPE_RESOURCE_LINK, 'uri' => $uri, 'name' => $name];

        if ($description !== null) {
            $item['description'] = $description;
        }

        if ($mimeType !== null) {
            $item['mimeType'] = $mimeType;
        }

        return new self([$item], false);
    }

    /**
     * Create a result embedding a text resource
     *
     * @param string  $uri       The resource URI
     * @param string  $text      The resource text content
     * @param ?string $mimeType  Optional MIME type of the resource
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function embeddedText(string $uri, string $text, ?string $mimeType = null): self
    {
        $resource = ['uri' => $uri, 'text' => $text];

        if ($mimeType !== null) {
            $resource['mimeType'] = $mimeType;
        }

        return new self([['type' => self::TYPE_RESOURCE, 'resource' => $resource]], false);
    }

    /**
     * Create a result embedding a binary resource
     *
     * @param string  $uri       The resource URI
     * @param string  $blob      The base64 encoded resource content
     * @param ?string $mimeType  Optional MIME type of the resource
     *
     * @return self
     *
     * @since  __DEPLOY_VERSION__
     */
    public static function embeddedBlob(string $uri, string $blob, ?string $mimeType = null): self
    {
        $resource = ['uri' => $uri, 'blob' => $blob];

        if ($mimeType !== null) {
            $resource['mimeType'] = $mimeType;
        }

        return new self([['type' => self::TYPE_RESOURCE, 'resource' => $resource]], false);
    }

    /**
     * Get the content items
     *
     * @return array  List of content items, each ['type' => self::TYPE_*, ...]
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getContent(): array
    {
        return $this->content;
    }

    /**
     * Whether the result represents an error
     *
     * @return boolean
     *
     * @since  __DEPLOY_VERSION__
     */
    public function isError(): bool
    {
        return $this->error;
    }

    /**
     * Get the structured content
     *
     * @return mixed  Structured output matching the tool's output schema, or null if not set
     *
     * @since  __DEPLOY_VERSION__
     */
    public function getStructuredContent(): mixed
    {
        return $this->structuredContent;
    }
}
