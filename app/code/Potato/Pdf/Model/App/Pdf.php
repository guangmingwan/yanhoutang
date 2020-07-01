<?php

namespace Potato\Pdf\Model\App;

use Magento\Framework\Webapi\Response;

/**
 * Class Pdf
 */
class Pdf
{
    const API_URL = 'http://pdf.app.potatocommerce.com/htmltopdf';

    /**
     * @param array $options
     * @return string
     * @throws \Exception
     */
    public function process($options)
    {
        $data = \Zend_Json::encode(
            [
                'options' => $options
            ]
        );
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER,
            [
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data)
            ]
        );

        $output = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($output === false || false === $httpCode || $httpCode !== Response::HTTP_OK) {
            throw new \Exception(__(
                "No output data is returned from service. (HTTP code: %1, error code: %2, error message: %3)",
                $httpCode,
                curl_errno($ch),
                curl_error($ch)
            ));
        }
        if (curl_getinfo($ch, CURLINFO_CONTENT_TYPE) == 'application/json') {
            $json = json_decode($output, true);
            throw new \Exception($json['result']);
        }
        return $output;
    }
}