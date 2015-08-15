<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Aws\Sqs\SqsClient;
use Yii;
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
     * @var SqsClient|array|string
     */
    public $sqs;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->sqs === null) {
            throw new InvalidConfigException('The "sqs" property must be set.');
        }

        if (!$this->sqs instanceof SqsClient) {
            $this->sqs = new SqsClient($this->sqs);
        }
    }

    /**
     * @inheritdoc
     */
    public function push($payload, $queue, $delay = 0)
    {
        return $this->sqs->sendMessage([
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
        $response = $this->sqs->receiveMessage(['QueueUrl' => $queue]);

        if (empty($response['Messages'])) {
            return null;
        }

        $data = reset($response['Messages']);
        $id = $data['MessageId'];
        $payload = $data['MessageBody'];
        unset($data['MessageId'], $data['MessageBody']);

        if (!isset($data['QueueUrl'])) {
            $data['QueueUrl'] = $queue;
        }

        return new Message($id, $payload, $data);
    }

    /**
     * @inheritdoc
     */
    public function purge($queue) {
        $this->sqs->purgeQueue(['QueueUrl' => $queue]);
    }

    /**
     * @inheritdoc
     */
    public function release(Message $message, $delay = 0)
    {
        $this->sqs->changeMessageVisibility([
            'QueueUrl' => $message->getMeta('QueueUrl'),
            'ReceiptHandle' => $message->getMeta('ReceiptHandle'),
            'VisibilityTimeout' => $delay,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function delete(Message $message)
    {
        $this->sqs->deleteMessage([
            'QueueUrl' => $message->getMeta('QueueUrl'),
            'ReceiptHandle' => $message->getMeta('ReceiptHandle'),
        ]);
    }
}
