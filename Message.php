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
 * @property string $id
 * @property string $payload
 *
 * @author Alexander Kochetov <creocoder@gmail.com>
 */
class Message extends Object
{
    /**
     * @var string
     */
    private $_id;
    /**
     * @var string
     */
    private $_payload;
    /**
     * @var array
     */
    private $_meta;

    /**
     * @param string $id
     * @param string $payload
     * @param array $meta
     * @param array $config
     */
    public function __construct($id, $payload, array $meta = [], array $config = [])
    {
        $this->_id = $id;
        $this->_payload = $payload;
        $this->_meta = $meta;
        parent::__construct($config);
    }

    /**
     * @return string
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
    public function getMeta($key)
    {
        if (!array_key_exists($key, $this->_meta)) {
            throw new InvalidParamException("Unknown meta: $key");
        }

        return $this->_meta[$key];
    }
}
