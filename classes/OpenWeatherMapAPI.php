<?php
class OpenWeatherMapAPI {
    // Api creds for OpenWeatherMap
    private $appid = 'b385aa7d4e568152288b3c9f5c2458a5';
    // Units for temperature response metric => Celsius, standard => Kelvin, imperial => Fahrenheit
    private  $units = 'metric';

    /**
     * @throws Exception
     */
    protected function getDataForCountry($id) : string
    {
        // Get Data from OpenWeatherMap by country id
        if(!empty($id))
        {
            // Api url for getting data from OpenWeatherMap
            $api_url = "https://api.openweathermap.org/data/2.5/weather?id=$id&appid=$this->appid&units=$this->units";
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $api_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $response = curl_exec($ch);
            $err = curl_error($ch);
            curl_close($ch);
            if ($err) {
                throw new Exception("cURL Error #:" . $err);

            } else {
                return $response;
            }

        }
        else
        {
            throw new Exception("Error: Invalid country id");
        }

    }

}
?>