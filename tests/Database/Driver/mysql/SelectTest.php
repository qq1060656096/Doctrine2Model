<?php
namespace Wei\Base\Tests\Database\Driver\mysql;


use Wei\Base\Database\Driver\mysql\Select;
use Wei\Base\Database\Query\ConnectionFactor;
use Wei\Base\Tests\WeiTestCase;

class SelectTest extends WeiTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub

        ConnectionFactor::getInstance('default')->delete('test', ['name' => '20170517--1149']);
        $values = [
            'name' => '20170517--1149',
            'age' => 1601,
            'uid' => 1602,
            'created' => '2017-05-17 11:49',
        ];
        ConnectionFactor::getInstance()->insert('test', $values);


        self::setFindAllFixture();
    }

    /**
     * 查询所有基镜
     */
    public static function setFindAllFixture()
    {
        ConnectionFactor::getInstance()->delete('test', ['name' => 'SelectTest::testFindAll-20170518-1256']);
        ConnectionFactor::getInstance()->delete('test', ['name' => 'SelectTest::testFindAll-20170518-125601']);
        ConnectionFactor::getInstance()->delete('test', ['name' => 'SelectTest::testFindAll-20170518-125602']);
        $values = [
            'name' => 'SelectTest::testFindAll-20170518-1256',
            'age' => 125601,
            'uid' => 1256011,
            'created' => '2017-05-18 12:56:00',
        ];
        ConnectionFactor::getInstance()->insert('test', $values);
        $values = [
            'name' => 'SelectTest::testFindAll-20170518-125601',
            'age' => 125602,
            'uid' => 1256022,
            'created' => '2017-05-18 12:56:00',
        ];
        ConnectionFactor::getInstance()->insert('test', $values);
        $values = [
            'name' => 'SelectTest::testFindAll-20170518-125602',
            'age' => 125603,
            'uid' => 1256033,
            'created' => '2017-05-18 12:56:00',
        ];
        ConnectionFactor::getInstance()->insert('test', $values);
    }



    /**
     * 测试查询字段
     */
    public function testFields()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->fields(['id', 'name']);
        $this->assertEquals(['id', 'name'], $obj->getFields());

        //添加查询字段
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->fields('id,name,age');
        $this->assertEquals(['id', 'name', 'age'], $obj->getFields());
        $obj->addFields(['uid','created']);
        $this->assertEquals(['id', 'name', 'age', 'uid', 'created'], $obj->getFields());
    }

    /**
     * 测试关联查询
     */
    public function testJoin()
    {
        // 内连接
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->innerJoin('test test2', 'test.id = test2.id');
        $join[] = [
            'type' => 'INNER JOIN',
            'table' => 'test test2',
            'condition' => 'test.id = test2.id',
            'arguments' => []
        ];
        $this->assertEquals($join, $obj->getJoin());

        // 左连接
        $join[] = [
            'type' => 'LEFT JOIN',
            'table' => 'left',
            'condition' => 'left.id = ?',
            'arguments' => ['test.id']
        ];
        $obj->leftJoin('left', 'left.id = ?', ['test.id']);
        $this->assertEquals($join, $obj->getJoin());

        // 右连接
        $join[] = [
            'type' => 'RIGHT JOIN',
            'table' => 'right',
            'condition' => 'right.id = ?',
            'arguments' => ['left.id']
        ];
        $obj->rightJoin('right', 'right.id = ?', ['left.id']);
        $this->assertEquals($join, $obj->getJoin());
    }

    /**
     * 测试排序
     */
    public function testOrder()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->orderBy('id');
        $obj->orderBy('name','DESC');
        $data = [
            'id' => 'ASC',
            'name' => 'DESC'
        ];
        $this->assertEquals($data, $obj->getOrderBy());
    }

    /**
     * 测试分组
     */
    public function testGroup()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->groupBy('id');
        $obj->groupBy('name');
        $data = [
            'id' => 'id',
            'name' => 'name',
        ];
        $this->assertEquals($data, $obj->getGroupBy());
    }

    /**
     * 测试offset
     */
    public function testOff()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->offset(20);
        $this->assertEquals('20', $obj->getOffset());
        $obj->offset(0);
        $this->assertEquals('0', $obj->getOffset());
        $obj->offset(-1);
        $this->assertEquals('0', $obj->getOffset());
    }
    /**
     * 返回多少条数据
     */
    public function testLimit()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->limit(20);
        $this->assertEquals('20', $obj->getLimit());
        $obj->limit(0);
        $this->assertEquals('0', $obj->getLimit());
        $obj->limit(-1);
        $this->assertEquals('0', $obj->getLimit());
    }

    /**
     * 测试编译
     */
    public function testCompile()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->compile();
        $this->assertEquals('test', $obj->getQueryArr()['table']);
        $queryArr = [
            'select' => 'SELECT',
            'field' => '*',
            'from'  => 'FROM',
            'table' => 'test'
        ];
        $this->assertEquals($queryArr, $obj->getQueryArr());
    }

    /**
     * 常用测试
     */
    public function testSelect()
    {

        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->condition('name', 't1');
        $obj->condition('age', 20);
        $obj->compile();
        $this->assertEquals('WHERE name = ? AND age = ?', $obj->getQueryArr()['where']);

        $obj->limit(10);
        $obj->compile();
        $this->assertEquals('LIMIT 10', $obj->getQueryArr()['limit']);

        $obj->offset(5);
        $obj->compile();
        $this->assertEquals('LIMIT 5,10', $obj->getQueryArr()['limit']);

        $obj->groupBy('name');
        $obj->groupBy('age');
        $obj->compile();
        $this->assertEquals('GROUP BY name,age', $obj->getQueryArr()['group']);

        $obj->orderBy('id');
        $obj->orderBy('age', 'DESC');
        $obj->compile();
        $this->assertEquals('ORDER BY id ASC,age DESC', $obj->getQueryArr()['order']);
//        print_r($obj->getQueryArr());
    }

    /**
     * 测试查询所有(findAll和findCount)
     *
     */
    public function testFindAllAndFindCount()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->condition('name', 'SelectTest::testFindAll-20170518-1256%', 'like');
        ConnectionFactor::enabledSqlLog();
        $count = $obj->findCount();

        $this->assertEquals('3', $count);
        $result = $obj->findAll();
        $this->assertEquals('3', count($result));


        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->condition('name', 'SelectTest::testFindAll-20170518-1256%', 'like');
        $obj->offset(2);
        $obj->limit(1);
        $count = $obj->findCount();
        $this->assertEquals('3', $count);
        $obj->orderBy('id');
        $result = $obj->findAll();
        $this->assertEquals('1', count($result));

        $values = [
            'name' => 'SelectTest::testFindAll-20170518-125602',
            'age' => 125603,
            'uid' => 1256033,
            'created' => '2017-05-18 12:56:00',
        ];
        unset($result[0]['id']);
        $this->assertEquals($values, $result[0]);
    }

    /**
     * 测试查询单条数据
     */
    public function testFindOne()
    {
        $obj = new Select(ConnectionFactor::getInstance(), 'test');
        $obj->condition('name', 'SelectTest::testFindAll-20170518-1256%', 'like');
        $obj->limit(3);
        $obj->groupBy('name');
        $obj->orderBy('id', 'DESC');
        ConnectionFactor::enabledSqlLog();
        $result = $obj->findOne();
        unset($result['id']);
        $values = [
            'name' => 'SelectTest::testFindAll-20170518-125602',
            'age' => 125603,
            'uid' => 1256033,
            'created' => '2017-05-18 12:56:00',
        ];
        $this->assertEquals($values, $result);
    }

    /**
     * 测试关联查询
     */
    public function testJoinSelect()
    {
        ConnectionFactor::enabledSqlLog();
        $obj = new Select(ConnectionFactor::getInstance(), 'test t1');
        $obj->condition('t2.name', 'SelectTest::testFindAll-20170518-1256%', 'like');
        $obj->fields('*');
        $obj->leftJoin('test t2', 'on t2.id = t1.id');
        $obj->rightJoin('test t3', 'on t3.id = t2.id');
        $obj->innerJoin('test t4', 'on t4.id = t3.id');
        $obj->findAll();

        $sql = "SELECT * FROM test t1 LEFT JOIN test t2 on t2.id = t1.id RIGHT JOIN test t3 on t3.id = t2.id INNER JOIN test t4 on t4.id = t3.id WHERE t2.name LIKE 'SelectTest::testFindAll-20170518-1256%'";
        $this->assertEquals($sql, ConnectionFactor::getLastRawSql()['rawSql']);
    }




}