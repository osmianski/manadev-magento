<?php

/**
 * @copyright   Copyright (c) http://www.manadev.com
 * @license     http://www.manadev.com/license  Proprietary License
 */
class Mana_Core_Timer
{
    /**
     * @return float start time
     */
    public static function start() {
        return microtime(true);
    }

    /**
     * @param float $startedAt time when measuring was started
     * @return int time elapsed in milliseconds
     */
    public static function stop($startedAt) {
        $finishedAt = microtime(true);

        return round(($finishedAt - $startedAt) * 1000);
    }
}