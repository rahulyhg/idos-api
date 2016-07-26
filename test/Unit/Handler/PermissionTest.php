<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Handler;

use App\Command\Permission\CreateNew;
use App\Command\Permission\DeleteOne;
use App\Factory\Entity as EntityFactory;
use App\Factory\Repository;
use App\Factory\Validator;
use App\Handler\Permission;
use App\Repository\DBPermission;
use App\Repository\PermissionInterface;
use App\Validator\Permission as PermissionValidator;
use Slim\Container;
use Test\Unit\AbstractUnit;
use App\Entity\Permission as PermissionEntity;

class PermissionTest extends AbstractUnit {
    public function testConstructCorrectInterface() {
        $repositoryMock = $this
            ->getMockBuilder(PermissionInterface::class)
            ->getMock();
        $validatorMock = $this
            ->getMockBuilder(PermissionValidator::class)
            ->getMock();

        $this->assertInstanceOf(
            'App\\Handler\\HandlerInterface',
            new Permission(
                $repositoryMock,
                $validatorMock
            )
        );
    }

    public function testRegister() {
        $container = new Container();

        $repositoryMock = $this
            ->getMockBuilder(PermissionInterface::class)
            ->getMock();

        $repositoryFactoryMock = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();
        $repositoryFactoryMock
            ->method('create')
            ->willReturn($repositoryMock);

        $container['repositoryFactory'] = function () use ($repositoryFactoryMock) {
            return $repositoryFactoryMock;
        };

        $validatorMock = $this
            ->getMockBuilder(PermissionValidator::class)
            ->getMock();

        $validatorFactoryMock = $this
            ->getMockBuilder(Validator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $validatorFactoryMock
            ->method('create')
            ->willReturn($validatorMock);

        $container['validatorFactory'] = function () use ($validatorFactoryMock) {
            return $validatorFactoryMock;
        };

        Permission::register($container);
        $this->assertInstanceOf(Permission::class, $container[Permission::class]);
    }

    public function testHandleCreateNewInvalidPermissionName() {
        $repositoryMock = $this
            ->getMockBuilder(PermissionInterface::class)
            ->getMock();

        $handler = new Permission(
            $repositoryMock,
            new PermissionValidator()
        );
        $this->setExpectedException('InvalidArgumentException');

        $commandMock = $this
            ->getMockBuilder(CreateNew::class)
            ->getMock();
        $commandMock->routeName = '';
        $commandMock->companyId = 1;

        $handler->handleCreateNew($commandMock);
    }

    public function testHandleCreateNew() {
        $permissionEntity = new PermissionEntity([]);

        $dbConnectionMock = $this->getMockBuilder('Illuminate\Database\ConnectionInterface')
            ->getMock();

        $entityFactory = new EntityFactory();
        $entityFactory->create('Permission');

        $permissionRepository = $this->getMockBuilder(DBPermission::class)
            ->setMethods(['save'])
            ->setConstructorArgs([$entityFactory, $dbConnectionMock])
            ->getMock();
        $permissionRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn($permissionEntity);

        $handler = new Permission(
            $permissionRepository,
            new PermissionValidator()
        );

        $command            = new CreateNew();
        $command->routeName = 'companies:listAll';
        $command->companyId = 1;

        $result = $handler->handleCreateNew($command);

        $this->assertSame('companies:listAll', $result->route_name);
        $this->assertSame(1, $result->company_id);
    }

    public function testHandleDeleteOneInvalidRouteName() {
        $repositoryMock = $this
            ->getMockBuilder(PermissionInterface::class)
            ->getMock();

        $handler = new Permission(
            $repositoryMock,
            new PermissionValidator()
        );

        $this->setExpectedException('InvalidArgumentException');

        $commandMock = $this
            ->getMockBuilder(DeleteOne::class)
            ->disableOriginalConstructor()
            ->getMock();

        // not a valid routeName (less than 5 chars)
        $commandMock->routeName = '';
        $commandMock->companyId = 1;

        $handler->handleDeleteOne($commandMock);
    }
}
