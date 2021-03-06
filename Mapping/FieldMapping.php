<?php
/**
 * Copyright (C) 2018 Gerrit Addiks.
 * This package (including this file) was released under the terms of the GPL-3.0.
 * You should have received a copy of the GNU General Public License along with this program.
 * If not, see <http://www.gnu.org/licenses/> or send me a mail so i can send you a copy.
 *
 * @license GPL-3.0
 *
 * @author Gerrit Addiks <gerrit@addiks.de>
 */

namespace Addiks\RDMBundle\Mapping;

use Doctrine\DBAL\Schema\Column;
use Addiks\RDMBundle\Hydration\HydrationContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Types\Type;
use Doctrine\DBAL\Connection;
use Addiks\RDMBundle\Mapping\MappingInterface;
use Webmozart\Assert\Assert;
use Doctrine\DBAL\Platforms\AbstractPlatform;

final class FieldMapping implements MappingInterface
{

    /**
     * @var Column
     */
    private $dbalColumn;

    /**
     * @var string
     */
    private $origin;

    public function __construct(
        Column $dbalColumn,
        string $origin = "unknown"
    ) {
        $this->dbalColumn = $dbalColumn;
        $this->origin = $origin;
    }

    public function getDBALColumn(): Column
    {
        return $this->dbalColumn;
    }

    public function describeOrigin(): string
    {
        return $this->origin;
    }

    public function collectDBALColumns(): array
    {
        return [$this->dbalColumn];
    }

    public function resolveValue(
        HydrationContextInterface $context,
        array $dataFromAdditionalColumns
    ) {
        /** @var mixed $value */
        $value = null;

        /** @var Type $type */
        $type = $this->dbalColumn->getType();

        /** @var Connection $connection */
        $connection = $context->getEntityManager()->getConnection();

        /** @var AbstractPlatform $platform */
        $platform = $connection->getDatabasePlatform();

        /** @var string $columnName */
        $columnName = $this->dbalColumn->getName();

        if (isset($dataFromAdditionalColumns[$columnName])) {
            $value = $dataFromAdditionalColumns[$columnName];
            $value = $type->convertToPHPValue($value, $platform);

        } elseif (isset($dataFromAdditionalColumns[''])) {
            if (isset($dataFromAdditionalColumns[''][$columnName])) {
                $value = $dataFromAdditionalColumns[''][$columnName];
                $value = $type->convertToPHPValue($value, $platform);
            }
        }

        return $value;
    }

    public function revertValue(
        HydrationContextInterface $context,
        $valueFromEntityField
    ): array {
        /** @var mixed $data */
        $data = array();

        /** @var Type $type */
        $type = $this->dbalColumn->getType();

        /** @var Connection $connection */
        $connection = $context->getEntityManager()->getConnection();

        /** @var scalar|null $databaseValue */
        $databaseValue = $type->convertToDatabaseValue(
            $valueFromEntityField,
            $connection->getDatabasePlatform()
        );

        if (!is_null($databaseValue)) {
            Assert::scalar($databaseValue);
        }

        $data[$this->dbalColumn->getName()] = $databaseValue;

        return $data;
    }

    public function assertValue(
        HydrationContextInterface $context,
        array $dataFromAdditionalColumns,
        $actualValue
    ): void {
    }

    public function wakeUpMapping(ContainerInterface $container): void
    {
    }

}
