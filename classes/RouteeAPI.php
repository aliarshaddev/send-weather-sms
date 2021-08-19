<?php
include_once 'OpenWeatherMapAPI.php';
class RouteeAPI extends OpenWeatherMapAPI {

    // Api creds for Routee
    private $api_url = "https://connect.routee.net/sms";
    private $auth_url = "https://auth.routee.net/oauth/token" ;
    private $appid = '5c5d5e28e4b0bae5f4accfec';
    private $app_secret = 'MGkNfqGud0' ;

    /**
     * @throws Exception
     */
    private function getAccessToken() : string
    {
        // Get access token from Routee Auth
        $combined_string = $this->appid.":".$this->app_secret;
        // Encode base64 string
        $encoded_string = base64_encode($combined_string);
        // Initialize api request
        $ch = curl_init();
        curl_setopt_array($ch, array(
            CURLOPT_URL => $this->auth_url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "grant_type=client_credentials",
            CURLOPT_HTTPHEADER => array(
                "authorization: Basic $encoded_string",
                "content-type: application/x-www-form-urlencoded"
            ),
        ));
        // Get Response
        $response = curl_exec($ch);
        // Check if there is an error in request
        $err = curl_error($ch);
        curl_close($ch);
        if ($err) {
            // An error occured
            throw new Exception("cURL Error #:" . $err);
        } else {
            // Get Access token in response to use in api request
            return $response;
        }
    }
    private function messageBody($country_id,$to) : string
    {
        // Make json request body for posting it to api
        // Get Weather data for specfic country
        try {
            $weather_data = $this->getDataForCountry($country_id);
            $weather_data = json_decode($weather_data);
            if($weather_data->cod == 200)
            {
                // Get main weather data to use
                $weather_main = $weather_data->main;
                // Get temprature value
                $temp = $weather_main->temp;
                $req_body = array();
                // Check temprature value to customize the message accordingly
                if($temp > 20)
                {
                    $message = "Ali Arshad and Temperature more than 20C. ".round($temp)."C";
                }
                else
                {
                    $message = "Ali Arshad and Temperature less than 20C. ".round($temp)."C";
                }
                $req_body['body'] = $message;
                // Phone to send message to
                $req_body['to'] = $to;
                $req_body['from'] = "optSolTech";
                return json_encode($req_body);
            }
            else
            {
                throw new Exception("Error: $weather_data->message");
            }
        }
        catch (Exception $e)
        {
            echo 'Message: ' .$e->getMessage();
            exit();
        }


    }

    /**
     * @throws Exception
     */
    private  function setAccessTokenSessions()
    {
        // Save access token in session to use it again
        $access_token_data = $this->getAccessToken();
        $access_token_data = json_decode($access_token_data,true);
        $_SESSION['access_token'] = $access_token_data['access_token'];
        $_SESSION['access_token_expire_time'] = time() + (int) $access_token_data['expires_in'];
    }
    /**
     * @throws Exception
     */
    public function sendSms($country_id, $to) : string
    {
        // Send sms to phone using country id and phone number
        // Get access token for request
        if(!isset($_SESSION['access_token']) && empty($_SESSION['access_token']) && !isset($_SESSION['access_token_expire_time']) && empty($_SESSION['access_token_expire_time']))
        {
            $this->setAccessTokenSessions();
        }
        else
        {
            if(time() >= (int) $_SESSION['access_token_expire_time'])
            {
                $this->setAccessTokenSessions();
            }
        }
        if(!empty($country_id) && !empty($to))
        {
            try {
                // Send sms by Api
                $ch = curl_init();
                curl_setopt_array($ch, array(
                    CURLOPT_URL => $this->api_url,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_ENCODING => "",
                    CURLOPT_MAXREDIRS => 10,
                    CURLOPT_TIMEOUT => 30,
                    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    CURLOPT_CUSTOMREQUEST => "POST",
                    CURLOPT_POSTFIELDS => $this->messageBody($country_id,$to),
                    CURLOPT_HTTPHEADER => array(
                        "authorization: Bearer ".$_SESSION['access_token'],
                        "content-type: application/json"
                    ),
                ));
                $response = curl_exec($ch);
                $err = curl_error($ch);
                curl_close($ch);
                if ($err) {
                    echo "cURL Error #:" . $err;
                } else {
                    return $response;
                }
            }
            catch (Exception $e)
            {
                echo 'Message: ' .$e->getMessage();
                exit();
            }

        }
        else
        {
            throw new Exception("Error: Invalid country id or phone");
        }

    }
}
?>