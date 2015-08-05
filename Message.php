<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\queue;

use yii\base\InvalidParamException;
use yii\base\Object;

/**
 * Message
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
  */
class Message extends Object
{
    /**
     * @var mixed
     */
    public $id;
    /**
     * @var string
     */
    public $payload;
    /**
     * @var array
     */
    private $_params;

    /**
     * @param array $params
     */
    public function setParams(array $params)
    {
        $this->_params = $params;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws InvalidParamException
     */
    public function getParam($key)
    {
        if (!array_key_exists($key, $this->_params)) {
            throw new InvalidParamException("Unknown param: $key");
        }

        return $this->_params[$key];
    }
}
