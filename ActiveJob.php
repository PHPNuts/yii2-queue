<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use Yii;
use yii\base\Object;
use yii\helpers\Json;

/**
 * ActiveJob
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
abstract class ActiveJob extends Object
{
    /**
     * @var array
     */
    public $serializer = ['serialize', 'unserialize'];

    /**
     * Runs the job.
     */
    abstract public function run();

    /**
     * @return string
     */
    abstract public function queueName();

    /**
     * @return QueueInterface
     */
    public static function getQueue()
    {
        return Yii::$app->get('queue');
    }

    /**
     * Pushs the job.
     */
    public function push()
    {
        $this->getQueue()->push($this->preparePayload(), $this->queueName());
    }

    /**
     * @return string
     */
    protected function preparePayload()
    {
        return Json::encode([
            'serializer' => $this->serializer,
            'object' => call_user_func($this->serializer[0], $this),
        ]);
    }
}
