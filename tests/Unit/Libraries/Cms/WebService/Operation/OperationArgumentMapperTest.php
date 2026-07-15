<?php

namespace Joomla\Tests\Unit\Libraries\Cms\WebService\Operation;

use Joomla\CMS\WebService\Operation\OperationArgumentMapper;
use Joomla\CMS\WebService\Operation\OperationCompiler;
use Joomla\Component\Content\Api\Controller\ArticlesController;
use PHPUnit\Framework\TestCase;

final class OperationArgumentMapperTest extends TestCase
{
    public function testUpdateArgumentsAreSplitIntoPathAndBody(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[3];
        $input     = (new OperationArgumentMapper())->map(
            $operation,
            ['id' => 7, 'title' => 'Changed title'],
        );

        self::assertSame(['id' => 7], $input->path);
        self::assertSame([], $input->query);
        self::assertSame(['title' => 'Changed title'], $input->body);
    }

    public function testListArgumentsUseJoomlaQueryParameterNames(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[0];
        $input     = (new OperationArgumentMapper())->map(
            $operation,
            ['author' => 42, 'ordering' => 'created'],
        );

        self::assertSame(
            ['filter[author]' => 42, 'list[ordering]' => 'created'],
            $input->query,
        );
    }

    public function testDateTimeArgumentsAreConvertedToTheStoredUtcFormat(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[0];
        $input     = (new OperationArgumentMapper())->map(
            $operation,
            ['modified_start' => '2026-01-17T22:00:00+05:00'],
        );

        self::assertSame(['filter[modified_start]' => '2026-01-17 17:00:00'], $input->query);
    }

    public function testDateTimeArgumentsWithoutAnOffsetAreReadAsUtc(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[0];
        $input     = (new OperationArgumentMapper())->map(
            $operation,
            ['modified_end' => '2026-01-17T22:00:00'],
        );

        self::assertSame(['filter[modified_end]' => '2026-01-17 22:00:00'], $input->query);
    }

    public function testDateTimeObjectsAreAccepted(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[0];
        $input     = (new OperationArgumentMapper())->map(
            $operation,
            ['modified_start' => new \DateTimeImmutable('2026-01-17 22:00:00', new \DateTimeZone('+05:00'))],
        );

        self::assertSame(['filter[modified_start]' => '2026-01-17 17:00:00'], $input->query);
    }

    public function testNonDateTimeArgumentsAreNotReformatted(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[0];
        $input     = (new OperationArgumentMapper())->map(
            $operation,
            ['search' => '2026-01-17T22:00:00+05:00'],
        );

        self::assertSame(['filter[search]' => '2026-01-17T22:00:00+05:00'], $input->query);
    }

    public function testAnUnusableDateTimeArgumentIsRejected(): void
    {
        $operation = (new OperationCompiler())->compile(ArticlesController::class)[0];

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('modified_start');

        (new OperationArgumentMapper())->map($operation, ['modified_start' => 'last friday-ish']);
    }
}
