<?php

namespace Nicy\Framework;

use josegonzalez\Dotenv\Loader;
use Nicy\Support\Traits\Singleton;

class LoadEnvironment
{
    use Singleton;

    /**
     * The directory containing the environment file.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The name of the environment file.
     *
     * @var string|null
     */
    protected $fileName;

    /**
     * @var \josegonzalez\Dotenv\Loader
     */
    protected $loader;

    /**
     * register a new loads environment variables instance.
     *
     * @param string $path
     * @param string|null $name
     * @return $this
     */
    public function register(string $path, string $name=null)
    {
        $this->filePath = $path;
        $this->fileName = $name;

        return $this;
    }

    /**
     * Setup the environment variables.
     *
     * If no environment file exists, we continue silently.
     *
     * @return void
     */
    public function bootstrap()
    {
        try {
            $overwrite = true;
            $this->createLoaderParse()->toEnv($overwrite)->toServer($overwrite)->putenv($overwrite);

        } catch (\LogicException $e) {
            $this->writeErrorAndDie([
                'The environment file is invalid!',
                $e->getMessage(),
            ]);
        }
    }

    /**
     * Create a env loader instance.
     *
     * @return \josegonzalez\Dotenv\Loader
     */
    protected function createLoaderParse()
    {
        if (! $this->loader) {
            $this->loader = (new Loader($this->filePath . DIRECTORY_SEPARATOR . $this->fileName ?: '.env'))->parse();
        }

        return $this->loader;
    }

    /**
     * Write the error information to the screen and exit.
     *
     * @param string[] $errors
     * @return void
     */
    protected function writeErrorAndDie(array $errors)
    {
        foreach ($errors as $error) {
            echo $error . PHP_EOL;
        }

        die(1);
    }

    /**
     * Get env loader instance
     *
     * @return \josegonzalez\Dotenv\Loader
     */
    public function getLoader()
    {
        return $this->createLoaderParse();
    }
}