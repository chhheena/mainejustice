<?php

namespace _JchOptimizeVendor\Laminas\Paginator\Adapter;

use Closure;
use _JchOptimizeVendor\Laminas\Db\Sql\Having;
use _JchOptimizeVendor\Laminas\Db\Sql\Where;
use _JchOptimizeVendor\Laminas\Db\TableGateway\AbstractTableGateway;
/**
 * @deprecated 2.10.0 Use the adapters in laminas/laminas-paginator-adapter-laminasdb.
 */
class DbTableGateway extends DbSelect
{
    /**
     * Constructs instance.
     *
     * @param null|Where|Closure|string|array $where
     * @param null|string|array                 $order
     * @param null|string|array                 $group
     * @param null|Having|Closure|string|array $having
     */
    public function __construct(AbstractTableGateway $tableGateway, $where = null, $order = null, $group = null, $having = null)
    {
        $sql = $tableGateway->getSql();
        $select = $sql->select();
        if ($where) {
            $select->where($where);
        }
        if ($order) {
            $select->order($order);
        }
        if ($group) {
            $select->group($group);
        }
        if ($having) {
            $select->having($having);
        }
        $resultSetPrototype = $tableGateway->getResultSetPrototype();
        parent::__construct($select, $sql, $resultSetPrototype);
    }
}
