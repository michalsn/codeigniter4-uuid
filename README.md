# CodeIgniter 4 UUID

UUID and ULID support for CodeIgniter 4 with seamless Model integration.

[![PHPUnit](https://github.com/michalsn/codeigniter4-uuid/actions/workflows/phpunit.yml/badge.svg)](https://github.com/michalsn/codeigniter4-uuid/actions/workflows/phpunit.yml)
[![PHPStan](https://github.com/michalsn/codeigniter4-uuid/actions/workflows/phpstan.yml/badge.svg)](https://github.com/michalsn/codeigniter4-uuid/actions/workflows/phpstan.yml)
[![Deptrac](https://github.com/michalsn/codeigniter4-uuid/actions/workflows/deptrac.yml/badge.svg)](https://github.com/michalsn/codeigniter4-uuid/actions/workflows/deptrac.yml)
[![Coverage Status](https://coveralls.io/repos/github/michalsn/codeigniter4-uuid/badge.svg?branch=develop)](https://coveralls.io/github/michalsn/codeigniter4-uuid?branch=develop)

![PHP](https://img.shields.io/badge/PHP-%5E8.2-blue)
![CodeIgniter](https://img.shields.io/badge/CodeIgniter-%5E4.5-blue)

## Installation

```console
composer require michalsn/codeigniter4-uuid:^2.0
```

## Configuration

Publish the configuration file:

```console
php spark uuid:publish
```

This creates `app/Config/Uuid.php` where you can customize the defaults:

```php
<?php

namespace Config;

use Michalsn\CodeIgniterUuid\Config\Uuid as BaseUuid;
use Michalsn\CodeIgniterUuid\Enums\UuidType;
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

class Uuid extends BaseUuid
{
    // Default UUID version for generation
    public UuidVersion $defaultVersion = UuidVersion::V7;

    // Default storage type: STRING (36 chars) or BYTES (16 bytes binary)
    public UuidType $defaultType = UuidType::STRING;

    // ...
}
```

## Usage

### Model Integration

Use the `HasUuid` trait to add UUID support to your models:

```php
<?php

namespace App\Models;

use CodeIgniter\Model;
use Michalsn\CodeIgniterUuid\Traits\HasUuid;

class ProjectModel extends Model
{
    use HasUuid;

    protected $table            = 'projects';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = false; // Required for UUID primary keys
    protected $returnType       = 'array';
    protected $allowedFields    = ['name', 'description'];

    // Define UUID fields using casts
    protected array $casts = [
        'id' => 'uuid',
    ];
}
```

The trait automatically:
- Generates UUIDs for the primary key on insert
- Handles UUID conversion for `find()`, `update()`, and `delete()` operations
- Supports both single and batch inserts

> [!NOTE]
> If you already use `initialize()` method in your model, then you have to add `$this->initUuid()` call inside this method to make the UUID package work.

#### Cast Syntax

The `uuid` cast accepts optional parameters for version and storage type:

```php
protected array $casts = [
    // Use defaults from config
    'id' => 'uuid',

    // Specify version only
    'id' => 'uuid[v4]',

    // Specify version and storage type
    'id' => 'uuid[v7,bytes]',

    // Use default version with specific storage type
    'id' => 'uuid[,bytes]',
];
```

#### UUID on Non-Primary Key Fields

You can use UUIDs on any field, not just primary keys:

```php
class OrderModel extends Model
{
    use HasUuid;

    protected $table            = 'orders';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true; // Regular auto-increment primary key
    protected $allowedFields    = ['tracking_id', 'customer_id', 'total'];

    protected array $casts = [
        'tracking_id' => 'uuid[v4]',
    ];
}
```

UUIDs are only auto-generated for the primary key field. For other fields, you must provide the value:

```php
$order = [
    'tracking_id' => service('uuid')->uuid4()->toRfc4122(),
    'customer_id' => 123,
    'total'       => 99.99,
];

$model->insert($order);
```

### Storage Types

#### String Storage (default)

Stores UUIDs as 36-character strings (e.g., `550e8400-e29b-41d4-a716-446655440000`).

```sql
CREATE TABLE projects (
    id CHAR(36) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);
```

#### Binary Storage

Stores UUIDs as 16-byte binary for better performance and smaller storage footprint, but at the cost of readability.

```php
protected array $casts = [
    'id' => 'uuid[v7,bytes]',
];
```

```sql
-- MySQL
CREATE TABLE projects (
    id BINARY(16) NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);

-- PostgreSQL
CREATE TABLE projects (
    id BYTEA NOT NULL PRIMARY KEY,
    name VARCHAR(255) NOT NULL
);
```

With binary storage:
- The model returns UUIDs as RFC4122 strings (human-readable)
- Internally converts to binary for database operations
- Supports MySQL, PostgreSQL, SQLite3, Oracle, and SQL Server

### UUID Service

Generate UUIDs using the service:

```php
// Convenience methods (recommended)
$uuid = service('uuid')->uuid4();
$uuid = service('uuid')->uuid7();
$ulid = service('uuid')->ulid();

// Or use generate() with string parameter
$uuid = service('uuid')->generate('v4');
$uuid = service('uuid')->generate('v7');

// Or use generate() with enum for type safety
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;

$uuid = service('uuid')->generate(UuidVersion::V4);
$uuid = service('uuid')->generate(UuidVersion::ULID);

// Generate using default version from config
$uuid = service('uuid')->generate();
```

Working with UUIDs:

```php
$uuid = service('uuid')->generate('v7');

// Convert to different formats
$uuid->toRfc4122();  // "550e8400-e29b-41d4-a716-446655440000"
$uuid->toBinary();   // 16-byte binary string
$uuid->toBase32();   // Crockford Base32 encoding
$uuid->toBase58();   // Base58 encoding
```

Parse existing UUIDs:

```php
// From string (with or without hyphens)
$uuid = service('uuid')->fromString('550e8400-e29b-41d4-a716-446655440000');
$uuid = service('uuid')->fromString('550e8400e29b41d4a716446655440000');

// From ULID string
$ulid = service('uuid')->fromString('01ARZ3NDEKTSV4RRFFQ69G5FAV');

// From unknown format (string or binary)
$uuid = service('uuid')->fromValue($unknownValue);
```

Validate UUIDs:

```php
// Check if a string is a valid UUID or ULID
service('uuid')->isValid('550e8400-e29b-41d4-a716-446655440000'); // true
service('uuid')->isValid('01ARZ3NDEKTSV4RRFFQ69G5FAV');           // true (ULID)
service('uuid')->isValid('not-a-uuid');                           // false
```

## Supported UUID Versions

| Version | Description | Use Case |
|---------|-------------|----------|
| `v1` | Timestamp + MAC address | Legacy systems |
| `v3` | Namespace + name (MD5) | Deterministic UUIDs |
| `v4` | Random | General purpose |
| `v5` | Namespace + name (SHA1) | Deterministic UUIDs |
| `v6` | Timestamp-ordered (reordered v1) | Time-sortable |
| `v7` | Timestamp-ordered (Unix epoch) | **Recommended** - Time-sortable, database-friendly |
| `ulid` | Universally Unique Lexicographically Sortable Identifier | Time-sortable, shorter string representation |

**Recommendation:** Use UUID v7 for new projects. It provides:
- Chronological ordering (great for database indexes)
- Better performance than v4 for sorted queries
- Standard UUID format compatibility

## Migration from v1

### Breaking Changes

1. **Dependency Change**: Switched from `ramsey/uuid` to `symfony/uid`
2. **UUID v2 Removed**: UUID v2 (DCE Security) is no longer supported
3. **Removed Classes**: `UuidModel`, `UuidEntity`, `UuidCast` (old location) removed
4. **New Trait-Based Approach**: Use `HasUuid` trait instead of extending `UuidModel`
5. **Type-Safe Versions**: Use `UuidVersion` and `UuidType` enum instead of string constants

### Migration Steps

#### 1. Update composer.json

```json
{
    "require": {
        "michalsn/codeigniter4-uuid": "^2.0"
    }
}
```

Run `composer update`.

#### 2. Update Model Classes

**Before (v1):**

```php
use Michalsn\Uuid\UuidModel;

class ProjectModel extends UuidModel
{
    protected $uuidVersion = 'uuid7';
    protected $uuidFields  = ['id', 'tracking_id'];
}
```

**After (v2):**

```php
use CodeIgniter\Model;
use Michalsn\CodeIgniterUuid\Traits\HasUuid;

class ProjectModel extends Model
{
    use HasUuid;

    protected $useAutoIncrement = false;

    protected array $casts = [
        'id'          => 'uuid[v7]',
        'tracking_id' => 'uuid[v4]',
    ];
}
```

#### 3. Update Entity Classes

**Before (v1):**

```php
use Michalsn\Uuid\UuidEntity;

class ProjectEntity extends UuidEntity
{
    protected $uuids = ['id', 'category_id'];
}
```

**After (v2):**

Entities no longer need special handling. Use standard CodeIgniter entities - the model's cast handles UUID conversion automatically.

```php
use CodeIgniter\Entity\Entity;

class ProjectEntity extends Entity
{
    // No special UUID configuration needed
}
```

#### 4. Update Configuration

**Before (v1):**

```php
// In model file
public $uuidVersion = 'uuid7';
```

**After (v2):**

```php
// In config file
use Michalsn\CodeIgniterUuid\Enums\UuidVersion;

public UuidVersion $defaultVersion = UuidVersion::V7;
```

#### 5. Update UUID String Conversion (Optional)

The old Ramsey UUID method names still work for backward compatibility:

```php
// These work (backward compatible)
$uuid->toString();   // alias for toRfc4122()
$uuid->getBytes();   // alias for toBinary()
$uuid->getHex();     // alias for toHex()

// New Symfony UID methods (preferred)
$uuid->toRfc4122();
$uuid->toBinary();
$uuid->toHex();
$uuid->toBase32();
$uuid->toBase58();
```

#### 6. Remove v2 Usage

If you were using UUID v2, you'll need to migrate to a different version (v4 or v7 recommended).

#### 7. Accessing the Underlying Symfony UID (Optional)

If you need direct access to the underlying Symfony UID object:

```php
$uuid        = service('uuid')->uuid7();
$symfonyUuid = $uuid->unwrap(); // Returns Symfony\Component\Uid\UuidV7
```

### New Features in v2

- **ULID Support**: Generate and parse ULIDs alongside UUIDs
- **SQLite Support**: Binary UUID storage now works with SQLite3
- **Backward Compatible API**: Old Ramsey method names (`toString()`, `getBytes()`) still work

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
