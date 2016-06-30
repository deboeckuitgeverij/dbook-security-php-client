<?php
namespace DBookSecurityClient\Api;

class JsonResponse extends Response
{
    protected $json;

    /**
     * @return string|array
     */
    public function getContent()
    {
        if (!$this->json) {

            $this->json = json_decode($this->content, true);

            if ($this->json === false) {
                throw new \InvalidArgumentException('JSON response could not parse content');
            }
        }

        return $this->json;
    }

    public function isSuccess()
    {
        if ($this->status < 400) {
            $json = $this->getContent();
            if (!isset($json['success']) || $json['success'] === true) {
                return true;
            }
        }
        
        return false;
    }
}