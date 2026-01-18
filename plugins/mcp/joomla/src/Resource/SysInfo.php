<?php

namespace Joomla\Plugin\Mcp\Joomla\Resource;

use Joomla\CMS\Version;
use Joomla\Component\MCP\Api\Resource\ResourceInterface;
use Mcp\Types\ReadResourceRequest;
use Mcp\Types\ReadResourceResult;
use Mcp\Types\TextResourceContents;

class SysInfo implements ResourceInterface
{

    public function getName(): string
    {
        return "sysInfo";
    }

    public function getUri(): string
    {
        return "joomla://com_admin/sysinfo";
    }

    public function getDescription(): string
    {
        return "Shows Joomla System Information";
    }

    public function getTitle(): string
    {
        return "Get System Information";
    }

    public function getMimeType(): string
    {
        return "text/plain";
    }

    public function read(): ReadResourceResult
    {
        $info = [
            "Server Time: " . date('Y-m-d H:i:s'),
            "PHP Version: " . PHP_VERSION,
            "Joomla Version: " . (new Version())->getLongVersion()
        ];

        return new ReadResourceResult(
            contents: [
                new TextResourceContents(
                    uri: $this->getUri(),
                    text: implode("\n", $info),
                    mimeType: $this->getMimeType(),
                )
            ]
        );
    }
}
