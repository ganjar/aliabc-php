<?php

namespace ALI\Buffer;

/**
 * Class Buffer
 * @package ALI
 */
class Buffer
{
    /**
     * @var array
     */
    protected $buffersContent = [];

    /**
     * Buffering content in callback function
     * @param \Closure $callback
     */
    public function buffering(\Closure $callback)
    {
        $this->start();
        $callback();
        $this->end();
    }

    /**
     * Start buffering
     */
    public function start()
    {
        ob_start(function ($bufferContent) {
            return $this->add($bufferContent);
        });
    }

    /**
     * Add buffer and get string buffer key
     * (after translate we replace this key to content)
     * @param string $bufferContent
     * @return string
     */
    public function add($bufferContent)
    {
        $bufferContentId = count($this->buffersContent);
        $this->buffersContent[$bufferContentId] = $bufferContent;

        return $this->getBufferKey($bufferContentId);
    }

    /**
     * @param $id
     * @return string
     */
    public function getBufferKey($id)
    {
        return '<!--ALI:buffer:' . $id . '-->';
    }

    /**
     * Stop buffering and get stub content
     */
    public function end()
    {
        ob_end_flush();
    }

    /**
     * @param string $bufferContentId
     * @return string|false
     */
    public function getBufferContent($bufferContentId)
    {
        return !empty($this->buffersContent[$bufferContentId]) ? $this->buffersContent[$bufferContentId] : false;
    }

    /**
     * @return array
     */
    public function getBuffersContent()
    {
        return $this->buffersContent;
    }

    /**
     * @param $bufferContentId
     */
    public function remove($bufferContentId)
    {
        if (isset($this->buffersContent[$bufferContentId])) {
            unset($this->buffersContent[$bufferContentId]);
        }
    }

    public function clear()
    {
        $this->buffersContent = [];
    }
}
