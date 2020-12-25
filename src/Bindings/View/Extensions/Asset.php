<?php

namespace Nicy\Framework\Bindings\View\Extensions;

use Latte\Engine;
use Nicy\Framework\Bindings\View\Contracts\FunctionInterface;
use LogicException;
use Nicy\Framework\Main;

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
     * @param Engine $engine
     * @return void
     */
    public function register(Engine $engine) :void
    {
        $engine->addFunction('asset', array($this, 'from'));
    }

    /**
     * @param  string $url
     *
     * @return string
     */
    public function from($url)
    {
        $path = $this->path . '/' .  ltrim($url, '/');

        if (!file_exists($path)) {
            throw new LogicException(
                'Unable to locate the asset "' . $url . '" in the "' . $this->path . '" directory.'
            );
        }

        $info = pathinfo($url);
        $updatedAt = filemtime($path);

        if ($info['dirname'] === '.') {
            $directory = '';
        } elseif ($info['dirname'] === DIRECTORY_SEPARATOR) {
            $directory = DIRECTORY_SEPARATOR;
        } else {
            $directory = $info['dirname'] . DIRECTORY_SEPARATOR;
        }

        return Main::getInstance()->container('url')
                    ->asset($directory . $info['filename'] . '.' . $info['extension'] . '?v=' . $updatedAt);
    }
}
