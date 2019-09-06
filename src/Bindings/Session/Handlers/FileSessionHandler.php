<?php

namespace Nicy\Framework\Bindings\Session\Handlers;

use SessionHandlerInterface;

class FileSessionHandler implements SessionHandlerInterface
{
    /**
     * The path where sessions should be stored.
     *
     * @var string
     */
    protected $path;

    /**
     * The number of minutes the session should be valid.
     *
     * @var int
     */
    protected $minutes;

    /**
     * Create a new file driven handler instance.
     *
     * @param  string  $path
     * @param  int  $minutes
     * @return void
     */
    public function __construct($path, $minutes)
    {
        $this->path = $path;
        $this->minutes = $minutes;
    }

    /**
     * {@inheritdoc}
     */
    public function open($savePath, $sessionName)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function close()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function read($sessionId)
    {
        if (is_file($path = $this->path.'/'.$sessionId)) {
            if (filemtime($path) >= strtotime('-'.$this->minutes.' minutes')) {
                return $this->sharedGet($path);
            }
        }

        return '';
    }

    /**
     * {@inheritdoc}
     */
    public function write($sessionId, $data)
    {
        file_put_contents($this->path.'/'.$sessionId, $data, LOCK_EX);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function destroy($sessionId)
    {
        $filename = $this->path.'/'.$sessionId;

        if ( file_exists($filename) ) @unlink($filename);

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function gc($lifetime)
    {
        foreach ( glob($this->path.'*') as $filename ) {
            if ( filemtime($filename) + $lifetime < time() && file_exists($filename) ) {
                @unlink($filename);
            }
        }

        return true;
    }

    /**
     * Get contents of a file with shared access.
     *
     * @param  string  $path
     * @return string
     */
    public function sharedGet($path)
    {
        $contents = '';

        $handle = fopen($path, 'rb');

        if ($handle) {
            try {
                if (flock($handle, LOCK_SH)) {
                    clearstatcache(true, $path);

                    $contents = fread($handle, filesize($path) ?: 1);

                    flock($handle, LOCK_UN);
                }
            } finally {
                fclose($handle);
            }
        }

        return $contents;
    }
}
