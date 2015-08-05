<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Aws\Sqs\SqsClient;
use yii\base\Component;
use yii\base\InvalidConfigException;

/**
 * SqsQueue
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class SqsQueue extends Component implements QueueInterface
{
    /**
     * @var string
     */
    public $key;
    /**
     * @var string
     */
    public $secret;
    /**
     * @var string
     */
    public $region;
    /**
     * @var string
     */
    public $version = 'latest';
    /**
     * @var SqsClient
     */
    protected $client;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->key === null) {
            throw new InvalidConfigException('The "key" property must be set.');
        }

        if ($this->secret === null) {
            throw new InvalidConfigException('The "secret" property must be set.');
        }

        if ($this->region === null) {
            throw new InvalidConfigException('The "region" property must be set.');
        }

        $this->client = new SqsClient([
            'credentials' => [
                'key' => $this->key,
                'secret' => $this->secret,
            ],
            'region' => $this->region,
            'version' => $this->version,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
        return $this->client->sendMessage([
            'QueueUrl' => $queue,
            'MessageBody' => $payload,
            'DelaySeconds' => $delay,
        ])->get('MessageId');
    }

    /**
     * @inheritdoc
     */
    public function pop($queue)
    {
        $response = $this->client->receiveMessage(['QueueUrl' => $queue]);

        if (count($response['Messages']) < 1) {
            return null;
        }

        $params = reset($response['Messages']);
        $id = $params['MessageId'];
        $payload = $params['Body'];
        unset($params['MessageId'], $params['Body']);

        if (!isset($params['QueueUrl'])) {
            $params['QueueUrl'] = $queue;
        }

        return new Message($id, $payload, $params);
    }

    /**
     * @inheritdoc
     */
    public function purge($queue) {
        $this->client->deleteQueue(['QueueUrl' => $queue]);
    }

    /**
     * @inheritdoc
     */
    public function delete(Message $message)
    {
        $this->client->deleteMessage([
            'QueueUrl' => $message->getParam('QueueUrl'),
            'ReceiptHandle' => $message->getParam('ReceiptHandle'),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function release(Message $message, $delay = 0)
    {
        $this->client->changeMessageVisibility([
            'QueueUrl' => $message->getParam('QueueUrl'),
            'ReceiptHandle' => $message->getParam('ReceiptHandle'),
            'VisibilityTimeout' => $delay,
        ]);
    }
}
