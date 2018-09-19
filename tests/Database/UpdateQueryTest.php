<?php
/**
 * Spiral, Core Components
 *
 * @author Wolfy-J
 */
namespace Spiral\Database\Tests;

use Spiral\Database\Builder\QueryBuilder;
use Spiral\Database\Builder\UpdateQuery;
use Spiral\Database\Entity\Database;
use Spiral\Database\Helper\QueryInterpolator;
use Spiral\Database\Injection\ParameterInterface;
use Spiral\Database\Schema\Prototypes\AbstractTable;

abstract class UpdateQueryTest extends BaseQueryTest
{
    /**
     * @var Database
     */
    protected $database;

    public function setUp()
    {
        $this->database = $this->database();
    }

    public function schema(string $table): AbstractTable
    {
        return $this->database->table($table)->getSchema();
    }

    public function testQueryInstance()
    {
        $this->assertInstanceOf(UpdateQuery::class, $this->database->update());
        $this->assertInstanceOf(UpdateQuery::class, $this->database->table('table')->update());
        $this->assertInstanceOf(UpdateQuery::class, $this->database->table->update());
    }

    //Generic behaviours

    public function testSimpleUpdate()
    {
        $update = $this->database->update()->in('table')->set('name', 'Anton');

        $this->assertSameQuery("UPDATE {table} SET {name} = ?", $update);
    }

    public function testSimpleUpdateAsArray()
    {
        $update = $this->database->update()->in('table')->values(['name' => 'Anton']);

        $this->assertSameQuery("UPDATE {table} SET {name} = ?", $update);
    }

    public function testUpdateWithWhere()
    {
        $update = $this->database->update()->in('table')->set('name', 'Anton')->where('id', 1);

        $this->assertSameQuery("UPDATE {table} SET {name} = ? WHERE {id} = ?", $update);

        $this->assertSameParameters([
            'Anton',
            1
        ], $update);
    }

    protected function assertSameParameters(array $parameters, QueryBuilder $builder)
    {
        $builderParameters = [];
        foreach (QueryInterpolator::flattenParameters($builder->getParameters()) as $value) {
            $this->assertInstanceOf(ParameterInterface::class, $value);
            $this->assertFalse($value->isArray());

            $builderParameters[] = $value->getValue();
        }

        $this->assertEquals($parameters, $builderParameters);
    }
}