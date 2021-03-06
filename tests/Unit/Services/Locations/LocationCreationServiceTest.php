<?php
/*
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

namespace Tests\Unit\Services\Locations;

use Mockery as m;
use Tests\TestCase;
use Pterodactyl\Models\Location;
use Pterodactyl\Services\Locations\LocationCreationService;
use Pterodactyl\Contracts\Repository\LocationRepositoryInterface;

class LocationCreationServiceTest extends TestCase
{
    /**
     * @var \Pterodactyl\Contracts\Repository\LocationRepositoryInterface
     */
    protected $repository;

    /**
     * @var \Pterodactyl\Services\Locations\LocationCreationService
     */
    protected $service;

    /**
     * Setup tests.
     */
    public function setUp()
    {
        parent::setUp();

        $this->repository = m::mock(LocationRepositoryInterface::class);

        $this->service = new LocationCreationService($this->repository);
    }

    /**
     * Test that a location is created.
     */
    public function testLocationIsCreated()
    {
        $location = factory(Location::class)->make();

        $this->repository->shouldReceive('create')->with(['test_data' => 'test_value'])->once()->andReturn($location);

        $response = $this->service->handle(['test_data' => 'test_value']);
        $this->assertNotEmpty($response);
        $this->assertInstanceOf(Location::class, $response);
        $this->assertEquals($location, $response);
    }
}
