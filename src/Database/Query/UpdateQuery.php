<?php
/**
 * Simplex Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

namespace Simplex\Database\Query;

use Simplex\Database\Driver\Compiler;
use Simplex\Database\Driver\CompilerInterface;
use Simplex\Database\Driver\DriverInterface;
use Simplex\Database\Exception\BuilderException;
use Simplex\Database\Injection\FragmentInterface;
use Simplex\Database\Injection\ParameterInterface;
use Simplex\Database\Query\Traits\TokenTrait;
use Simplex\Database\Query\Traits\WhereTrait;

/**
 * Update statement builder.
 */
class UpdateQuery extends AbstractQuery
{
    use TokenTrait, WhereTrait;

    const QUERY_TYPE = Compiler::UPDATE_QUERY;

    /**
     * Every affect builder must be associated with specific table.
     *
     * @var string
     */
    protected $table = '';

    /**
     * Column names associated with their values.
     *
     * @var array
     */
    protected $values = [];

    /**
     * {@inheritdoc}
     *
     * @param array $values Initial set of column updates.
     */
    public function __construct(
        DriverInterface $driver,
        Compiler $compiler,
        string $table = null,
        array $where = [],
        array $values = []
    ) {
        parent::__construct($driver, $compiler);

        $this->table = $table ?? '';
        $this->values = $values;

        if (!empty($where)) {
            $this->where($where);
        }
    }

    /**
     * Change target table.
     *
     * @param string $table Table name without prefix.
     * @return self|$this
     */
    public function in(string $table): UpdateQuery
    {
        $this->table = $table;

        return $this;
    }

    /**
     * Change value set to be updated, must be represented by array of columns associated with new
     * value to be set.
     *
     * @param array $values
     * @return self|$this
     */
    public function values(array $values): UpdateQuery
    {
        $this->values = $values;

        return $this;
    }

    /**
     * Get list of columns associated with their values.
     *
     * @return array
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * Set update value.
     *
     * @param string $column
     * @param mixed  $value
     * @return self|$this
     */
    public function set(string $column, $value): UpdateQuery
    {
        $this->values[$column] = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getParameters(): array
    {
        $values = [];
        foreach ($this->values as $value) {
            if ($value instanceof BuilderInterface) {
                foreach ($value->getParameters() as $parameter) {
                    $values[] = $parameter;
                }

                continue;
            }

            if ($value instanceof FragmentInterface && !$value instanceof ParameterInterface) {
                //Apparently sql fragment
                continue;
            }

            $values[] = $value;
        }

        //Join and where parameters are going after values
        return $this->flattenParameters(array_merge($values, $this->whereParameters));
    }

    /**
     * {@inheritdoc}
     */
    public function sqlStatement(CompilerInterface $compiler = null): string
    {
        if (empty($this->values)) {
            throw new BuilderException('Update values must be specified');
        }

        if (empty($compiler)) {
            $compiler = clone $this->compiler;
        }

        return $compiler->compileUpdate($this->table, $this->values, $this->whereTokens);
    }

    /**
     * {@inheritdoc}
     *
     * Affect queries will return count of affected rows.
     *
     * @return int
     */
    public function run(): int
    {
        return $this->driver->execute($this->sqlStatement(), $this->getParameters());
    }
}
