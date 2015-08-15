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
     * Pushs payload to the queue.
     *
     * @param string $payload
     * @param integer $delay
     * @param string $queue
     * @return string
     */
    public function push($payload, $queue, $delay = 0);

    /**
     * Pops message from the queue.
     *
     * @param string $queue
     * @return Message|null
     */
    public function pop($queue);

    /**
     * Purges the queue.
     *
     * @param string $queue
     */
    public function purge($queue);

    /**
     * Releases the message.
     *
     * @param Message $message
     * @param integer $delay
     */
    public function release(Message $message, $delay = 0);

    /**
     * Deletes the message.
     *
     * @param Message $message
     */
    public function delete(Message $message);
}
