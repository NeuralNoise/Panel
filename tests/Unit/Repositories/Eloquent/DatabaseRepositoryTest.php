<?php
/**
 * Pterodactyl - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace Tests\Unit\Repositories\Eloquent;

use Mockery as m;
use Tests\TestCase;
use Pterodactyl\Models\Database;
use Illuminate\Database\Eloquent\Builder;
use Pterodactyl\Exceptions\DisplayException;
use Pterodactyl\Repositories\Eloquent\DatabaseRepository;
use Pterodactyl\Exceptions\Repository\DuplicateDatabaseNameException;

class DatabaseRepositoryTest extends TestCase
{
    /**
     * @var \Illuminate\Database\Eloquent\Builder
     */
    protected $builder;

    /**
     * @var \Pterodactyl\Repositories\Eloquent\DatabaseRepository
     */
    protected $repository;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->builder = m::mock(Builder::class);
        $this->repository = m::mock(DatabaseRepository::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $this->repository->shouldReceive('getBuilder')->withNoArgs()->andReturn($this->builder);
        $this->repository->shouldNotReceive('runStatement');
    }

    /**
     * Test that we are returning the correct model.
     */
    public function testCorrectModelIsAssigned()
    {
        $this->assertEquals(Database::class, $this->repository->model());
    }

    /**
     * Test that a database can be created if it does not already exist.
     */
    public function testDatabaseIsCreatedIfNotExists()
    {
        $data = [
            'server_id' => 1,
            'database_host_id' => 100,
            'database' => 'somename',
        ];

        $this->builder->shouldReceive('where')->with([
            ['server_id', '=', array_get($data, 'server_id')],
            ['database_host_id', '=', array_get($data, 'database_host_id')],
            ['database', '=', array_get($data, 'database')],
        ])->once()->andReturnSelf()
            ->shouldReceive('count')->withNoArgs()->once()->andReturn(0);

        $this->repository->shouldReceive('create')->with($data)->once()->andReturn(true);

        $this->assertTrue($this->repository->createIfNotExists($data));
    }

    /**
     * Test that an exception is thrown if a database already exists with the given name.
     */
    public function testExceptionIsThrownIfDatabaseAlreadyExists()
    {
        $this->builder->shouldReceive('where->count')->once()->andReturn(1);
        $this->repository->shouldNotReceive('create');

        try {
            $this->repository->createIfNotExists([]);
        } catch (DisplayException $exception) {
            $this->assertInstanceOf(DuplicateDatabaseNameException::class, $exception);
            $this->assertEquals('A database with those details already exists for the specified server.', $exception->getMessage());
        }
    }

    /**
     * Test SQL used to create a database.
     */
    public function testCreateDatabaseStatement()
    {
        $query = sprintf('CREATE DATABASE IF NOT EXISTS `%s`', 'test_database');
        $this->repository->shouldReceive('runStatement')->with($query, 'test')->once()->andReturn(true);

        $this->assertTrue($this->repository->createDatabase('test_database', 'test'));
    }

    /**
     * Test SQL used to create a user.
     */
    public function testCreateUserStatement()
    {
        $query = sprintf('CREATE USER `%s`@`%s` IDENTIFIED BY \'%s\'', 'test', '%', 'password');
        $this->repository->shouldReceive('runStatement')->with($query, 'test')->once()->andReturn(true);

        $this->assertTrue($this->repository->createUser('test', '%', 'password', 'test'));
    }

    /**
     * Test that a user is assigned the correct permissions on a database.
     */
    public function testUserAssignmentToDatabaseStatement()
    {
        $query = sprintf('GRANT SELECT, INSERT, UPDATE, DELETE, CREATE, DROP, ALTER, INDEX ON `%s`.* TO `%s`@`%s`', 'test_database', 'test', '%');
        $this->repository->shouldReceive('runStatement')->with($query, 'test')->once()->andReturn(true);

        $this->assertTrue($this->repository->assignUserToDatabase('test_database', 'test', '%', 'test'));
    }

    /**
     * Test SQL for flushing privileges.
     */
    public function testFlushStatement()
    {
        $this->repository->shouldReceive('runStatement')->with('FLUSH PRIVILEGES', 'test')->once()->andReturn(true);

        $this->assertTrue($this->repository->flush('test'));
    }

    /**
     * Test SQL to drop a database.
     */
    public function testDropDatabaseStatement()
    {
        $query = sprintf('DROP DATABASE IF EXISTS `%s`', 'test_database');
        $this->repository->shouldReceive('runStatement')->with($query, 'test')->once()->andReturn(true);

        $this->assertTrue($this->repository->dropDatabase('test_database', 'test'));
    }

    /**
     * Test SQL to drop a user.
     */
    public function testDropUserStatement()
    {
        $query = sprintf('DROP USER IF EXISTS `%s`@`%s`', 'test', '%');
        $this->repository->shouldReceive('runStatement')->with($query, 'test')->once()->andReturn(true);

        $this->assertTrue($this->repository->dropUser('test', '%', 'test'));
    }
}
