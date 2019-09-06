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

$framework = new Framework\Main(
    dirname(__DIR__)
);

// Support Facade 
$framework->withFacades();

// Register some service providers
$framework->register(App\Providers\EventServiceProvider::class);

// Add some routes
Router::get('/home', 'HomeController:index');

$framework->run();
```

### Controller

```php
use Psr\Http\Message\ServerRequestInterface as Request;
use Nicy\Framework\Support\Traits\{ForRequest, ForResponse}

class HomeController extends Controller
{
    use ForRequest, ForResponse;

    public function index(Request $request, $arguments)
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
    }
}
```

be continued...