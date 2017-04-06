<?php
namespace Wei\Base\Tests\DB;

use phpDocumentor\Reflection\DocBlock\Tags\Var_;
use Wei\Base\DB\Query;
use Wei\Base\Exception\BaseException;
use Wei\Base\Exception\LimitFrequencyException;
use Wei\Base\Tests\WeiTestCase;

class QueryTest extends WeiTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass(); // TODO: Change the autogenerated stub
        $query = new Query();
        $query->from('test');
        $query->where(['name' => '20170406--0016'])->delete();
        $query->where(['name' => '20170406--0950'])->delete();
        $query->where(['name' => '20170406--1051'])->delete();
        $query->where(['name' => '20170406--1439-avg'])->delete();
        self::setFixture();
        self::setFixtureForTestDelete();
    }



    /**
     * 通用基镜
     */
    public static function setFixture()
    {
        $query = new Query();
        $query->from('test');
        $query->insert([
            'name' => '20170406--0016',
            'age' => 20,
            'uid' => '20170406--0016',
            'created' => '2017-04-06 00:16:28',
        ]);

        $query->insert([
            'name' => '20170406--1051',
            'age' => 20,
            'uid' => '20170406--1051',
            'created' => '2017-04-06 10:51:28',
        ]);

        $query->insert([
            'name' => '20170406--1439-avg',
            'age' => 29,
            'uid' => '20170406--1439',
            'created' => '2017-04-06 14:39:28',
        ]);
        $query->insert([
            'name' => '20170406--1439-avg',
            'age' => 19,
            'uid' => '20170406--1439',
            'created' => '2017-04-06 14:39:28',
        ]);
    }
    /**
     * 测试查询字段[select]
     */
    public function testSelect()
    {
        $query = new Query();
        $this->assertEquals('*', $query->getSelect());

        $query->select(['id','name','age']);
        $this->assertEquals('id,name,age', $query->getSelect());

        $query->addSelect('create,change');
        $this->assertEquals('id,name,age,create,change', $query->getSelect());

        $query->addSelect(['uid', 'tid' => 'tid']);
        $this->assertEquals('id,name,age,create,change,uid,tid', $query->getSelect());

//        print_r($query->select);
        $query->select('*,id');
        $this->assertEquals('*,id', $query->getSelect());
    }

    /**
     * 测试[from]
     */
    public function testFrom()
    {
        $query = new Query();
        $query->from('wei_test');
        $this->assertEquals('wei_test', $query->getFrom());
    }

    /**
     * 测试空表名
     * @expectedException \Wei\Base\Exception\BaseException
     */
    public function testFormThrowException()
    {
        $query = new Query();
        $query->getFrom();
    }


    /**
     * 测试where条件
     */
    public function testWhere()
    {
        $query = new Query();
        $query->where('id=1 and name=2');
        $this->assertEquals(' where id=1 and name=2', $query->getWhere());

        $query->andWhere(['uid' => 3]);
        $this->assertEquals(" where id=1 and name=2 and uid = '3'", $query->getWhere());

        $query->andWhere("tid=4");
        $this->assertEquals(" where id=1 and name=2 and uid = '3' and tid=4", $query->getWhere());

        $query->orWhere("name like 't%'");
        $this->assertEquals(" where id=1 and name=2 and uid = '3' and tid=4 or name like 't%'", $query->getWhere());
        $where = [
            'name' => [
                'op' => 'like',
                'test%'
            ]
        ];
        $query->orWhere($where);
        $this->assertEquals(" where id=1 and name=2 and uid = '3' and tid=4 or name like 't%' or name like 'test%'", $query->getWhere());

        $where = [
            'id' => 3,
            'name' => [
                'on' => 'and',
                'op' => 'in',
                'rawValue' => "('test1','test2')",
            ],
            'age' => [
                'on' => 'or',
                'op' => '>=',
                '5',
            ],
            '`age`' => [
                'on' => 'or',
                'op' => '<=',
                'rawValue' => 10,
            ],
            'tid' => [
                'on' => 'OR',
                'op' => '<=',
                'rawValue' => 11,
            ]
        ];
        $query->where($where);
        $this->assertEquals(" where id = '3' and name in ('test1','test2') or age >= '5' or `age` <= 10 or tid <= 11", $query->getWhere());
//        var_dump($query->where);
    }

    /**
     * 测试where in
     */
    public function testWhereIn()
    {
        $query = new Query();

        $query->where('id=1 and name=2');
        $query->andWhere([
            'age' => [
                'op' => 'in',
                [20,30,40,50,100]
            ]
        ]);
        $this->assertEquals(" where id=1 and name=2 and age in ('20','30','40','50','100')", $query->getWhere());
    }

    /**
     * 测试分组查询[group by ]
     */
    public function testGroupBy()
    {
        $query = new Query();
        $query->groupBy(['name'=>'name','age','id']);
        $this->assertEquals(' group by name,age,id', $query->getGroupBy());

        $query->addGroupBy('tid,uid');
        $this->assertEquals(' group by name,age,id,tid,uid', $query->getGroupBy());

        $query->groupBy("age,name");
        $this->assertEquals(' group by age,name', $query->getGroupBy());
        $query->addGroupBy(['tid'=>'tid','uid']);
        $this->assertEquals(' group by age,name,tid,uid', $query->getGroupBy());
    }

    /**
     * 测试排序[order by]
     */
    public function testOrderBy()
    {
        $query = new Query();
        $query->orderBy([
            'age' => 'desc',
            'name' => 'asc',
            'uid' => 'desc',
        ]);
        $this->assertEquals(' order by age desc,name asc,uid desc', $query->getOrderBy());
        $query->addOrderBy('id desc, tid  desc');

        $this->assertEquals(' order by age desc,name asc,uid desc,id desc,tid  desc', $query->getOrderBy());

        $query->orderBy("id asc,tid desc");
        $this->assertEquals(' order by id asc,tid desc', $query->getOrderBy());

        $query->addOrderBy([
            'age' => 'desc',
            'uid' => 'desc',
        ]);
        $this->assertEquals(' order by id asc,tid desc,age desc,uid desc', $query->getOrderBy());
    }

    /**
     * 测试offset
     */
    public function testOff()
    {
        $query = new Query();
        $query->offset(20);
        $this->assertEquals('20', $query->offset);

        $query->offset(0);
        $this->assertEquals('0', $query->offset);

        $query->offset(-1);
        $this->assertEquals('0', $query->offset);
    }

    /**
     * 返回多少条数据
     */
    public function testLimit()
    {
        $query = new Query();
        $query->limit(20);
        $this->assertEquals('20', $query->limit);

        $query->limit(0);
        $this->assertEquals('0', $query->limit);

        $query->limit(-1);
        $this->assertEquals('0', $query->limit);
    }

    /**
     * 测试连接
     */
    public function testJoin()
    {
        $query = new Query();
        $query->join(Query::INNER_JOIN, 'test1','test.tid=demo.pid');
        $this->assertEquals(' inner join test1 on test.tid=demo.pid', $query->getJoin());

        $query->join(Query::LEFT_JOIN, 'demo2');
        $this->assertEquals(' inner join test1 on test.tid=demo.pid left join demo2', $query->getJoin());

        $query->join(Query::RIGHT_JOIN, 'demo3');
        $this->assertEquals(' inner join test1 on test.tid=demo.pid left join demo2 right join demo3', $query->getJoin());
    }

    /**
     * 内联查询
     */
    public function testInner()
    {
        $query = new Query();
        $query->innerJoin('test', 'test.did=demo.id');
        $this->assertEquals(' inner join test on test.did=demo.id', $query->getJoin());

        $query->innerJoin('demo2', 'demo.demo2_id=demo2.demo2_id');
        $this->assertEquals(' inner join test on test.did=demo.id inner join demo2 on demo.demo2_id=demo2.demo2_id', $query->getJoin());
    }

    /**
     * 左连查询
     */
    public function testLeftJoin()
    {
        $query = new Query();
        $query->leftJoin('test', 'test.did=demo.id');
        $this->assertEquals(' left join test on test.did=demo.id', $query->getJoin());

        $query->leftJoin('demo2', 'demo.lid=demo2.d2_lid');
        $this->assertEquals(' left join test on test.did=demo.id left join demo2 on demo.lid=demo2.d2_lid', $query->getJoin());
    }

    /**
     * 右连查询
     */
    public function testRightJoin()
    {
        $query = new Query();
        $query->rightJoin('test', 'test.did=demo.id');
        $this->assertEquals(' right join test on test.did=demo.id', $query->getJoin());

        $query->rightJoin('demo2', 'demo.rid=demo2.d2_rid');
        $this->assertEquals(' right join test on test.did=demo.id right join demo2 on demo.rid=demo2.d2_rid', $query->getJoin());
    }

    /**
     * 设置删除基镜
     */
    public static function setFixtureForTestDelete()
    {
        $query = new Query();
        $query->from('test');
        $query->insert([
            'name' => '20170406--0950',
            'age' => 20,
            'uid' => '20170406--0950',
            'created' => '2017-04-06 09:50',
        ]);

        $query->insert([
            'name' => '20170406--1041',
            'age' => 20,
            'uid' => '20170406--1041',
            'created' => '2017-04-06 10:41',
        ]);

        $query->insert([
            'name' => '20170406--1041',
            'age' => 20,
            'uid' => '20170406--1041',
            'created' => '2017-04-06 10:41',
        ]);
    }

    /**
     * 测试查询单行数据
     */
    public function testOne()
    {
        $query = new Query();
        $query->from('test');
        $query->where(['name' => '20170406--0950']);
        $query->enabledSqlLog();
        $result = $query->one();
        $this->assertEquals('20170406--0950', $result['name']);
        $this->assertEquals('20', $result['age']);
    }

    /**
     * 测试删除
     * @depends testOne
     */
    public function testDelete()
    {
        $query = new Query();
        $query->from('test');
        $query->enabledSqlLog();
        $query->where(['name' => '20170406--0950']);
        $result = $query->delete();
        $this->assertEquals('1', $result);


        $query = new Query();
        $query->from('test');
        $query->enabledSqlLog();
        $query->where([
            'name' => [
                '20170406--1041%',
                'op' => 'like'
            ]
        ]);
        $result = $query->delete();
        $this->assertEquals('2', $result);
    }
    /**
     * 测试最小查询
     * @depends testOne
     */
    public function testMin()
    {
        $query = new Query();
        $query->addSelect(['age','id']);
        $query->from('test');
        $query->addGroupBy(['name','age']);
        $query->orderBy(['id'=>'desc','age asc']);
        $this->assertEquals('select min(id) from test group by name,age order by id desc,age asc', $query->getColumnRawSqlPart('min(id)'));

        $query = new Query();
        $query->from('test');
        $query->where(['`name`' => '20170406--0016']);
        $query->orderBy(['id' => 'asc']);
        $query->enabledSqlLog();
        $result = $query->min('id');
        $debugSql = $query->getLastRawSql();
        $this->assertEquals("select min(id) from test where `name` = '20170406--0016' order by id asc", $debugSql['rawSql']);
        $row = $query->one();
        $this->assertEquals($row['id'], $result);
        $this->assertEquals('20170406--0016', $row['name']);
    }

    /**
     * 测试最大值
     * @depends testOne
     */
    public function testMax()
    {
        $query = new Query();
        $query->from('test');
        $query->where([
            '`name`' => [
                '20170406%',
                'op' => 'like'
            ]
        ]);
        $query->orderBy(['id' => 'desc']);

        $row = $query->one();

        $query->enabledSqlLog();
        $max = $query->max('id');
        $debugSql = $query->getLastRawSql();
        $this->assertEquals($max, $row['id']);

        $this->assertEquals("select max(id) from test where `name` like '20170406%' order by id desc", $debugSql['rawSql']);
    }

    /**
     * 测试总和
     *
     * @depends testOne
     */
    public function testSum()
    {
        $query = new Query();
        $query->from('test');
        $query->where([
            '`name`' => '20170406--1439-avg'
        ]);
        $result = $query->all();
        $arrAge = array_column($result, 'age');
        $sumAge = array_sum($arrAge);

        $query->enabledSqlLog();
        $sum = $query->sum('age');
        $debugSql = $query->getLastRawSql();
        $this->assertEquals('48', $sumAge);
        $this->assertEquals('48', $sum);
        $this->assertEquals("select sum(age) from test where `name` = '20170406--1439-avg'", $debugSql['rawSql']);
    }

    /**
     * 测试平均值
     */
    public function testAverage()
    {
        $query = new Query();
        $query->from('test');
        $query->where([
            '`name`' => '20170406--1439-avg'
        ]);
        $query->enabledSqlLog();
        $avg = $query->average('age');
        $debugSql = $query->getLastRawSql();
        $this->assertEquals("24", $avg);
        $this->assertEquals("select avg(age) from test where `name` = '20170406--1439-avg'", $debugSql['rawSql']);
    }

    /**
     * 测试统计
     */
    public function testCount()
    {
        $query = new Query();
        $query->from('test');
        $query->where([
            '`name`' => '20170406--1439-avg'
        ]);
        $query->enabledSqlLog();
        $result = $query->count();
        $debugSql = $query->getLastRawSql();
        $this->assertEquals("select count(*) from test where `name` = '20170406--1439-avg'", $debugSql['rawSql']);
        $this->assertEquals('2', $result);

        $result = $query->count('id');
        $debugSql = $query->getLastRawSql('id');
        $this->assertEquals("select count(id) from test where `name` = '20170406--1439-avg'", $debugSql['rawSql']);
        $this->assertEquals('2', $result);
    }
}