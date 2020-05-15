# CodeIgniter 4 UUID [![](https://github.com/michalsn/codeigniter4-uuid/workflows/PHP%20Tests/badge.svg)](https://github.com/michalsn/codeigniter4-uuid/actions?query=workflow%3A%22PHP+Tests%22)

This package make it easy to work with UUIDs in Codeigniter 4. It provide three classes to make that possible: `Uuid`, `UuidModel` and `UuidEntity`. This implementation is tighly coupled with `Ramsey\Uuid`.

**NOTE: This package is still in the early stage of development. Things may change!**

## Installation via composer

    > composer require michalsn/codeigniter4-uuid

## Manual installation

Download this repo and then enable it by editing **app/Config/Autoload.php** and adding the **Michalsn\UuidModel**
namespace to the **$psr4** array. For example, if you copied it into **app/ThirdParty**:

```php
$psr4 = [
    'Config'      => APPPATH . 'Config',
    APP_NAMESPACE => APPPATH,
    'App'         => APPPATH,
    'Michalsn\Uuid' => APPPATH . 'ThirdParty/codeigniter4-uuid/src',
];
```

## How to use it

In general, using `UuidModel` and `UuidEntity` is no much different than using the original classes provided with CodeIgniter 4 framework. We just have some additional config options. There is a good chance that you will not need to use `Uuid` class at all, because most of the things that happens are already automated.

### Uuid

Working with `Uuid` class is really simple:

```php
$uuid = service('uuid');
// will prepare UUID4 object
$uuid4 = $uuid->uuid4();
// will assign UUID4 as string
$string = $uuid4->toString();
// will assign UUID4 as byte string
$byte_string = $uuid4->getBytes();
```

If you have any additional configuration options to set to a specific UUID version then you can do it via config file.

### UuidModel

UUID fields are always returned as a `string` even if we store them in byte format in the database. This decision was made because of the convenience of use. We don't have to worry about field type or conversion of the data.

Parameter | Default value | Description
--------- | ------------- | -----------
`$uuidVersion` | `uuid4` | Defines the UUID version to use.
`$uuidUseBytes` | `true` | Defines if the UUID should be stored in byte format in the database. This is recommended since will allow us to save over half the space. Also, it's quite easy to use, because we always translate UUID to a string form when retrieving the data or to a byte form when we are saving it.
`$uuidFields` | `['id']` | Defines the fields that will be treated as UUID. By default we assume it will be a primary key, but it can be any field or fields you want.

Now, let's see a simple example, how to use `UuidModel` in your code. In example below, there are no additional changes except that our model extends `UuidModel`. The primary key will be stored as UUID4 in the byte format in the database.

```php
namespace App\Models;

use Michalsn\UuidModel\UuidModel;

class Project1Model extends UuidModel
{
    protected $table      = 'projects_1';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['name', 'description', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'name' => 'required|min_length[3]',
        'description' => 'required',
    ];
}

```

Now, here is an example where we will use the UUID but not as a primary key.

```php
namespace App\Models;

use Michalsn\UuidModel\UuidModel;

class Project2Model extends UuidModel
{
    protected $uuidFields = ['category_id'];

    protected $table      = 'projects_2';
    protected $primaryKey = 'id';

    protected $returnType = 'array';
    protected $useSoftDeletes = true;

    protected $allowedFields = ['category_id', 'name', 'description', 'created_at', 'updated_at', 'deleted_at'];

    protected $useTimestamps = true;

    protected $validationRules = [
        'category_id' => 'required',
        'name' => 'required|min_length[3]',
        'description' => 'required',
    ];
}

```

### UuidEntity

Using the `UuidEntity` is only required if we store UUID fields in the byte format. In other case there are no benefits over original `Entity` class. The same as in the `UuidModel`, by default we assume that only primary key will have the UUID type.

Parameter | Default value | Description
--------- | ------------- | -----------
`$uuids` | `['id']` | Defines the fields that will be treated as UUID. By default we assume it will be a primary key, but it can be any field or fields you want.

Now let's see a two examples which will match those for models that were previously shown.

```php
namespace App\Entities;

use Michalsn\Uuid\UuidEntity;

class Project1Entity extends UuidEntity
{
    protected $attributes = [
        'id' => null,
        'name' => null,
        'description' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

```php
namespace App\Entities;

use Michalsn\Uuid\UuidEntity;

class Project2Entity extends UuidEntity
{
    protected $uuids = ['category_id'];

    protected $attributes = [
        'id' => null,
        'category_id' => null,
        'name' => null,
        'description' => null,
        'created_at' => null,
        'updated_at' => null,
        'deleted_at' => null,
    ];
}
```

And that pretty much it. No more changes are needed.

## Differences between Model and UuidModel

There are a few differences between original class and this provided here. 

* `getInsertID()` method can return a string when primary key is using UUID. If insertion of the data will fail this method still returns `0`.
* `insertBatch()` and `updateBatch()` methods are adding all "additional parameters" (insert date, update date) as it happens in case of using `insert()` or `update()` methods.

## Limitations

For now this class doesn't support SQLite3 database when you want to strore UUIDs in a byte format.

## Supported UUID versions

* Version 1: Time-based - `uuid1`
* Version 2: DCE Security - `uuid2`
* Version 3: Name-based (MD5) - `uuid3`
* Version 4: Random - `uuid4`
* Version 5: Name-based (SHA-1) - `uuid5`
* Version 6: Ordered-Time (nonstandard yet) - `uuid6`

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.