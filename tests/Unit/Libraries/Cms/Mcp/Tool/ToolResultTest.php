<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Mcp
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Mcp\Tool;

use Joomla\CMS\Mcp\Tool\ToolResult;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for ToolResult.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mcp
 * @since       __DEPLOY_VERSION__
 */
class ToolResultTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTextCreatesSingleTextItem(): void
    {
        $result = ToolResult::text('hello');

        $this->assertSame([['type' => 'text', 'text' => 'hello']], $result->getContent());
        $this->assertFalse($result->isError());
        $this->assertNull($result->getStructuredContent());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testErrorCreatesErrorTextItem(): void
    {
        $result = ToolResult::error('boom');

        $this->assertSame([['type' => 'text', 'text' => 'boom']], $result->getContent());
        $this->assertTrue($result->isError());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testStructuredCarriesDataAndJsonTextFallback(): void
    {
        $data   = ['items' => [['id' => 1, 'title' => 'Test']]];
        $result = ToolResult::structured($data);

        $this->assertSame($data, $result->getStructuredContent());
        $this->assertSame(
            [['type' => 'text', 'text' => json_encode($data)]],
            $result->getContent()
        );
        $this->assertFalse($result->isError());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testStructuredAcceptsCustomFallbackText(): void
    {
        $result = ToolResult::structured(['a' => 1], 'One item found');

        $this->assertSame([['type' => 'text', 'text' => 'One item found']], $result->getContent());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testImageCreatesImageItem(): void
    {
        $result = ToolResult::image('aWJhc2U2NA==', 'image/png');

        $this->assertSame(
            [['type' => 'image', 'data' => 'aWJhc2U2NA==', 'mimeType' => 'image/png']],
            $result->getContent()
        );
        $this->assertFalse($result->isError());
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testAudioCreatesAudioItem(): void
    {
        $result = ToolResult::audio('YWJhc2U2NA==', 'audio/wav');

        $this->assertSame(
            [['type' => 'audio', 'data' => 'YWJhc2U2NA==', 'mimeType' => 'audio/wav']],
            $result->getContent()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testResourceLinkCreatesLinkItemWithoutNullFields(): void
    {
        $result = ToolResult::resourceLink('joomla://media/logo.png', 'logo.png');

        $this->assertSame(
            [['type' => 'resource_link', 'uri' => 'joomla://media/logo.png', 'name' => 'logo.png']],
            $result->getContent()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testResourceLinkIncludesOptionalFields(): void
    {
        $result = ToolResult::resourceLink(
            'joomla://media/logo.png',
            'logo.png',
            'Site logo',
            'image/png'
        );

        $this->assertSame(
            [
                [
                    'type'        => 'resource_link',
                    'uri'         => 'joomla://media/logo.png',
                    'name'        => 'logo.png',
                    'description' => 'Site logo',
                    'mimeType'    => 'image/png',
                ],
            ],
            $result->getContent()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmbeddedTextCreatesEmbeddedResourceItem(): void
    {
        $result = ToolResult::embeddedText('joomla://config', '{"sitename":"Test"}', 'application/json');

        $this->assertSame(
            [
                [
                    'type'     => 'resource',
                    'resource' => [
                        'uri'      => 'joomla://config',
                        'text'     => '{"sitename":"Test"}',
                        'mimeType' => 'application/json',
                    ],
                ],
            ],
            $result->getContent()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmbeddedBlobCreatesEmbeddedResourceItem(): void
    {
        $result = ToolResult::embeddedBlob('joomla://media/logo.png', 'YmxvYg==', 'image/png');

        $this->assertSame(
            [
                [
                    'type'     => 'resource',
                    'resource' => [
                        'uri'      => 'joomla://media/logo.png',
                        'blob'     => 'YmxvYg==',
                        'mimeType' => 'image/png',
                    ],
                ],
            ],
            $result->getContent()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testEmbeddedTextOmitsNullMimeType(): void
    {
        $result = ToolResult::embeddedText('joomla://config', 'plain');

        $this->assertSame(
            [['type' => 'resource', 'resource' => ['uri' => 'joomla://config', 'text' => 'plain']]],
            $result->getContent()
        );
    }
}
