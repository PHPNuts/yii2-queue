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
    private $_id;
    /**
     * @var string
     */
    private $_payload;
    /**
     * @var array
     */
    private $_params;

    /**
     * @param mixed $id
     * @param string $payload
     * @param array $params
     * @param array $config
     */
    public function __construct($id, $payload, array $params = [], array $config = [])
    {
        $this->_id = $id;
        $this->_payload = $payload;
        $this->_params = $params;
        parent::__construct($config);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->_payload;
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
