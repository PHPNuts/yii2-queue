<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Yii;
use yii\base\Component;
use yii\di\Instance;
use yii\helpers\Json;
use yii\redis\Connection;

/**
 * RedisQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class RedisQueue extends Component implements QueueInterface
{
    /**
     * @var Connection|array|string
     */
    public $redis = 'redis';
    /**
     * @var integer
     */
    public $expire = 60;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        $this->redis = Instance::ensure($this->redis, Connection::className());
    }

    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
        $this->redis->executeCommand('ZADD', [
            $queue . ':delayed',
            time() + $delay,
            Json::encode(['id' => $id = md5(uniqid('', true)), 'payload' => $payload]),
        ]);

        return $id;
    }

    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
        // @todo: migrate messages from queue:delayed using transaction

        $data = $this->redis->executeCommand('LPOP', [$queue]);

        if ($data === null) {
            return null;
        }

        $this->redis->executeCommand('ZADD', [$queue . ':reserved', time() + $this->expire, $data]);

        $data = Json::decode($data);
        $id = $data['id'];
        $payload = $data['payload'];
        unset($data['id'], $data['payload']);
        $data['queue'] = $queue;

        return new Message($id, $payload, $data);
    }

    /**
     * @inheritdoc
     */
    public function purge($queue) {
        // @todo: implementation
    }

    /**
     * @inheritdoc
     */
    public function release(Message $message, $delay = 0)
    {
        $this->redis->executeCommand('ZADD', [
            $message->getMeta('queue') . ':delayed',
            time() + $delay,
            $message->payload,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function delete(Message $message)
    {
        $this->redis->executeCommand('ZREM', [$message->getMeta('queue') . ':reserved', $message->payload]);
    }
}
