# Laravel Artisan Service Maker

A Laravel package that provides Artisan commands to generate services, repositories, traits, and interfaces with their contracts. This package helps you implement clean architecture patterns in your Laravel applications with minimal effort.

## Features

- Generate service classes with optional interfaces
- Create repository classes with standard CRUD methods
- Generate traits for reusable code
- Create interfaces for your classes
- Automatically bind services and repositories to their interfaces in the service provider
- Support for nested directories

## Installation

You can install the package via composer:

```bash
composer require alirezacrr/lara-artisan-service-maker
```

The package will automatically register its service provider.

## Usage

### Creating a Service

```bash
php artisan make:service UserService
```

Options:
- `--interface` or `-i`: Create an interface for this service
- `--model=ModelName` or `-m ModelName`: The model that the service will use
- `--bind` or `-b`: Automatically bind the service in the service provider
- `--singleton` or `-s`: Register the service as a singleton

Example with all options:
```bash
php artisan make:service UserService -i -m User -b -s
```

This will create:
- `app/Services/UserService.php`
- `app/Interfaces/UserServiceInterface.php` (if `-i` option is used)
- Add binding in `AppServiceProvider.php` (if `-b` or `-s` option is used)

### Creating a Repository

```bash
php artisan make:repository UserRepository
```

Options:
- `--model=ModelName`: The model that the repository will use (defaults to the repository name)
- `--interface` or `-i`: Create an interface for this repository
- `--bind` or `-b`: Automatically bind the repository in the service provider
- `--singleton` or `-s`: Register the repository as a singleton

Example:
```bash
php artisan make:repository UserRepository --model=User -i -b
```

This will create:
- `app/Repositories/UserRepository.php` with standard CRUD methods
- `app/Interfaces/UserRepositoryInterface.php` (if `-i` option is used)
- Add binding in `AppServiceProvider.php` (if `-b` or `-s` option is used)

### Creating a Trait

```bash
php artisan make:trait Filterable
```

This will create `app/Traits/Filterable.php`.

### Creating an Interface

```bash
php artisan make:interface Searchable
```

This will create `app/Interfaces/SearchableInterface.php`.

### Nested Directories

All commands support nested directories:

```bash
php artisan make:service Admin/UserService
php artisan make:repository User/OrderRepository
php artisan make:trait Concerns/Filterable
php artisan make:interface Contracts/Searchable
```

## Examples

### Service with Interface and Model

```bash
php artisan make:service UserService -i -m User
```

Creates:

```php
// app/Interfaces/UserServiceInterface.php
<?php

namespace App\Interfaces;

interface UserServiceInterface
{
    //
}
```

```php
// app/Services/UserService.php
<?php

namespace App\Services;

use App\Interfaces\UserServiceInterface;
use App\Models\User;

class UserService implements UserServiceInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * Create a new service instance.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }
}
```

### Repository with Interface

```bash
php artisan make:repository UserRepository -i
```

Creates a repository with standard CRUD methods (all, find, create, update, delete) and a matching interface:

```php
// app/Repositories/UserRepository.php
<?php

namespace App\Repositories;

use App\Models\User;
use App\Interfaces\UserRepositoryInterface;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @var User
     */
    protected $model;

    /**
     * Create a new repository instance.
     */
    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Get all resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find resource by id.
     *
     * @param int $id
     * @return \App\Models\User|null
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * Create new resource.
     *
     * @param array $data
     * @return \App\Models\User
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update resource.
     *
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update(array $data, $id)
    {
        $record = $this->find($id);
        return $record->update($data);
    }

    /**
     * Delete resource.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }
}
```

```php
// app/Interfaces/UserRepositoryInterface.php
<?php

namespace App\Interfaces;

interface UserRepositoryInterface
{
    /**
     * Get all resources.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function all();

    /**
     * Find resource by id.
     *
     * @param int $id
     * @return mixed
     */
    public function find($id);

    /**
     * Create new resource.
     *
     * @param array $data
     * @return mixed
     */
    public function create(array $data);

    /**
     * Update resource.
     *
     * @param array $data
     * @param int $id
     * @return bool
     */
    public function update(array $data, $id);

    /**
     * Delete resource.
     *
     * @param int $id
     * @return bool
     */
    public function delete($id);
}
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


