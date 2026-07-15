<?php

/**
 * @package     Joomla.UnitTest
 * @subpackage  Mcp
 *
 * @copyright   (C) 2026 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Tests\Unit\Libraries\Cms\Mcp\Resource;

use Joomla\CMS\Mcp\Resource\ResourceResult;
use Joomla\Tests\Unit\UnitTestCase;

/**
 * Test class for ResourceResult.
 *
 * @package     Joomla.UnitTest
 * @subpackage  Mcp
 * @since       __DEPLOY_VERSION__
 */
class ResourceResultTest extends UnitTestCase
{
    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTextCreatesSingleTextItem(): void
    {
        $result = ResourceResult::text('joomla://config', '{"sitename":"Test"}', 'application/json');

        $this->assertSame(
            [['uri' => 'joomla://config', 'text' => '{"sitename":"Test"}', 'mimeType' => 'application/json']],
            $result->getContents()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testTextDefaultsToPlainTextMimeType(): void
    {
        $result = ResourceResult::text('joomla://info', 'hello');

        $this->assertSame(
            [['uri' => 'joomla://info', 'text' => 'hello', 'mimeType' => 'text/plain']],
            $result->getContents()
        );
    }

    /**
     * @return  void
     *
     * @since   __DEPLOY_VERSION__
     */
    public function testBlobCreatesSingleBlobItem(): void
    {
        $result = ResourceResult::blob('joomla://media/logo.png', 'YmxvYg==', 'image/png');

        $this->assertSame(
            [['uri' => 'joomla://media/logo.png', 'blob' => 'YmxvYg==', 'mimeType' => 'image/png']],
            $result->getContents()
        );
    }
}
