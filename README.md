## Framework

provides mainstream features, such as DI, IoC, Events, ORM, Filesystem, etc.

### Features:

- Request & Response (use `slim\slim`, see: slim framework)
- DI (use `php-di\php-di`)
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

> All packages are lazy to load. And you can expand many other useful packages to the framework.

## About

The framework use slim/slim and php-di/php-di to provide routing requests, responses and container services.

It's like laravel, but not as complex as laravel.

I want to minimize the number of dependent packages, make the framework faster, and you can clearly see what it does.

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
use Nicy\Framework\Support\Contracts\Router\Arguments;
use Nicy\Framework\Support\Traits\{ForRequest, ForResponse}

class HomeController extends Controller
{
    use ForRequest, ForResponse;

    public function index(Arguments $arguments)
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
use League\Event\EventInterface as Event;

class Custom extends Model
{
    protected $connection = 'default';
    
    protected $table = 'customs';
    
    protected $fillable = ['name', 'mobile'];
    
    // you can listen events on boot
    public static function boot()
    {
        parent::boot();
        
        static::creating(function(Event $e, Model $model) {
            // some code
        });
    }
}
```

in controller

```php
namespace App\Http\Controllers;

use App\Models\Custom;

class CustomController extends Controller
{
    public function demo(Custom $custom)
    {
        // get custom model instance
        $instance = $custom;
        // or
        $instance = Custom::instance();
        
        // get all entries from customs
        return $instance->all();
        
        // or for pagination
        return $instance->paginate(1);
        
        // with some conditions and columns
        // see medoo ducoments
        return $instance->all(['name[~]' => 'w%'], 'name, age');
        
        // find a item, fill and update
        $found = $instance->one(['name' => 'bin']);
        
        if ($found) {
            $found->mobile = '156...';
            $found->save();
            
            // delete item
            $found->delete();
        }
        
        // for update with conditions
        $instance->update(['name[~]' => 'w%'], ['mobile' => '156...']);
        
        // destroy any items
        $instance->destroy([1,2,3,4]);
    }
}
```

If you don't want to use ORM, there is also a repository model. like this:

```php
namespace App\Repositories;

use Nicy\Framework\Support\Repository;

class Custom extends Repository
{
    // if donot set the default connection name, configure `database.php` config, set the database connection default value.
    protected $connection = 'default'; 

    // your table name
    protected $table = 'customs';
}
```

It is similar to ORM in use and inheritance, but it does not provide attribute mapping and object operation. The results of data query and data operation are medoo based on the original state.

### Container

get container instance:

```php
$container = container();

// or
Main::getInstance()->contaner();
```

get definition from container:

```php
$definition = container('name');

// or
$definition = container()->get('name');

// or
$definition = Main::getInstance()->container('name');;
```

set a definition to container:

```php
// give a callable or instance
container()->singleton('name', Callable $callable);

// or, Set the `$value` parameter to null, will definition a `Support\Helper` instance.
container()->singleton(Support/Helper::class);
```

### Cookie

set response with cookie:

```php
use Nick\Framework\Support\Traits;

class Controller
{
    use Traits\ForResponse;

    public function demo()
    {
        return $this->response('contents', $headers, $cookies = [
            'token' => 'generate a token string',
        ]]);
        
        // or
        
        return $this->response('contents', $headers, $cookies = [
                    set_cookie('token', 'token string')->withDomain('/')->with......,
                ]]);
    }
}
```

get cookie from request:

```php
use Nick\Framework\Support\Helpers;

class Service
{
    public function provider()
    {
        $cookie = get_cookie('token', 'default');
        
        // or use facede
        $cookie = Nicy\Framework\Facades\Cookie::get('token', 'default');
    }
}
```

### Events

define a listener:

```php
use League\Event\ListenerInterface as Listener;
use League\Event\EventInterface as Event;

class AddedListener extend Listener
{
    public function handler(Event $event)
    {
        // some codes
    }
}
```

define a event:

```php
use App\Models\Product;
use League\Event\EventAbstract as Event;

class AddedEvent extend Event
{
    protected $product;
    
    public function __construct(Product $product)
    {
        $this->product = $product;
    }
}
```

dispatch a event:

```php
// in anywhere
// event name as custom string or event class name
container('events')->dispatch('event_name', $payloads = []);
```

listen any events in EventServiceProvider

```php
use Nicy\Framework\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extend ServiceProvider
{
    protected $listen = [
        // for event name
        'product.added' => [
            'App\Listeners\AddedListener',
        ],
        // or event class name
        'App\Events\AddedEvent' => [
            'App\Listeners\AddedListener',
        ]
    ];
}
```

### Filesystem

basic use:

```php
container('filesystem')->put('path.txt', 'contents');

// with facede
Disk::put('path.txt', 'contents');

// or
Storage::driver('file')->put('path.txt', 'contents');
```

you can extend your custom filesystem driver.

```php
Storage::extend('qiniu', function() {
    // some code
});

// usage
Storage::driver('qiniu')->put('path.txt', 'contents');
```

you will read league/flysystem document.

### Session

basic use:

```php
session('name', 'default');

// set a session
session(['name' => 'value']);

// with facede
Session::put(['name' => 'value']);

Session::get('name', 'default');
```
you can choose file, cache or null handlers for session.


### Validation

basic use:

```php
validate($inputs, [
    'name' => 'required',
    'age' => 'required|numeric',
    ...
]);

// if fail it will throw a ValidationException.

// with Facede
$validator = Validator::validate($inputs, $rules = []);

if ($validator->fails()) {
    // some code
}
```

more rules, you will read rakit/validation document.


### View

basic use:

```php
class Controller()
{
    public function display()
    {
        return view('resource/home/index.html', $parameters = []);
        
        // with Facade
        return View::render('resource/home/index.html', $parameters = []);
    }
}
```

you can read the twig/twig document for more information.