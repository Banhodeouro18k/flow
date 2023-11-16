<?php
// required headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// get posted data
$data = json_decode(file_get_contents("php://input"));

// make sure json data is not incomplete
if (
    !empty($data->query) &&
    !empty($data->appPackageName) &&
    !empty($data->messengerPackageName) &&
    !empty($data->query->sender) &&
    !empty($data->query->message)
) {

    // Extracting data from the received JSON
    $appPackageName = $data->appPackageName;
    $messengerPackageName = $data->messengerPackageName;
    $sender = $data->query->sender;
    $message = $data->query->message;

    // Process messages here if needed

    // Preparing data for the cURL request to the prediction API
    $predictionData = array(
        'question' => $message // Using the received message as the question
    );

    // cURL request to the prediction API endpoint
    $predictionURL = 'https://germinal-flowise.hf.space/api/v1/prediction/ee8fc21f-7886-4df6-87c3-79be136fb5bf';
    $ch = curl_init($predictionURL);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($predictionData));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer 8rGR/M0S9Y0ycHzrZ3lfDBbTsqziPYn9XxNOaG0J67w='
    ));

    // Execute cURL request and capture the response
    $predictionResponse = curl_exec($ch);
    $predictionHTTPCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if cURL request was successful
    if ($predictionHTTPCode == 200) {
        // Successfully received response from prediction API
        // Process the prediction response here if needed

        // Assuming no additional processing, preparing the response to AutoResponder
        $autoResponderResponse = array(
            "replies" => array(
                array("message" => "Hey " . $sender . "!\nThanks for sending: " . $message),
                array("message" => "Success ✅")
            )
        );

        // Set response code - 200 success
        http_response_code(200);
        echo json_encode($autoResponderResponse);
    } else {
        // If there's an error in the cURL request to the prediction API
        http_response_code($predictionHTTPCode);
        echo json_encode(array("error" => "Failed to get prediction response"));
    }
} else {
    // Tell the user json data is incomplete
    http_response_code(400);
    echo json_encode(array("error" => "JSON data is incomplete. Was the request sent by AutoResponder?"));
}
?>
