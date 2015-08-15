<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Predis\Client;
use Predis\Transaction\MultiExec;
use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * RedisQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class RedisQueue extends Component implements QueueInterface
{
    /**
     * @var Client|array|string
     */
    public $redis;
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

        if ($this->redis === null) {
            throw new InvalidConfigException('The "redis" property must be set.');
        }

        if (!$this->redis instanceof Client) {
            $this->redis = new Client($this->redis);
        }
    }

    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
        $payload = Json::encode(['id' => $id = md5(uniqid('', true)), 'payload' => $payload]);

        if ($delay > 0) {
            $this->redis->zadd($queue . ':delayed', [$payload => time() + $delay]);
        } else {
            $this->redis->rpush($queue, [$payload]);
        }

        return $id;
    }

    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
        foreach ([':delayed', ':reserved'] as $type) {
            $options = ['cas' => true, 'watch' => $queue . $type];
            $this->redis->transaction($options, function (MultiExec $transaction) use ($queue, $type) {
                $data = $this->redis->zrangebyscore($queue . $type, '-inf', $time = time());

                if (!empty($data)) {
                    $transaction->zremrangebyscore($queue . $type, '-inf', $time);
                    $transaction->rpush($queue, $data);
                }
            });
        }

        $data = $this->redis->lpop($queue);

        if ($data === null) {
            return null;
        }

        $this->redis->zadd($queue . ':reserved', [$data => time() + $this->expire]);

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
        $this->redis->del([$queue, $queue . ':delayed', $queue . ':reserved']);
    }

    /**
     * @inheritdoc
     */
    public function release(Message $message, $delay = 0)
    {
        if ($delay > 0) {
            $this->redis->zadd($message->getMeta('queue') . ':delayed', [$message->payload => time() + $delay]);
        } else {
            $this->redis->rpush($message->getMeta('queue'), [$message->payload]);
        }
    }

    /**
     * @inheritdoc
     */
    public function delete(Message $message)
    {
        $this->redis->zrem($message->getMeta('queue') . ':reserved', $message->payload);
    }
}
