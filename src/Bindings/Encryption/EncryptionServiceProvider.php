<?php

namespace Nicy\Framework\Bindings\Encryption;

use Nicy\Framework\Support\ServiceProvider;
use Nicy\Support\Str;
use RuntimeException;

class EncryptionServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->container->singleton('encryption', function () {
            $config = $this->container['config']['app'];

            // If the key starts with "base64:", we will need to decode the key before handing
            // it off to the encrypter. Keys may be base-64 encoded for presentation and we
            // want to make sure to convert them back to the raw bytes before encrypting.
            if (Str::startsWith($key = $this->key($config), 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, $config['cipher']);
        });

        $this->container->singleton('Nicy\Framework\Bindings\Encryption\Contracts\Encrypter', function() {
            return $this->container['encryption'];
        });
    }

    /**
     * Extract the encryption key from the given configuration.
     *
     * @param array $config
     * @return string
     * @throws \RuntimeException
     */
    protected function key($config)
    {
        return tap($config['key'], function ($key) {
            if (empty($key)) {
                throw new RuntimeException(
                    'No application encryption key has been specified.'
                );
            }
        });
    }
}