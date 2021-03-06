<?php

/**
 * @package    dev
 * @author     David Molineus <david.molineus@netzmacht.de>
 * @copyright  2016 netzmacht creative David Molineus
 * @license    LGPL 3.0
 * @filesource
 *
 */

namespace Netzmacht\Contao\QueryBuilder\Query;

use Aura\SqlQuery\Common\SubselectInterface;

/**
 * Class WherePlugin extends the where interface of an query.
 *
 * @package Netzmacht\Contao\QueryBuilder\Query
 */
trait WherePlugin
{
    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException If an invalid values argument is given.
     */
    public function whereIn($column, $values)
    {
        return $this->addWhereIn('where', $column, $values);
    }

    /**
     * {@inheritDoc}
     * @throws \InvalidArgumentException If an invalid values argument is given.
     */
    public function orWhereIn($column, $values)
    {
        return $this->addWhereIn('orWhere', $column, $values);
    }

    /**
     * Add an where in query.
     *
     * @param string                   $method Where query method. Valid values are where and orWhere.
     * @param string                   $column Query column.
     * @param array|SubselectInterface $values Values for in query.
     *
     * @throws \InvalidArgumentException If an invalid values argument is given.
     * @return $this
     */
    protected function addWhereIn($method, $column, $values)
    {
        if ($values instanceof SubselectInterface) {
            $condition = sprintf('%s IN(%s)', $column, $values->getStatement());
            $arguments = array_merge(
                [$condition],
                $values->getBindValues()
            );

            call_user_func_array([$this, $method], $arguments);

            return $this;
        }

        if (!is_array($values)) {
            throw new \InvalidArgumentException('Invalid values given. Expected array got ' . gettype($values));
        }

        if (count($values)) {
            $condition = sprintf(
                '%s IN (?%s)',
                $column,
                str_repeat(', ?', (count($values) - 1))
            );

            $arguments = array_merge(
                [$condition],
                $values
            );

            call_user_func_array([$this, $method], $arguments);
        }

        return $this;
    }

    /**
     * Build condition arguments.
     *
     * @param callable $callback Callable Where condition callback.
     *
     * @return array
     */
    protected function buildCallbackCondition(callable $callback)
    {
        /** @var Condition $condition */
        $condition = $this->factory->newCondition();

        call_user_func($callback, $condition);

        return array_merge(
            [$condition->getStatement()],
            $condition->getBindValues()
        );
    }
}
