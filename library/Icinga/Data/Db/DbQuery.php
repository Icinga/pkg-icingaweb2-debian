<?php
/* Icinga Web 2 | (c) 2013-2015 Icinga Development Team | http://www.gnu.org/licenses/gpl-2.0.txt */

namespace Icinga\Data\Db;

use Icinga\Data\SimpleQuery;
use Icinga\Application\Benchmark;
use Icinga\Data\Filter\FilterChain;
use Icinga\Data\Filter\FilterExpression;
use Icinga\Data\Filter\FilterOr;
use Icinga\Data\Filter\FilterAnd;
use Icinga\Data\Filter\FilterNot;
use Icinga\Exception\IcingaException;
use Zend_Db_Select;

/**
 * Database query class
 */
class DbQuery extends SimpleQuery
{
    /**
     * @var Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * Select query
     *
     * @var Zend_Db_Select
     */
    protected $select;

    /**
     * Whether to use a subquery for counting
     *
     * When the query is distinct or has a HAVING or GROUP BY clause this must be set to true
     *
     * @var bool
     */
    protected $useSubqueryCount = false;

    /**
     * Set the count maximum
     *
     * If the count maximum is set, count queries will not count more than that many rows. You should set this
     * property only for really heavy queries.
     *
     * @var int
     */
    protected $maxCount;

    /**
     * Count query result
     *
     * Count queries are only executed once
     *
     * @var int
     */
    protected $count;

    /**
     * GROUP BY clauses
     *
     * @var string|array
     */
    protected $group;

    protected function init()
    {
        $this->db = $this->ds->getDbAdapter();
        parent::init();
    }

    public function setUseSubqueryCount($useSubqueryCount = true)
    {
        $this->useSubqueryCount = $useSubqueryCount;
        return $this;
    }

    public function where($condition, $value = null)
    {
        // $this->count = $this->select = null;
        return parent::where($condition, $value);
    }

    protected function dbSelect()
    {
        if ($this->select === null) {
            $this->select = $this->db->select()->from($this->target, array());
        }
        return clone $this->select;
    }

    /**
     * Get the select query
     *
     * Applies order and limit if any
     *
     * @return Zend_Db_Select
     */
    public function getSelectQuery()
    {
        $select = $this->dbSelect();
        // Add order fields to select for postgres distinct queries (#6351)
        if ($this->hasOrder()
            && $this->getDatasource()->getDbType() === 'pgsql'
            && $select->getPart(Zend_Db_Select::DISTINCT) === true) {
            foreach ($this->getOrder() as $fieldAndDirection) {
                if (array_search($fieldAndDirection[0], $this->columns) === false) {
                    $this->columns[] = $fieldAndDirection[0];
                }
            }
        }

        if ($this->group) {
            $select->group($this->group);
        }

        $select->columns($this->columns);
        $this->applyFilterSql($select);

        if ($this->hasLimit() || $this->hasOffset()) {
            $select->limit($this->getLimit(), $this->getOffset());
        }
        if ($this->hasOrder()) {
            foreach ($this->getOrder() as $fieldAndDirection) {
                $select->order(
                    $fieldAndDirection[0] . ' ' . $fieldAndDirection[1]
                );
            }
        }

        return $select;
    }

    protected function applyFilterSql($select)
    {
        $where = $this->renderFilter($this->filter);
        if ($where !== '') {
            $select->where($where);
        }
    }

    protected function renderFilter($filter, $level = 0)
    {
        $str = '';
        if ($filter instanceof FilterChain) {
            if ($filter instanceof FilterAnd) {
                $op = ' AND ';
            } elseif ($filter instanceof FilterOr) {
                $op = ' OR ';
            } elseif ($filter instanceof FilterNot) {
                $op = ' AND ';
                $str .= ' NOT ';
            } else {
                throw new IcingaException(
                    'Cannot render filter: %s',
                    $filter
                );
            }
            $parts = array();
            if (! $filter->isEmpty()) {
                foreach ($filter->filters() as $f) {
                    $filterPart = $this->renderFilter($f, $level + 1);
                    if ($filterPart !== '') {
                        $parts[] = $filterPart;
                    }
                }
                if ($level > 0) {
                    $str .= ' (' . implode($op, $parts) . ') ';
                } else {
                    $str .= implode($op, $parts);
                }
            }
        } else {
            $str .= $this->whereToSql($filter->getColumn(), $filter->getSign(), $filter->getExpression());
        }

        return $str;
    }

