<?php
require 'formatMessage.php';

// this is simple php webhook for QuilChat WhatsApp Gateway, not recommended using this procedure pattern if you have a lot of keywrds!
//This also work with ChatGPT all you need is just to edit the information below.

header('content-type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);
if (!$data) {
    die('this url is for webhook.');
}
file_put_contents('whatsapp.txt','[' . date('Y-m-d H:i:s') . "]\n" . json_encode($data) . "\n\n",FILE_APPEND);                                             
 $message = strtolower($data['message']);
 $from = strtolower($data['from']);
 $bufferimage = isset($data['bufferImage']) ? $data['bufferImage'] : null;
 $respon = false;


// Use this space to train your ChatGPT, Whatever you input here is what ChatGPT will use to train your replies, so be sure you put enough content here for ChatGPT

$last_message = 'PLEASE ENTER YOUR TRAINING PROMPT HERE. MAKE SURE IT IS SUFFICIENT ENOUGH FOR CHATGPT TO PICK RESPONSES FROM IT';

$ai_language = 'YOU CAN ASK CHATGPT NOT TO INCLUDE SOME INFORMATION HERE';

// Call OpenAI ChatGPT API with message
if (!empty($message)) {
    $openai_api_key = 'PLEASE ENTER YOUR API KEY HERE'; //this api can be obtained from OpenAI website

    // Set up request data for ChatGPT API
    $request_data = array(
        'model' => 'gpt-3.5-turbo-0301', //This is the best Model for this for now, once another model is compatible, this will be updated
    //    'max_tokens' =>'10',      // You can enable this if you want, but it is not compulsory. Token means the number of words ChatGPT will give in response.
        'messages' => array(
            array(
                'role' => 'assistant',
                'content' => $last_message . $message .$ai_language,
            ),
        ),
    );

    // Send request to ChatGPT API
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.openai.com/v1/chat/completions');     // Don't change the URL if you don't know what it means.
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request_data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Authorization: Bearer ' . $openai_api_key
    ));
    $response_json = curl_exec($ch);
    curl_close($ch);

    // Extract response text from ChatGPT API response
    $response_arr = json_decode($response_json, true);
    $response_text = $response_arr['choices'][0]['message']['content'];

    // Print response for debugging purposes
    file_put_contents('openai.txt', '[' . date('Y-m-d H:i:s') . "]\n" . $response_text . "\n\n", FILE_APPEND);

    // Build JSON response to send back to system
    $respon = FormatMessage::text($response_text, true);
}


// CHATGPT RESPONSE IS OVER, THE CODE BELOW THIS IS USED FOR INDIVIDUAL KEYWORDS


// for media message
if ($message == 'media') {
    $respon = FormatMessage::exampleMedia(true);
}

// for button message
if ($message == 'button') {
    $respon = FormatMessage::exampleButton(true);
}

// for template message
if ($message == 'template') {
    $respon = FormatMessage::exampleTemplate(true);
}

// for list message
if ($message == 'list') {
    $respon = FormatMessage::exampleList(true);
}

// get image
if ($bufferimage) {
    $base64str = 'data:image/png;base64,' . $bufferimage;
    list(,$base64str) = explode(';', $base64str);
    list(,$base64str) = explode(',', $base64str);
    $imagedata = base64_decode($base64str);
    $filename = 'images/' . time() . '.png';
   $file = file_put_contents($filename, $imagedata);
    fwrite($file, $imagedata);
    fclose($file);
}


echo $respon;

?>
