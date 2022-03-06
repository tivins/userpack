# UserPack
User utility pack for web applications

Require:
* PHP 8.1
* tivins/database
* tivins/php-common
* firebase/php-jwt

Example:

```php
use Tivins\Database\{Database, Connectors\MySQLConnector};
use Tivins\UserPack\UserModule;

$database = new Database(new MySQLConnector($dbname, $user, $password));
$usermod  = new UserModule($database);
$usermod->install();
$usermod->createUser('admin', 'admin@example.com', 'aStrongPassword');

# Find a user using credentials
$user = $usermod->getByCredentials('admin', 'aStrongPassword');
```

JWT:

```php
use Tivins\UserPack\WebToken;
class MyWebToken extends WebToken 
{
    public function __construct()
    {
        $this->issuer         = 'my-app.example.com';
        $this->audience       = '';
        $this->privateKeyPath = '/path/to/private-key.pem';
        $this->publicKeyPath  = '/path/to/public-key.pub';
    }
}

// generate a web-token
$webToken = new MyWebToken();
$user     = $usermod->getByCredentials('admin', 'aStrongPassword');
$token    = $webToken->encode(['uid' => $user->id]);

// Authenticate from Authorization header
$webToken = new MyWebToken();
$user     = $usermod->getFromHTTPAuthorization($webToken);
```

### Hooks

Example of a custom UserModule class:

```php
use Tivins\UserPack\UserModule;
use Tivins\Database\CreateQuery;

class MyUserModule extends UserModule 
{
    /**
     * Override the UserModule property to change the table name. 
     */
    protected string $tableName = 'custom_user_table';
    
    /**
     * Alter the user table creation.
     * @see https://github.com/tivins/database#create-query 
     */
    public function alterCreateTable(CreateQuery $query) : void
    {
        $query->addString('custom_field')
        ->addInteger('another_field', unsigned: true);
    }
}
```

## Session

```php
use Tivins\UserPack\UserSession;
UserSession::isAuthenticated(); // bool
UserSession::getID(); // int
UserSession::setID(123);
```

## UI