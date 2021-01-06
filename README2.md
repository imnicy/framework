## Framework

提供了一些很有用的功能, 比如 容器, 依赖注入, 事件, ORM, 文件系统等。

### 功能:

- 请求和响应 (`slim\slim`, see: slim framework)
- 容器 (`php-di\php-di`)
- 缓存 (`phpfastcache/phpfastcache`)
- 配置
- Cookie
- 数据库 (`catfan/medoo`)
- ORM (based on `medoo`, provide two ways to invoke the data model, you can never use ORM at all.)
- 加密
- 事件 (`league/event`)
- 文件系统 (`league/flysystem`)
- Session (with CSRF)
- 表单验证 (`rakit/validation`)
- 视图 (`twig/twig`)
- 一些有用的小组件 (如 Str, Arr, Collection, Manager...等)

> 所有的功能都是懒加载的，如果你代码中不需要用到它们，他们不回被初始化

## 关于

这个框架是使用 slim/slim 和 php-di/php-di 来提供路由请求、响应和容器的。
及时有很少的需要依赖的包，但是它也能完成几乎你在开发过程中碰到的所有场景，而且它是非常容易扩展的，希望你能喜欢它。

### 安装

```
composer require imnicy\framework
```

### 引导

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
Route::get('/home', 'HomeController:index');

$framework->run();
```

### 控制器

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
        // request mode parameter: 
        //      1 => query mode.
        //      2 => request mode.
        //      1 | 2 => request && request mode. this is default value.
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

### 模型和仓库

定义模型

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

在控制器中使用

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

如果你不想使用ORM的话，系统中的仓库(Repository)功能也非常方便使用，并且体积更小，运行速度更快：

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

它在使用和继承方面与ORM类似，但不提供属性映射和对象操作。数据查询和数据操作的结果是基于原始状态的medoo。

### 容器

获取容器管理实例:

```php
$container = container();

// or
Main::getInstance()->contaner();
```

从容器获取定义:

```php
$definition = container('name');

// or
$definition = container()->get('name');

// or
$definition = Main::getInstance()->container('name');;
```

将定义放入容器:

```php
// give a callable or instance
container()->singleton('name', Callable $callable);

// or, Set the `$value` parameter to null, will definition a `Support\Helper` instance.
container()->singleton(Support/Helper::class);
```

### Cookie

将Cookies放入响应:

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
                    set_cookie('token', 'token string')->withDomain('/')->with...,
                ]]);
    }
}
```

从请求中获取Cookies:

```php
use Nick\Framework\Support\Helpers;

class Service
{
    public function provider()
    {
        $cookie = get_cookie('token', 'default');
        
        // or use facade
        $cookie = Nicy\Framework\Facades\Cookie::get('token', 'default');
    }
}
```

### 事件

定义侦听器:

```php
class AddedListener
{
    public function handler(AddedEvent $event)
    {
        // some codes
    }
}
```

定义事件:

```php
use App\Models\Product;
use League\Event\AbstractEvent as Event;

class AddedEvent extend Event
{
    protected $product;
    
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
// in anywhere
// event name as custom string or event class name
container('events')->dispatch('event_name', $payloads = []);
```

在 EventServiceProvider 中定义事件侦听列表

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

### 文件系统

基本使用:

```php
container('filesystem')->put('path.txt', 'contents');

// with facade
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

更多内容你可以阅读league/flysystem的文档.

### Session

基本使用:

```php
session('name', 'default');

// set a session
session(['name' => 'value']);

// with facade
Session::put(['name' => 'value']);

Session::get('name', 'default');
```
您可以为会话选择文件、缓存或空为处理程序。


### 表单验证

基本使用:

```php
validate($inputs, [
    'name' => 'required',
    'age' => 'required|numeric',
    ...
]);

// if fail it will throw a ValidationException.

// with Facade
$validator = Validator::make($inputs, $rules = []);

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
        return view('index.latte', $parameters=[]);
        
        // with Facade
        return View::render('resource/home/index.twig', $parameters=[]);
    }
}
```

更多内容你可以阅读 twig/twig 文档