<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

/**
 * MessageInterface
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
interface MessageInterface
{
    /**
     * Gets the message identifier.
     *
     * @return string
     */
    public function getId();

    /**
     * Gets the message payload.
     *
     * @return string
     */
    public function getPayload();

    /**
     * Releases the message.
     *
     * @param integer $delay
     */
    public function release($delay = 0);

    /**
     * Deletes the message.
     */
    public function delete();
}
