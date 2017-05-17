<?php
namespace Wei\Base\Database\Driver\mysql;

use Wei\Base\Exception\BaseException;
use Wei\Base\Exception\QueryException;

/**
 * mysql删除
 *
 * Class Delete
 * @package Wei\Base\Database\Driver\mysql
 */
class Delete extends \Wei\Base\Database\Query\Delete
{

    /**
     * 删除
     *
     * @return int
     * @throws BaseException
     */
    public function delete()
    {

        //没有设置条件不能删除
        if ($this->condition->count() < 1) {
            throw new BaseException(QueryException::DELETE_NOT_WHERE);
        }
        $whereStr       = (string)$this->condition->compile();
        $whereArguments = $this->condition->arguments();
        $sql            = "DELETE FROM {$this->getFrom()} WHERE ".$whereStr;
        return $this->connection->executeUpdate($sql, $whereArguments);
    }
}
