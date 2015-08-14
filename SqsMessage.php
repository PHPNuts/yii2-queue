<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Aws\Sqs\SqsClient;
use yii\base\InvalidConfigException;
use yii\base\Object;

/**
 * SqsMessage
 *
 * @property $id
 * @property $payload
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class SqsMessage extends Object implements MessageInterface
{
    /**
     * @var SqsClient
     */
    protected $sqs;
    /**
     * @var string
     */
    protected $id;
    /**
     * @var string
     */
    protected $payload;
    /**
     * @var string
     */
    protected $queue;
    /**
     * @var string
     */
    protected $handle;

    /**
     * @param SqsClient $sqs
     * @param array $config
     * @throws InvalidConfigException
     */
    public function __construct(SqsClient $sqs, $config = [])
    {
        $this->sqs = $sqs;

        foreach ([
             ['MessageId' => 'id'],
             ['MessageBody' => 'payload'],
             ['QueueUrl' => 'queue'],
             ['ReceiptHandle' => 'handle'],
        ] as $attribute => $property) {
            if (isset($config[$attribute])) {
                $this->$property = $config[$attribute];
                unset($config[$attribute]);
            } else {
                throw new InvalidConfigException("Sqs message attribute '$attribute' is required.");
            }
        }

        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @inheritdoc
     */
    public function release($delay = 0)
    {
        $this->sqs->changeMessageVisibility([
            'QueueUrl' => $this->queue,
            'ReceiptHandle' => $this->handle,
            'VisibilityTimeout' => $delay,
        ]);
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $this->sqs->deleteMessage([
            'QueueUrl' => $this->queue,
            'ReceiptHandle' => $this->handle,
        ]);
    }
}
