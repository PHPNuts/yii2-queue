<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

/**
 * QueueInterface
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
interface QueueInterface
{
    /**
     * @param string $payload
     * @param integer $delay
     * @param string $queue
     * @return mixed
     */
    public function push($payload, $queue, $delay = 0);

    /**
     * @param string $queue
     * @return Message|null
     */
    public function pop($queue);

    /**
     * @param string $queue
     */
    public function purge($queue);

    /**
     * @param Message $message
     * @param integer $delay
     */
    public function release(Message $message, $delay = 0);

    /**
     * @param Message $message
     */
    public function delete(Message $message);
}
