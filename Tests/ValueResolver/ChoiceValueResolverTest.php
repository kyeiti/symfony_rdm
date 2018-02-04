<?php
/**
 * Copyright (C) 2017  Gerrit Addiks.
 * This package (including this file) was released under the terms of the GPL-3.0.
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/> or send me a mail so i can send you a copy.
 * @license GPL-3.0
 * @author Gerrit Addiks <gerrit@addiks.de>
 */

namespace Addiks\RDMBundle\Tests\ValueResolver;

use PHPUnit\Framework\TestCase;
use Addiks\RDMBundle\ValueResolver\ValueResolverInterface;
use Addiks\RDMBundle\ValueResolver\ChoiceValueResolver;
use Addiks\RDMBundle\Mapping\ChoiceMapping;
use Addiks\RDMBundle\Tests\Hydration\EntityExample;
use Addiks\RDMBundle\Mapping\ServiceMapping;
use Addiks\RDMBundle\Mapping\MappingInterface;

final class ChoiceValueResolverTest extends TestCase
{

    /**
     * @var ChoiceValueResolver
     */
    private $valueResolver;

    /**
     * @var ValueResolverInterface
     */
    private $innerValueResolver;

    public function setUp()
    {
        $this->innerValueResolver = $this->createMock(ValueResolverInterface::class);

        $this->valueResolver = new ChoiceValueResolver($this->innerValueResolver);
    }

    /**
     * @test
     */
    public function choosesTheCorrectValue()
    {
        $serviceMappingFoo = new ServiceMapping("foo_service");
        $serviceMappingBar = new ServiceMapping("bar_service");

        $fieldMapping = new ChoiceMapping("some_column", [
            'foo' => $serviceMappingFoo,
            'bar' => $serviceMappingBar
        ]);

        $entity = new EntityExample();

        /** @var array<scalar> $dataFromAdditionalColumns */
        $dataFromAdditionalColumns = array(
            'lorem' => 'ipsum',
            'some_column' => 'foo',
        );

        /** @var string $expectedValue */
        $expectedValue = "lorem ipsum dolor sit amet";

        $this->innerValueResolver->method('resolveValue')->will($this->returnValueMap([
            [$serviceMappingFoo, $entity, $dataFromAdditionalColumns, $expectedValue],
            [$serviceMappingBar, $entity, $dataFromAdditionalColumns, "unexpected value"]
        ]));

        /** @var mixed $actualValue */
        $actualValue = $this->valueResolver->resolveValue(
            $fieldMapping,
            $entity,
            $dataFromAdditionalColumns
        );

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * @test
     */
    public function canRevertAChoice()
    {
        $serviceMappingFoo = new ServiceMapping("foo_service");
        $serviceMappingBar = new ServiceMapping("bar_service");

        $fieldMapping = new ChoiceMapping("some_column", [
            'foo' => $serviceMappingFoo,
            'bar' => $serviceMappingBar
        ]);

        $entity = new EntityExample();

        /** @var scalar $valueFromEntityField */
        $valueFromEntityField = 'lorem ipsum';

        /** @var array<scalar> $expectedValue */
        $expectedValue = [
            "some_column" => 'bar'
        ];

        $this->innerValueResolver->method('resolveValue')->will($this->returnValueMap([
            [$serviceMappingFoo, $entity, [], "unexpected value"],
            [$serviceMappingBar, $entity, [], "lorem ipsum"]
        ]));

        /** @var mixed $actualValue */
        $actualValue = $this->valueResolver->revertValue(
            $fieldMapping,
            $entity,
            $valueFromEntityField
        );

        $this->assertEquals($expectedValue, $actualValue);
    }

    /**
     * @test
     */
    public function assertsChosenValue()
    {
        $serviceMappingFoo = new ServiceMapping("foo_service");
        $serviceMappingBar = new ServiceMapping("bar_service");

        $fieldMapping = new ChoiceMapping("some_column", [
            'foo' => $serviceMappingFoo,
            'bar' => $serviceMappingBar
        ]);

        $entity = new EntityExample();

        /** @var scalar $valueFromEntityField */
        $valueFromEntityField = 'lorem ipsum';

        /** @var array<scalar> $dataFromAdditionalColumns */
        $dataFromAdditionalColumns = array(
            'lorem' => 'ipsum',
            'some_column' => 'foo',
        );

        /** @var scalar $actualValue */
        $actualValue = "some actual value";

        $this->innerValueResolver->expects($this->once())->method('assertValue')->with(
            $this->equalTo($serviceMappingFoo),
            $this->equalTo($entity),
            $this->equalTo($dataFromAdditionalColumns),
            $this->equalTo($actualValue)
        );

        $this->valueResolver->assertValue(
            $fieldMapping,
            $entity,
            $dataFromAdditionalColumns,
            $actualValue
        );
    }

}