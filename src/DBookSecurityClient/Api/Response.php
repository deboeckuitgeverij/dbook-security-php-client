<?php
namespace DBookSecurityClient\Api;

class Response
{
    const TYPE_HTML = 'text/html';
    const TYPE_XML  = 'text/xml';
    const TYPE_JSON = 'application/json';
    const TYPE_PHP  = 'application/php';

    /**
     * @var int
     */
    protected $status;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var string|array
     */
    protected $content;

    /**
     * @var string
     */
    protected $requestUrl;

    /**
     * @param int $status
     * @param string $contentType
     * @param string $content
     * @param null $requestUrl
     */
    public function __construct($status, $contentType, $content, $requestUrl = null)
    {
        $this->status = $status;
        $this->contentType = $contentType;
        $this->content = $content;
        $this->requestUrl = $requestUrl;
    }

    public static function assertContentType($contentType)
    {
        $parts = explode(';', $contentType);

        return $parts[0];
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string|array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @return string
     */
    public function getRequestUrl()
    {
        return $this->requestUrl;
    }

    public function isSuccess()
    {
        return $this->status < 400;
    }
}