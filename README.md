<h1 align="center">symfony-permission</h1>
<p align="center">An authorization library that supports access control models like ACL, RBAC, ABAC in Symfony.</p>

## Installing

Require this package in the `composer.json` of your easyswoole project. This will download the package.

```shell
$ composer require
```

## Usage

First, you can install Doctrine.

```bash
$ composer require symfony/orm-pack
```

Configuring the Database, you can find and customize this inside `.env`:
```env
# customize this line!
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=5.7"

# to use sqlite:
# DATABASE_URL="sqlite:///%kernel.project_dir%/var/app.db"

# to use postgresql:
# DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=11&charset=utf8"
```

And you can create migration file to generate database table, :

*migrations/Version20200823135629.php*
```php
<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200823135629 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE casbin_rules (id INT AUTO_INCREMENT NOT NULL, ptype VARCHAR(255) DEFAULT NULL, v0 VARCHAR(255) DEFAULT NULL, v1 VARCHAR(255) DEFAULT NULL, v2 VARCHAR(255) DEFAULT NULL, v3 VARCHAR(255) DEFAULT NULL, v4 VARCHAR(255) DEFAULT NULL, v5 VARCHAR(255) DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE casbin_rules');
    }
}
```

execute your migrations:
```shell
$ php bin/console doctrine:migrations:migrate
```

Then you can start like this:

```php
use Easyswolle\Permission\Casbin;
use Easyswolle\Permission\Config;

$config = new Config();
$config->setUrl($_ENV['DATABASE_URL']);
$casbin = new Casbin($config);

// adds permissions to a user
$casbin->addPermissionForUser('eve', 'articles', 'read');
// adds a role for a user.
$casbin->addRoleForUser('eve', 'writer');
// adds permissions to a rule
$casbin->addPolicy('writer', 'articles', 'edit');
```

You can check if a user has a permission like this:

```php
// to check if a user has permission
if ($casbin->enforce('eve', 'articles', 'edit')) {
  // permit eve to edit articles
} else {
  // deny the request, show an error
}
```

### Using Enforcer Api

It provides a very rich api to facilitate various operations on the Policy:

Gets all roles:

```php
$casbin->getAllRoles(); // ['writer', 'reader']
```

Gets all the authorization rules in the policy.:

```php
$casbin->getPolicy();
```

Gets the roles that a user has.

```php
$casbin->getRolesForUser('eve'); // ['writer']
```

Gets the users that has a role.

```php
$casbin->getUsersForRole('writer'); // ['eve']
```

Determines whether a user has a role.

```php
$casbin->hasRoleForUser('eve', 'writer'); // true or false
```

Adds a role for a user.

```php
$casbin->addRoleForUser('eve', 'writer');
```

Adds a permission for a user or role.

```php
// to user
$casbin->addPermissionForUser('eve', 'articles', 'read');
// to role
$casbin->addPermissionForUser('writer', 'articles','edit');
```

Deletes a role for a user.

```php
$casbin->deleteRoleForUser('eve', 'writer');
```

Deletes all roles for a user.

```php
$casbin->deleteRolesForUser('eve');
```

Deletes a role.

```php
$casbin->deleteRole('writer');
```

Deletes a permission.

```php
$casbin->deletePermission('articles', 'read'); // returns false if the permission does not exist (aka not affected).
```

Deletes a permission for a user or role.

```php
$casbin->deletePermissionForUser('eve', 'articles', 'read');
```

Deletes permissions for a user or role.

```php
// to user
$casbin->deletePermissionsForUser('eve');
// to role
$casbin->deletePermissionsForUser('writer');
```

Gets permissions for a user or role.

```php
$casbin->getPermissionsForUser('eve'); // return array
```

Determines whether a user has a permission.

```php
$casbin->hasPermissionForUser('eve', 'articles', 'read');  // true or false
```

See [Casbin API](https://casbin.org/docs/en/management-api) for more APIs.
