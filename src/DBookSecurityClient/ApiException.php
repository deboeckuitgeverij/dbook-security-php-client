<?php
namespace DBookSecurityClient;

use DBookSecurityClient\Api\JsonResponse;
use DBookSecurityClient\Api\Response;

class ApiException extends Exception
{
    /**
     * @var Response
     */
    protected $response;

    public function __construct($message, Response $response = null)
    {
        $code = 0;

        if ($response instanceof Response) {

            if ($response instanceof JsonResponse) {

                try {
                    $json = $response->getContent();
                    if (isset($json['error_code'])) {
                        $code = $json['error_code'];
                    }
                    if (isset($json['error_message'])) {
                        $message = sprintf('%s (%s)', $message, $json['error_message']);
                    }
                } catch (\Exception $e) {}

            } else {

                $message = sprintf('%s: HTTP %s %s', $message, $response->getStatus(), substr($response->getContent(), 0, 250));

            }

        }

        parent::__construct($message, $code);

        $this->response = $response;
    }

    public function getResponse()
    {
        return $this->response;
    }
}