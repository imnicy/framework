<?php

namespace Nicy\Framework\Bindings\JWT\Support;

use Carbon\Carbon;

class Utils
{
    /**
     * Get the Carbon instance for the current time.
     *
     * @return Carbon
     */
    public static function now()
    {
        return Carbon::now('UTC');
    }

    /**
     * Get the Carbon instance for the timestamp.
     *
     * @param int $timestamp
     * @return Carbon
     */
    public static function timestamp(int $timestamp)
    {
        return Carbon::createFromTimestampUTC($timestamp)->timezone('UTC');
    }

    /**
     * Checks if a timestamp is in the past.
     *
     * @param int $timestamp
     * @param int $leeway
     * @return bool
     */
    public static function isPast(int $timestamp, int $leeway = 0): bool
    {
        $timestamp = static::timestamp($timestamp);
        return $leeway > 0
            ? $timestamp->addSeconds($leeway)->isPast()
            : $timestamp->isPast();
    }

    /**
     * Checks if a timestamp is in the future.
     *
     * @param int $timestamp
     * @param int $leeway
     * @return bool
     */
    public static function isFuture(int $timestamp, int $leeway = 0): bool
    {
        $timestamp = static::timestamp($timestamp);
        return $leeway > 0
            ? $timestamp->subSeconds($leeway)->isFuture()
            : $timestamp->isFuture();
    }
}
