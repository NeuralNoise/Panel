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

namespace Pterodactyl\Providers;

use Illuminate\Support\ServiceProvider;
use Pterodactyl\Repositories\Daemon\FileRepository;
use Pterodactyl\Repositories\Daemon\PowerRepository;
use Pterodactyl\Repositories\Eloquent\NodeRepository;
use Pterodactyl\Repositories\Eloquent\PackRepository;
use Pterodactyl\Repositories\Eloquent\TaskRepository;
use Pterodactyl\Repositories\Eloquent\UserRepository;
use Pterodactyl\Repositories\Daemon\CommandRepository;
use Pterodactyl\Repositories\Eloquent\ApiKeyRepository;
use Pterodactyl\Repositories\Eloquent\ServerRepository;
use Pterodactyl\Repositories\Eloquent\ServiceRepository;
use Pterodactyl\Repositories\Eloquent\SessionRepository;
use Pterodactyl\Repositories\Eloquent\SubuserRepository;
use Pterodactyl\Repositories\Eloquent\DatabaseRepository;
use Pterodactyl\Repositories\Eloquent\LocationRepository;
use Pterodactyl\Repositories\Eloquent\ScheduleRepository;
use Pterodactyl\Repositories\Eloquent\AllocationRepository;
use Pterodactyl\Repositories\Eloquent\PermissionRepository;
use Pterodactyl\Repositories\Daemon\ConfigurationRepository;
use Pterodactyl\Contracts\Repository\NodeRepositoryInterface;
use Pterodactyl\Contracts\Repository\PackRepositoryInterface;
use Pterodactyl\Contracts\Repository\TaskRepositoryInterface;
use Pterodactyl\Contracts\Repository\UserRepositoryInterface;
use Pterodactyl\Repositories\Eloquent\DatabaseHostRepository;
use Pterodactyl\Repositories\Eloquent\ApiPermissionRepository;
use Pterodactyl\Repositories\Eloquent\ServiceOptionRepository;
use Pterodactyl\Contracts\Repository\ApiKeyRepositoryInterface;
use Pterodactyl\Contracts\Repository\ServerRepositoryInterface;
use Pterodactyl\Repositories\Eloquent\OptionVariableRepository;
use Pterodactyl\Repositories\Eloquent\ServerVariableRepository;
use Pterodactyl\Contracts\Repository\ServiceRepositoryInterface;
use Pterodactyl\Contracts\Repository\SessionRepositoryInterface;
use Pterodactyl\Contracts\Repository\SubuserRepositoryInterface;
use Pterodactyl\Repositories\Eloquent\ServiceVariableRepository;
use Pterodactyl\Contracts\Repository\DatabaseRepositoryInterface;
use Pterodactyl\Contracts\Repository\LocationRepositoryInterface;
use Pterodactyl\Contracts\Repository\ScheduleRepositoryInterface;
use Pterodactyl\Contracts\Repository\AllocationRepositoryInterface;
use Pterodactyl\Contracts\Repository\PermissionRepositoryInterface;
use Pterodactyl\Contracts\Repository\Daemon\FileRepositoryInterface;
use Pterodactyl\Contracts\Repository\Daemon\PowerRepositoryInterface;
use Pterodactyl\Contracts\Repository\DatabaseHostRepositoryInterface;
use Pterodactyl\Contracts\Repository\ApiPermissionRepositoryInterface;
use Pterodactyl\Contracts\Repository\ServiceOptionRepositoryInterface;
use Pterodactyl\Contracts\Repository\Daemon\CommandRepositoryInterface;
use Pterodactyl\Contracts\Repository\OptionVariableRepositoryInterface;
use Pterodactyl\Contracts\Repository\ServerVariableRepositoryInterface;
use Pterodactyl\Contracts\Repository\ServiceVariableRepositoryInterface;
use Pterodactyl\Contracts\Repository\Daemon\ConfigurationRepositoryInterface;
use Pterodactyl\Repositories\Daemon\ServerRepository as DaemonServerRepository;
use Pterodactyl\Contracts\Repository\Daemon\ServerRepositoryInterface as DaemonServerRepositoryInterface;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register all of the repository bindings.
     */
    public function register()
    {
        // Eloquent Repositories
        $this->app->bind(AllocationRepositoryInterface::class, AllocationRepository::class);
        $this->app->bind(ApiKeyRepositoryInterface::class, ApiKeyRepository::class);
        $this->app->bind(ApiPermissionRepositoryInterface::class, ApiPermissionRepository::class);
        $this->app->bind(DatabaseRepositoryInterface::class, DatabaseRepository::class);
        $this->app->bind(DatabaseHostRepositoryInterface::class, DatabaseHostRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(NodeRepositoryInterface::class, NodeRepository::class);
        $this->app->bind(OptionVariableRepositoryInterface::class, OptionVariableRepository::class);
        $this->app->bind(PackRepositoryInterface::class, PackRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(ScheduleRepositoryInterface::class, ScheduleRepository::class);
        $this->app->bind(ServerRepositoryInterface::class, ServerRepository::class);
        $this->app->bind(ServerVariableRepositoryInterface::class, ServerVariableRepository::class);
        $this->app->bind(ServiceRepositoryInterface::class, ServiceRepository::class);
        $this->app->bind(ServiceOptionRepositoryInterface::class, ServiceOptionRepository::class);
        $this->app->bind(ServiceVariableRepositoryInterface::class, ServiceVariableRepository::class);
        $this->app->bind(SessionRepositoryInterface::class, SessionRepository::class);
        $this->app->bind(SubuserRepositoryInterface::class, SubuserRepository::class);
        $this->app->bind(TaskRepositoryInterface::class, TaskRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);

        // Daemon Repositories
        $this->app->bind(ConfigurationRepositoryInterface::class, ConfigurationRepository::class);
        $this->app->bind(CommandRepositoryInterface::class, CommandRepository::class);
        $this->app->bind(DaemonServerRepositoryInterface::class, DaemonServerRepository::class);
        $this->app->bind(FileRepositoryInterface::class, FileRepository::class);
        $this->app->bind(PowerRepositoryInterface::class, PowerRepository::class);
    }
}
