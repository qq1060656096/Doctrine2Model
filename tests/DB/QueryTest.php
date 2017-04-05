<?php
namespace Wei\Base\Tests\DB;

use Wei\Base\DB\Query;
use Wei\Base\Tests\WeiTestCase;

class QueryTest extends WeiTestCase
{
    public function setUp()
    {
        $query = new Query();
        $query->from('test');
        $query->insert([
            'name' => '20170406--0016',
            'age' => 20,
            'uid' => '2017040601--0016'
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
     * 测试最小查询
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

        $row = $query->one();
        $this->assertEquals("select min(id) from test where `name` = '20170406--0016' order by id asc", $debugSql['rawSql']);
        $this->assertEquals($row['id'], $result);
    }

    public function testMax()
    {

    }

}