<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use Twig\Environment;
use Nicy\Framework\Bindings\View\Contracts\FunctionInterface;
use LogicException;
use Nicy\Framework\Main;
use Twig\TwigFunction;

class Asset implements FunctionInterface
{
    /**
     * Path to asset directory.
     *
     * @var string
     */
    public $path;

    /**
     * Create new Asset instance.
     *
     * @param string  $path
     */
    public function __construct($path)
    {
        $this->path = rtrim($path, '/');
    }

    /**
     * Register extension function.
     *
     * @param Environment $engine
     * @return void
     */
    public function register(Environment $engine) :void
    {
        $engine->addFunction(new TwigFunction('asset', array($this, 'from')));
    }

    /**
     * @param  string $url
     *
     * @return string
     */
    public function from($url)
    {
        $main = Main::getInstance();

        $path = $this->path . DIRECTORY_SEPARATOR .  ltrim($url, DIRECTORY_SEPARATOR);

        if (!file_exists($path)) {
            throw new LogicException(
                'Unable to locate the asset "' . $url . '" in the "' . $this->path . '" directory.'
            );
        }

        $info = pathinfo($path);
        $updatedAt = filemtime($path);

        if ($info['dirname'] === '.') {
            $directory = '';
        } elseif ($info['dirname'] === DIRECTORY_SEPARATOR) {
            $directory = DIRECTORY_SEPARATOR;
        } else {
            $directory = $info['dirname'] . DIRECTORY_SEPARATOR;
        }

        $directory = str_replace([$main->path(), DIRECTORY_SEPARATOR], ['', '/'], $directory);

        return $main->container('url')->asset(
            ltrim($directory, DIRECTORY_SEPARATOR) .
            $info['filename'] .
            '.' .
            $info['extension'] .
            '?v=' .
            $updatedAt
        );
    }
}
