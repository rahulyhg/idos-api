<?php
/**
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Handler;

use App\Command\Company\CreateNew;
use App\Command\Company\DeleteOne;
use App\Factory\Repository;
use App\Factory\Validator;
use App\Handler\Company;
use App\Entity\Company as CompanyEntity;
use App\Factory\Entity as EntityFactory;
use App\Repository\CompanyInterface;
use App\Repository\DBCompany;
use App\Validator\Company as CompanyValidator;
use Slim\Container;

class CompanyTest extends \PHPUnit_Framework_TestCase {
    public function testConstructCorrectInterface() {
        $repositoryMock = $this
            ->getMockBuilder(CompanyInterface::class)
            ->getMock();
        $validatorMock = $this
            ->getMockBuilder(CompanyValidator::class)
            ->getMock();

        $this->assertInstanceOf(
            'App\\Handler\\HandlerInterface',
            new Company(
                $repositoryMock,
                $validatorMock
            )
        );
    }

    public function testRegister() {
        $container = new Container();

        $repositoryMock = $this
            ->getMockBuilder(CompanyInterface::class)
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
            ->getMockBuilder(CompanyValidator::class)
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

        Company::register($container);
        $this->assertInstanceOf(Company::class, $container[Company::class]);
    }

    public function testHandleCreateNewInvalidCompanyName() {
        $repositoryMock = $this
            ->getMockBuilder(CompanyInterface::class)
            ->getMock();

        $handler = new Company(
            $repositoryMock,
            new CompanyValidator()
        );
        $this->setExpectedException('InvalidArgumentException');

        $commandMock = $this
            ->getMockBuilder(CreateNew::class)
            ->getMock();
        $commandMock->name = '';

        $handler->handleCreateNew($commandMock);
    }

    public function testHandleCreateNew() {
        $dbConnectionMock = $this->getMock('Illuminate\Database\ConnectionInterface');

        $entityFactory = new EntityFactory();
        $entityFactory->create("Company");

        $companyRepository = $this->getMockBuilder(DBCompany::class)
            ->setMethods(['save'])
            ->setConstructorArgs([$entityFactory, $dbConnectionMock])
            ->getMock();
        $companyRepository
            ->expects($this->once())
            ->method('save')
            ->willReturn(true);

        $handler = new Company(
            $companyRepository,
            new CompanyValidator()
        );

        $command           = new CreateNew();
        $command->name     = 'valid co';
        $command->parentId = 1;

        $result = $handler->handleCreateNew($command);
        $this->assertSame('valid co', $result->name);
        $this->assertSame('valid-co', $result->slug);
        $this->assertNotEmpty($result->public_key);
    }

    public function testHandleDeleteOneInvalidCompanySlug() {
        $repositoryMock = $this
            ->getMockBuilder(CompanyInterface::class)
            ->getMock();

        $handler = new Company(
            $repositoryMock,
            new CompanyValidator()
        );

        $this->setExpectedException('InvalidArgumentException');

        $commandMock = $this
            ->getMockBuilder(DeleteOne::class)
            ->disableOriginalConstructor()
            ->getMock();
        $commandMock->companySlug = '';

        $handler->handleDeleteOne($commandMock);
    }
}
