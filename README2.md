## Framework

提供了一些很有用的功能, 比如 路由, 容器, 事件, ORM, 文件系统等。

### 功能:

- 路由 (`slim\slim`)
- 容器 (`php-di\php-di`)
- 缓存 (`phpfastcache/phpfastcache`)
- 配置
- Cookie
- 数据库 (`catfan/medoo`)
- ORM (based on `medoo`)
- 加解密
- 事件 (`league/event`)
- 文件系统 (`league/flysystem`)
- Session (with CSRF)
- 表单验证 (`rakit/validation`)
- 视图 (`twig/twig`)
- 其他 (如 Singleton, Str, Arr, Collection, Manager...等)

> 除了请求、响应、路由和容器这几个基本功能外其他都是懒加载，如果你的代码中不需要用到它们，他们永远不会被初始化

## 关于

- 这个框架是使用 Slim-Framework 和 PHP-DI 来提供路由请求、响应和服务容器等基础功能的。
- 即使它的依赖包很少且代码简单，但是它的功能覆盖了几乎你在开发过程中碰到的所有场景。
- 它非常容易扩展，通过创建自己的 `ServiceProvider` 然后注册至服务容器中既可以在任何地方使用它。

### 安装

```
  {
    "require": {
      "imnicy/framework": "dev-master"
    }
  }
```

### 开始使用

```php
require __DIR__ . '/vendor/autoload.php';

// Setup environment variables
Nicy\Framework\LoadEnvironment::instance()->register(
    dirname(__DIR__), '.env'
)->bootstrap();

// Create framework instance
$framework = new Nicy\Framework\Main(
    dirname(__DIR__)
);

// Support Facade 
$framework->withFacades();

// Use contracts
$framework->singleton(
    Nicy\Framework\Exceptions\ExceptionHandler::class,
    Exceptions\Handler::class
);

// Add some middleware
$framework->middleware(Middleware\StartSession::class);

// Register some service providers
$framework->register(Providers\EventServiceProvider::class);

// Add some routes
Nicy\Framework\Facades\Route::get('/home', 'HomeController:index');
// ro
$framework->app()->get('/any', 'HomeController:any');

$framework->run();
```

### 控制器

```php
use Nicy\Framework\Support\Contracts\Router\Arguments;
use Nicy\Framework\Support\Traits\{ForRequest, ForResponse};

class HomeController extends Controller
{
    use ForRequest, ForResponse;

    public function index(Arguments $arguments)
    {
        // your codes ...
        ...

        // request params
        $requests = $this->request()->all();

        // or
        // request mode parameter: 
        //      1 => query mode.
        //      2 => request mode.
        //      1 | 2 => request && request mode. this is default value.
        $queries = $this->request(1)->only(['name', 'age']);

        // route arguments as collection
        $arguments->get('name', 'default');
        $arguments->all();
        ...

        // response
        return $this->response('contents', $headers = [], $cookies = []);
    }
}
```

### 模型和仓库

定义模型

```php
class Custom extends Nicy\Framework\Support\Model
{
    protected $connection = 'default';
    
    protected $table = 'customs';
    
    protected $fillable = ['name', 'mobile'];
    
    // you can listen events on boot
    public static function boot()
    {
        parent::boot();
        
        static::creating(function(League\Event\EventInterface $e, Nicy\Framework\Support\Model $model) {
            // some code
        });
    }
}
```

在控制器中使用

```php
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
        // see medoo documents
        return $instance->all('*', ['name[~]' => 'w%']);

        // find a item, fill and update
        $found = $instance->one('*', ['name' => 'bin']);

        if ($found) {
            $found->mobile = '156...';
            $found->save();
            
            // delete item
            $found->delete();
        }

        // update with conditions
        $instance->update(['name[~]' => 'w%'], ['mobile' => '156...']);

        // destroy any items with primary
        $instance->destroy([1,2,3,4]);
    }
}
```