    protected function escapeForSql($value)
    {
        // bindParam? bindValue?
        if (is_array($value)) {
            $ret = array();
            foreach ($value as $val) {
                $ret[] = $this->escapeForSql($val);
            }
            return implode(', ', $ret);
        } else {
            //if (preg_match('/^\d+$/', $value)) {
            //    return $value;
            //} else {
                return $this->db->quote($value);
            //}
        }
    }

    protected function escapeWildcards($value)
    {
        return preg_replace('/\*/', '%', $value);
    }

    protected function valueToTimestamp($value)
    {
        // We consider integers as valid timestamps. Does not work for URL params
        if (ctype_digit($value)) {
            return $value;
        }
        $value = strtotime($value);
        if (! $value) {
            /*
            NOTE: It's too late to throw exceptions, we might finish in __toString
            throw new IcingaException(sprintf(
                '"%s" is not a valid time expression',
                $value
            ));
            */
        }
        return $value;
    }

    protected function timestampForSql($value)
    {
        // TODO: do this db-aware
        return $this->escapeForSql(date('Y-m-d H:i:s', $value));
    }

    /**
     * Check for timestamp fields
     *
     * TODO: This is not here to do automagic timestamp stuff. One may
     *       override this function for custom voodoo, IdoQuery right now
     *       does. IMO we need to split whereToSql functionality, however
     *       I'd prefer to wait with this unless we understood how other
     *       backends will work. We probably should also rename this
     *       function to isTimestampColumn().
     *
     * @param  string $field Field Field name to checked
     * @return bool          Whether this field expects timestamps
     */
    public function isTimestamp($field)
    {
        return false;
    }

    public function whereToSql($col, $sign, $expression)
    {
        if ($this->isTimestamp($col)) {
            $expression = $this->valueToTimestamp($expression);
        }
        if (is_array($expression) && $sign === '=') {
            // TODO: Should we support this? Doesn't work for blub*
            return $col . ' IN (' . $this->escapeForSql($expression) . ')';
        } elseif ($sign === '=' && strpos($expression, '*') !== false) {
            return $col . ' LIKE ' . $this->escapeForSql($this->escapeWildcards($expression));
        } elseif ($sign === '!=' && strpos($expression, '*') !== false) {
            return $col . ' NOT LIKE ' . $this->escapeForSql($this->escapeWildcards($expression));
        } else {
            return $col . ' ' . $sign . ' ' . $this->escapeForSql($expression);
        }
    }

    /**
     * Get the count query
     *
     * @return Zend_Db_Select
     */
    public function getCountQuery()
    {
        // TODO: there may be situations where we should clone the "select"
        $count = $this->dbSelect();
        if ($this->group) {
            $count->group($this->group);
        }
        $this->applyFilterSql($count);
        if ($this->useSubqueryCount || $this->group) {
            $count->columns($this->columns);
            $columns = array('cnt' => 'COUNT(*)');
            return $this->db->select()->from($count, $columns);
        }
        if ($this->maxCount !== null) {
            return $this->db->select()->from($count->limit($this->maxCount));
        }

        $count->columns(array('cnt' => 'COUNT(*)'));
        return $count;
    }

    /**
     * Count all rows of the result set
     *
     * @return int
     */
    public function count()
    {
        if ($this->count === null) {
            Benchmark::measure('DB is counting');
            $this->count = $this->db->fetchOne($this->getCountQuery());
            Benchmark::measure('DB finished count');
        }
        return $this->count;
    }

    /**
     * Return the select and count query as a textual representation
     *
     * @return string A string containing the select and count query, using unix style newlines as linebreaks
     */
    public function dump()
    {
        return "QUERY\n=====\n"
        . $this->getSelectQuery()
        . "\n\nCOUNT\n=====\n"
        . $this->getCountQuery()
        . "\n\n";
    }

    public function __clone()
    {
        if ($this->select) {
            $this->select = clone $this->select;
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return (string) $this->getSelectQuery();
    }

    /**
     * Add a GROUP BY clause
     *
     * @param   string|array $group
     *
     * @return  $this
     */
    public function group($group)
    {
        $this->group = $group;
        return $this;
    }
}
