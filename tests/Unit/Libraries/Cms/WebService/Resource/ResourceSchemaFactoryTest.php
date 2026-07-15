<?php

namespace Joomla\Tests\Unit\Libraries\Cms\WebService\Resource;

use Joomla\CMS\WebService\Resource\ResourceProfile;
use Joomla\CMS\WebService\Resource\Schema\ResourceSchemaFactory;
use Joomla\Component\Content\Api\Resource\Article;
use PHPUnit\Framework\TestCase;

final class ResourceSchemaFactoryTest extends TestCase
{
    public function testCreateSchemaUsesDefaultsAndGuardedConvention(): void
    {
        $schema = (new ResourceSchemaFactory())->create(Article::class, ResourceProfile::CREATE);

        self::assertSame(['title', 'text', 'catid'], $schema['required']);
        self::assertArrayNotHasKey('id', $schema['properties']);
        self::assertSame('*', $schema['properties']['language']['default']);
        self::assertSame('integer', $schema['properties']['tags']['items']['type']);
        self::assertTrue($schema['additionalProperties']);
    }

    public function testUpdateSchemaIsPartialAndPreservesNullableSemantics(): void
    {
        $schema = (new ResourceSchemaFactory())->create(Article::class, ResourceProfile::UPDATE);

        self::assertArrayNotHasKey('required', $schema);
        self::assertSame(1, $schema['minProperties']);
        self::assertArrayNotHasKey('modified', $schema['properties']);
        self::assertContains('null', $schema['properties']['note']['type']);
    }

    public function testListSchemaHonoursProfileVisibility(): void
    {
        $schema = (new ResourceSchemaFactory())->create(Article::class, ResourceProfile::LIST);

        self::assertArrayNotHasKey('modified_by', $schema['properties']);
        self::assertArrayHasKey('modified_by', (new ResourceSchemaFactory())->create(
            Article::class,
            ResourceProfile::READ,
        )['properties']);
    }

    public function testReadSchemaContainsGuardedProperties(): void
    {
        $schema = (new ResourceSchemaFactory())->create(Article::class, ResourceProfile::READ);

        self::assertTrue($schema['properties']['id']['readOnly']);
        self::assertSame('object', $schema['properties']['tags']['items']['type']);
        self::assertSame('date-time', $schema['properties']['created']['format']);
    }

    public function testCategoryIsWrittenAsAnIdentifierAndReadAsAnObject(): void
    {
        $factory = new ResourceSchemaFactory();

        $create = $factory->create(Article::class, ResourceProfile::CREATE)['properties'];
        $read   = $factory->create(Article::class, ResourceProfile::READ)['properties'];

        self::assertSame('integer', $create['catid']['type']);
        self::assertArrayNotHasKey('category', $create);

        self::assertSame('object', $read['category']['type']);
        self::assertArrayNotHasKey('catid', $read);
    }
}
