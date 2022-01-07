<?php

namespace Nicy\Tests;

use Mockery;
use Nicy\Framework\Main as Framework;
use Nicy\Framework\Support\Helpers\Request;
use Nicy\Tests\Events\AddedEvent;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\ServerRequestFactory;

class BaseTest extends TestCase
{
    protected $framework;

    protected function tearDown(): void
    {
        Mockery::close();
    }

    protected function getFramework()
    {
        return $this->framework = new Framework(dirname(__DIR__));
    }

    protected function createRequest(string $method, string $uri, array $params=[])
    {
        return (new ServerRequestFactory())->createServerRequest($method, $uri, $params);
    }

    public function testRequestResponse()
    {
        $framework = $this->getFramework();

        $framework->app()->get('/foo/{bar}/{baz}', function() {
            return Request::getUri()->getPath();
        });

        $request = $this->createRequest('GET', $uri = '/foo/bar/haz', ['k' => 'v']);

        $response = $framework->handle($request);

        $this->assertEquals($response->getStatusCode(), 200);
        $this->assertEquals($response->getBody()->getContents(), $uri);
    }

    public function testCache()
    {
        $framework = $this->getFramework();
        $cache = $framework->get('cache');

        $cache->set('key', 'v1');

        $this->assertEquals($cache->get('key'), 'v1');
    }

    public function testConfig()
    {
        $framework = $this->getFramework();
        $config = $framework->get('config');

        $config->set('app.env', 'development');

        $this->assertEquals($config->get('app.env'), 'development');
    }

    public function testFilesystem()
    {
        $framework = $this->getFramework();
        $filesystem = $framework->get('filesystem');

        $disk = $filesystem->disk();

        $disk->write($file = 'temp.txt', 'temp contents');

        $this->assertEquals($disk->read($file), 'temp contents');
    }

    public function testEncryption()
    {
        $framework = $this->getFramework();
        $encryption = $framework->get('encryption');

        $encrypted = $encryption->encrypt('secret');

        $this->assertNotEquals($encrypted, 'secret');
        $this->assertEquals($encryption->decrypt($encrypted), 'secret');
    }

    public function testValidation()
    {
        $framework = $this->getFramework();
        $validator = $framework->get('validation');

        $validation1 = $validator->make(
            ['name' => 'yourname', 'age' => 10, 'gender' => 'F'],
            ['name' => 'required|alpha_dash', 'age' => 'required|numeric', 'gender' => 'nullable|in:F,M']
        );
        $validation1->validate();

        $validation2 = $validator->make(['time' => '2020-10-12'], ['time' => 'required|numeric']);
        $validation2->validate();

        $this->assertEquals($validation1->fails(), false);
        $this->assertEquals($validation2->fails(), true);
    }

    public function testEvents()
    {
        $framework = $this->getFramework();
        $events = $framework->get('events');
        $name = null;

        $events->listen('user_added_event', function(AddedEvent $event) use(& $name) {
            $name = $event->user;
        });

        $events->dispatch(new AddedEvent('bin'));

        $this->assertEquals($name, 'bin');
    }
}