<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GetDataFromWineAc
{
    /**
     * API to send data.
     * @access    private
     */
    const API_URL = 'http://bidesk.local/api/new-request';


    /**
     * @param array $data
     * @return bool|string
     */
    public static function sendData($data = array()){


        // Data in JSON format

        $payload = json_encode($data);

        // Prepare new cURL resource
        $ch = curl_init(self::API_URL);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);

        // Set HTTP Header for POST request
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'X-Requested-With: XMLHttpRequest',
                'Content-Length: ' . strlen($payload))
        );

        // Submit the POST request
        $result = curl_exec($ch);

        // Close cURL session handle
        curl_close($ch);

       return $result;


    }

}