如果你不想使用ORM的话，系统中的仓库(Repository)功能也非常方便，并且体积更小，运行速度更快：

```php
class Custom extends Nicy\Framework\Support\Repository
{
    // if donot set the default connection name, configure `database.php` config, set the database connection default value.
    protected $connection = 'default'; 

    // table name
    protected $table = 'customs';
}
```

它在使用和继承方面与ORM类似，但不提供属性映射和对象操作。数据查询和数据操作的结果是基于原始状态的medoo。

### 容器

获取容器管理实例:

```php
$container = container();
// or
Nicy\Framework\Main::instance()->container();
```

从容器获取定义:

```php
$definition = container('name');
// or
$definition = container()->get('name');
// or
$definition = Nicy\Framework\Main::instance()->container('name');
```

将定义放入容器:

```php
// give a callable or instance
container()->singleton('name', $callable);

// or, Set the `$value` parameter to null, will definition a `Your\ClassName` instance.
container()->singleton(Your/ClassName::class);
```

### Cookie

将Cookies放入响应:

```php
class Controller
{
    use Nicy\Framework\Support\Traits\ForResponse;

    public function demo()
    {
        return $this->response('contents',$headers, $cookies = [
            'token' => 'generate a token string',
        ]]);
        
        // or
        return $this->response('contents', $headers, $cookies = [
                    set_cookie('token', 'token string')->withDomain('/')->with...,
                ]]);
    }
}
```

从请求中获取Cookies:

```php
class Service
{
    public function provider()
    {
        $cookie = get_cookie('token', 'default');
        
        // or use Facade
        $cookie = Nicy\Framework\Facades\Cookie::get('token', 'default');
    }
}
```

### 事件

定义侦听器:

```php
class AddedListener
{
    public function handler(League\Event\EventInterface $event)
    {
        // some codes
    }
}
```

定义事件:

```php
class AddedEvent extends League\Event\AbstractEvent
{
    public $product;
    
    public function __construct(Product $product)
    {
        $this->product = $product;
    }

    public function getName()
    {
        return 'product.added';
    }
}
```

触发事件:

```php
// anywhere
// event name as custom string or event class name
container('events')->dispatch('event_name', $payloads);
```

在 EventServiceProvider 中定义事件侦听列表

```php
use Nicy\Framework\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
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

### 文件系统

基本使用:

```php
container('filesystem')->write('path.txt', 'contents');

// with Facade
Nicy\Framework\Facades\Disk::write('path.txt', 'contents');

// or
Nicy\Framework\Facades\Storage::disk('file')->write('path.txt', 'contents');
```

you can extend your custom filesystem driver.

```php
Nicy\Framework\Facades\Storage::extend('cos', function() {
    // some code
});

// usage
Nicy\Framework\Facades\Storage::disk('cos')->write('path.txt', 'contents');
```

更多内容你可以阅读league/flysystem的文档.

### Session

基本使用:

```php
session('name', 'default');

// set a session
session(['name' => 'value']);

// with Facade
Nicy\Framework\Facades\Session::put(['name' => 'value']);
Nicy\Framework\Facades\Session::get('name', 'default');
```
您可以为会话选择文件、缓存或空为处理程序。


### 表单验证

基本使用:

```php
validate($inputs, [
    'name' => 'required',
    'age' => 'required|numeric|in:0,1',
    ...
]);

// throw a ValidationException if fails.

// with Facade
$validator = Nicy\Framework\Facades\Validator::make$inputs, $rules);

if ($validator->fails()) {
    // some code
}
```

更多的校验规则你可以阅读 rakit/validation 的文档


### 视图

基本使用:

```php
class Controller
{
    public function display()
    {
        return view('index.twig', $parameters);
        
        // with Facade
        return Nicy\Framework\Facades\View::render('resource/home/index.twig', $parameters);
    }
}
```

更多内容你可以阅读 twig/twig 文档