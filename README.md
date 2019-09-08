## simple framework package

Follow the Psr specification

### Provided:

- Request & Response (use `slim\slim`, read more see: slim framework)
- Container (use `php-di\php-di`)
- Cache (use `phpfastcache/phpfastcache`)
- Configure
- Cookie
- Database (use `catfan/medoo`)
- ORM (based on `medoo`, provide two ways to invoke the data model, you can never use ORM at all.)
- Encryption
- Events (use `league/event`)
- Filesystem (use `league/flysystem`)
- Session (with CSRF)
- Validation (use `rakit/validation`)
- View (use `twig/twig`)
- Powerful libs (like Str, Arr, Collection, Manager...)

> All packages are lazy to load, and if you don't use them, you won't execute the code logic of the response.

## Container and Routing

Use slim\slim to provide simple routing requests and responses, container comes from php-di\php-di.

The coding style is very similar to laravel, but lighter and simpler than laravel. Try to assemble a package with as little code as possible.

### Installation

```
composer require imnicy\framework
```

### Bootstrap

```php
require __DIR__ . '/vendor/autoload.php';

$framework = new Nicy\Framework\Main(
    dirname(__DIR__)
);

// Support Facade 
$framework->withFacades();

// Use contracts
$framework->singleton(
    Nicy\Framework\Support\Contracts\Handler::class,
    App\Exceptions\Handler::class
);

// Add some middleware
$framework->middleware(App\Events\StartSession::class);

// Register some service providers
$framework->register(App\Providers\EventServiceProvider::class);

// Add some routes
Router::get('/home', 'HomeController:index');

$framework->run();
```

### Controller

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Nicy\Framework\Support\Router\Arguments;
use Nicy\Framework\Support\Traits\{ForRequest, ForResponse}

class HomeController extends Controller
{
    use ForRequest, ForResponse;

    public function index(Request $request, Arguments $arguments)
    {
        // write your codes ...
        ...

        // for request params
        $requests = $this->request()->all();

        // or
        $queries = $this->request(1)->only(['name', 'age']);

        // or more...

        // for response
        return $this->response('contents', $headers = [], $cookies = []);
        
        // for route arguments as collection
        $arguments->get('name', 'default');
        $arguments->all();
        ...
    }
}
```

### Model or Repository

custom model

```php
namespace App\Models;

use Nicy\Framework\Support\Model;

class Custom extends Model
{
    protected $connection = 'default';
    
    protected $table = 'customs';
    
    protected $fillable = ['name', 'mobile'];
}
```

in controller

```php
namespace App\Http\Controllers;

use App\Models\Custom;

class CustomController extends Controller
{
    public function demo()
    {
        return Custom::instance()->all();
        
        // or for pagination
        return Custom::instance()->paginate(1);
        
        // with some conditions and columns
        // see medoo ducoments
        return Custom::instance()->all(['name[~]' => 'w%'], 'name, age');
        
        // find a item, fill and update
        $found = Custom::instance()->one(['name' => 'bin']);
        
        if ($found) {
            $found->mobile = '156...';
            $found->save();
            
            // delete item
            $found->delete();
        }
        
        // for update with conditions
        Custom::instance()->update(['name[~]' => 'w%'], ['mobile' => '156...']);
        
        // destroy any items
        Custom::instance()->destroy([1,2,3,4]);
    }
}
```

If you don't want to use ORM, there is also a repository model. like this:

```php
namespace App\Repositories;

use Nicy\Framework\Support\Repository;

class Custom extends Repository
{
    protected $connection = 'default';
    
    protected $table = 'customs';
}
```

It is similar to ORM in use and inheritance, but it does not provide attribute mapping and object operation. The results of data query and data operation are medoo based on the original state.

Be continued...
