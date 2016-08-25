<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Repository;

use App\Entity\Feature as FeatureEntity;
use App\Factory\Entity;
use App\Repository\DBFeature;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Jenssegers\Optimus\Optimus;
use Test\Unit\AbstractUnit;

class DBFeatureTest extends AbstractUnit {
    /*
     * Jenssengers\Optimus\Optimus $optimus
     */
    private $optimus;

    public function setUp() {
        $this->optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testUpdate() {
        $factory = new Entity($this->optimus);
        $factory->create('Feature', []);

        $queryMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['where', 'update'])
            ->getMock();

        $queryMock
            ->method('where')
            ->will($this->returnValue($queryMock));

        $queryMock
            ->method('update')
            ->will($this->returnValue(1));

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFetchMode', 'table'])
            ->getMock();

        $connectionMock
            ->method('setFetchMode')
            ->will($this->returnValue([1]));

        $connectionMock
            ->method('table')
            ->will($this->returnValue($queryMock));

        $dbFeature = new DBFeature($factory, $this->optimus, $connectionMock);

        $featureEntity = new FeatureEntity(['user_id' => $userId], $this->optimus);

        $this->assertSame(1, $dbFeature->update($featureEntity));
    }

    public function testGetAllByUserIdUnfiltered() {
        $factory = new Entity($this->optimus);
        $factory->create('Feature', []);

        $queryMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['where'])
            ->getMock();

        $queryMock
            ->method('where')
            ->will($this->returnValue($queryMock));

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFetchMode', 'table'])
            ->getMock();

        $connectionMock
            ->method('setFetchMode')
            ->will($this->returnValue([1]));

        $connectionMock
            ->method('table')
            ->will($this->returnValue($queryMock));

        $dbFeatureMock = $this->getMockBuilder(DBFeature::class)
            ->setConstructorArgs([$factory, $this->optimus, $connectionMock])
            ->setMethods(['filter', 'paginate'])
            ->getMock();

        $dbFeatureMock
            ->method('filter')
            ->will($this->returnValue($queryMock));

        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedResult = [
            'pagination' => [
                'total'        => 1,
                'per_page'     => 1,
                'current_page' => 1,
                'last_page'    => 1,
                'from'         => 0,
                'to'           => 1,
            ],
            'collection' => $collectionMock
        ];

        $dbFeatureMock
            ->method('paginate')
            ->will($this->returnValue($expectedResult));

        $this->assertSame($expectedResult, $dbFeatureMock->getAllByUserId(1, []));
    }

    public function testGetAllByUserIdFiltered() {
        $factory = new Entity($this->optimus);
        $factory->create('Feature', []);

        $queryMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->setMethods(['where'])
            ->getMock();

        $queryMock
            ->method('where')
            ->will($this->returnValue($queryMock));

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFetchMode', 'table'])
            ->getMock();

        $connectionMock
            ->method('setFetchMode')
            ->will($this->returnValue([1]));

        $connectionMock
            ->method('table')
            ->will($this->returnValue($queryMock));

        $dbFeatureMock = $this->getMockBuilder(DBFeature::class)
            ->setConstructorArgs([$factory, $this->optimus, $connectionMock])
            ->setMethods(['filter', 'paginate'])
            ->getMock();

        $dbFeatureMock
            ->method('filter')
            ->will($this->returnValue($queryMock));

        $collectionMock = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();

        $expectedResult = [
            'pagination' => [
                'total'        => 1,
                'per_page'     => 1,
                'current_page' => 1,
                'last_page'    => 1,
                'from'         => 0,
                'to'           => 1,
            ],
            'collection' => $collectionMock
        ];

        $dbFeatureMock
            ->method('paginate')
            ->will($this->returnValue($expectedResult));

        $this->assertSame($expectedResult, $dbFeatureMock->getAllByUserId(1, ['slug' => 'new-test']));
    }

    public function testDeleteByUserId() {
        $factory = new Entity($this->optimus);
        $factory->create('Feature', []);

        $queryMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFetchMode', 'table'])
            ->getMock();

        $connectionMock
            ->method('setFetchMode')
            ->will($this->returnValue([1]));

        $connectionMock
            ->method('table')
            ->will($this->returnValue($queryMock));

        $dbFeatureMock = $this->getMockBuilder(DBFeature::class)
            ->setConstructorArgs([$factory, $this->optimus, $connectionMock])
            ->setMethods(['deleteByKey'])
            ->getMock();

        $amount = 1;
        $dbFeatureMock
            ->method('deleteByKey')
            ->will($this->returnValue($amount));

        $this->assertSame($amount, $dbFeatureMock->deleteByUserId(1));
    }

    public function testFindByUserId() {
        $factory = new Entity($this->optimus);
        $factory->create('Feature', []);

        $queryMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFetchMode', 'table'])
            ->getMock();

        $connectionMock
            ->method('setFetchMode')
            ->will($this->returnValue([1]));

        $connectionMock
            ->method('table')
            ->will($this->returnValue($queryMock));

        $dbFeatureMock = $this->getMockBuilder(DBFeature::class)
            ->setConstructorArgs([$factory, $this->optimus, $connectionMock])
            ->setMethods(['findBy'])
            ->getMock();

        $expectedArray = [
            [
                'id'         => 1,
                'name'       => 'Test name',
                'slug'       => 'test-name',
                'value'      => 'Test value',
                'user_id'    => 1,
                'created_at' => time(),
                'updated_at' => null,
            ],
            [
                'id'         => 2,
                'name'       => 'New test',
                'slug'       => 'New-test',
                'value'      => 'New value',
                'user_id'    => 1,
                'created_at' => time(),
                'updated_at' => null,
            ]
        ];

        $expectedResult = new Collection($expectedArray);

        $dbFeatureMock
            ->method('findBy')
            ->will($this->returnValue($expectedResult));

        $this->assertSame($expectedResult, $dbFeatureMock->findByUserId(1));
    }

    public function testFindByUserIdAndSlug() {
        $factory = new Entity($this->optimus);
        $factory->create('Feature', []);

        $queryMock = $this->getMockBuilder(Builder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $connectionMock = $this->getMockBuilder(Connection::class)
            ->disableOriginalConstructor()
            ->setMethods(['setFetchMode', 'table'])
            ->getMock();

        $connectionMock
            ->method('setFetchMode')
            ->will($this->returnValue([1]));

        $connectionMock
            ->method('table')
            ->will($this->returnValue($queryMock));

        $dbFeatureMock = $this->getMockBuilder(DBFeature::class)
            ->setConstructorArgs([$factory, $this->optimus, $connectionMock])
            ->setMethods(['findBy'])
            ->getMock();

        $featureEntity = new FeatureEntity(
            [
                'name'    => 'Name test',
                'slug'    => 'name-test',
                'value'   => 'value',
                'user_id' => 1
            ],
            $this->optimus
        );

        $dbFeatureMock
            ->method('findByUserIdAndSlug')
            ->will($this->returnValue($featureEntity));

        $this->assertInstanceOf(FeatureEntity::class, $dbFeatureMock->findByUserIdAndSlug(1, 'name-test'));
    }
}
