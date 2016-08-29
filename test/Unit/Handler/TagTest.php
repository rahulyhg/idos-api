<?php
/*
 * Copyright (c) 2012-2016 Veridu Ltd <https://veridu.com>
 * All rights reserved.
 */

namespace Test\Unit\Handler;

use App\Command\Tag\CreateNew;
use App\Command\Tag\DeleteAll;
use App\Command\Tag\DeleteOne;
use App\Entity\Tag as TagEntity;
use App\Entity\User as UserEntity;
use App\Factory\Entity as EntityFactory;
use App\Factory\Repository;
use App\Factory\Validator;
use App\Handler\Tag;
use App\Repository\CredentialInterface;
use App\Repository\DBTag;
use App\Repository\DBUser;
use App\Repository\TagInterface;
use App\Repository\UserInterface;
use App\Validator\Tag as TagValidator;
use Illuminate\Support\Collection;
use Jenssegers\Optimus\Optimus;
use League\Event\Emitter;
use Slim\Container;
use Test\Unit\AbstractUnit;

class TagTest extends AbstractUnit {
    /*
     * Jenssengers\Optimus\Optimus $optimus
     */
    private $optimus;

    public function setUp() {
        $this->optimus = $this->getMockBuilder(Optimus::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getEntity() {
        return new TagEntity(
            [
                'user_id'    => 1,
                'name'       => 'Tag Test',
                'slug'       => 'tag-test',
                'created_at' => time(),
                'updated_at' => time()
            ],
            $this->optimus
        );
    }
    private function getUserEntity() {
        return new UserEntity(
            [
                'id'         => 1,
                'username'   => 'userName',
                'created_at' => time(),
                'updated_at' => time()
            ],
            $this->optimus
        );
    }

    public function testConstructCorrectInterface() {
        $repositoryMock = $this
            ->getMockBuilder(TagInterface::class)
            ->getMock();

        $userRepositoryMock = $this
            ->getMockBuilder(UserInterface::class)
            ->getMock();

        $validatorMock = $this
            ->getMockBuilder(TagValidator::class)
            ->getMock();

        $emitterMock = $this
            ->getMockBuilder(Emitter::class)
            ->getMock();

        $this->assertInstanceOf(
            'App\\Handler\\HandlerInterface',
            new Tag(
                $repositoryMock,
                $userRepositoryMock,
                $validatorMock,
                $emitterMock
            )
        );
    }

    public function testRegister() {
        $container = new Container();

        $repositoryMock = $this
            ->getMockBuilder(TagInterface::class)
            ->getMock();

        $userRepositoryMock = $this
            ->getMockBuilder(UserInterface::class)
            ->getMock();

        $repositoryFactoryMock = $this
            ->getMockBuilder(Repository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $repositoryFactoryMock
            ->expects($this->exactly(2))
            ->method('create')
            ->will($this->onConsecutiveCalls($repositoryMock, $userRepositoryMock));

        $container['repositoryFactory'] = function () use ($repositoryFactoryMock) {
            return $repositoryFactoryMock;
        };

        $validatorMock = $this
            ->getMockBuilder(TagValidator::class)
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

        $emitterMock = $this
            ->getMockBuilder(Emitter::class)
            ->getMock();

        $container['eventEmitter'] = function () use ($emitterMock) {
            return $emitterMock;
        };

        Tag::register($container);
        $this->assertInstanceOf(Tag::class, $container[Tag::class]);
    }

    public function testHandleCreateNewInvalidParam() {
        $repositoryMock = $this
            ->getMockBuilder(TagInterface::class)
            ->getMock();

        $userRepositoryMock = $this
            ->getMockBuilder(UserInterface::class)
            ->getMock();

        $emitterMock = $this
            ->getMockBuilder(Emitter::class)
            ->getMock();

        $handler = new Tag(
            $repositoryMock,
            $userRepositoryMock,
            new TagValidator(),
            $emitterMock
        );

        $this->setExpectedException('InvalidArgumentException');

        $commandMock = $this
            ->getMockBuilder(CreateNew::class)
            ->getMock();
        $commandMock->user = '';
        $commandMock->name = '';

        $handler->handleCreateNew($commandMock);
    }

    public function testHandleCreateNew() {
        $tagEntity        = $this->getEntity();
        $dbConnectionMock = $this->getMockBuilder('Illuminate\Database\ConnectionInterface')
            ->getMock();

        $entityFactory = new EntityFactory($this->optimus);
        $entityFactory->create('Tag');

        $repository = $this->getMockBuilder(DBTag::class)
            ->setMethods(['create', 'save'])
            ->setConstructorArgs([$entityFactory, $this->optimus, $dbConnectionMock])
            ->getMock();

        $repository
            ->expects($this->once())
            ->method('create')
            ->will($this->returnValue($tagEntity));

        $repository
            ->expects($this->once())
            ->method('save')
            ->willReturn($tagEntity);

        $userRepositoryMock = $this->getMockBuilder(DBUser::class)
            ->setConstructorArgs([$entityFactory, $this->optimus, $dbConnectionMock])
            ->getMock();

        $emitterMock = $this
            ->getMockBuilder(Emitter::class)
            ->getMock();

        $handler = new Tag(
            $repository,
            $userRepositoryMock,
            new TagValidator(),
            $emitterMock
        );

        $command       = new CreateNew();
        $command->user = $this->getUserEntity();
        $command->name = 'Tag Test';
        $command->slug = 'tag-test';

        $result = $handler->handleCreateNew($command);
        $this->assertSame($tagEntity, $result);
    }

    public function testHandleDeleteOne() {
        $dbConnectionMock = $this->getMockBuilder('Illuminate\Database\ConnectionInterface')
            ->getMock();

        $entityFactory = new EntityFactory($this->optimus);
        $entityFactory->create('Tag');

        $repository = $this->getMockBuilder(DBTag::class)
            ->setMethods(['delete'])
            ->setConstructorArgs([$entityFactory, $this->optimus, $dbConnectionMock])
            ->getMock();

        $repository
            ->method('deleteOne')
            ->will($this->returnValue(1));

        $credentialRepositoryMock = $this
            ->getMockBuilder(CredentialInterface::class)
            ->getMock();

        $userRepositoryMock = $this->getMockBuilder(DBUser::class)
            ->disableOriginalConstructor()
            ->getMock();

        $emitterMock = $this
            ->getMockBuilder(Emitter::class)
            ->getMock();

        $handler = new Tag(
            $repository,
            $userRepositoryMock,
            new TagValidator(),
            $emitterMock
        );

        $commandMock = $this
            ->getMockBuilder(DeleteOne::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandMock->companyId = 1;
        $commandMock->userId    = 1;

        $this->assertEquals(1, $handler->handleDeleteOne($commandMock));
    }

    public function testHandleDeleteAll() {
        $dbConnectionMock = $this->getMockBuilder('Illuminate\Database\ConnectionInterface')
            ->getMock();

        $entityFactory = new EntityFactory($this->optimus);
        $entityFactory->create('Tag');

        $repository = $this->getMockBuilder(DBTag::class)
            ->setMethods(['deleteByUserId', 'getAllByUserId'])
            ->setConstructorArgs([$entityFactory, $this->optimus, $dbConnectionMock])
            ->getMock();

        $repository
            ->method('deleteByUserId')
            ->will($this->returnValue(1));

        $repository
            ->method('getAllByUserId')
            ->will(
                $this->returnValue(
                    new Collection(
                        [
                            ['id' => 1]
                        ]
                    )
                )
            );

        $userRepositoryMock = $this->getMockBuilder(DBUser::class)
            ->setMethods(null)
            ->setConstructorArgs([$entityFactory, $this->optimus, $dbConnectionMock])
            ->getMock();

        $emitterMock = $this
            ->getMockBuilder(Emitter::class)
            ->getMock();

        $handler = new Tag(
            $repository,
            $userRepositoryMock,
            new TagValidator(),
            $emitterMock
        );

        $commandMock = $this
            ->getMockBuilder(DeleteAll::class)
            ->disableOriginalConstructor()
            ->getMock();

        $commandMock->user = $this->getUserEntity();

        $this->assertEquals(1, $handler->handleDeleteAll($commandMock));
    }
}
