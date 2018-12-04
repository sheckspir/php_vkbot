<?php

function handleNewMessage($data) {

    $userId = $data[VK_FROM_USER_ID];
    log_msg("encode" . json_encode($data));

    $payload = json_decode($data[VK_PAYLOAD]);
    if (is_null($payload)) {
        log_msg("payload null " . $data[VK_PAYLOAD]);
        if ($data[VK_CONVERSATION_MESSAGE_ID] < 1000) {
//        if ($data[VK_CONVERSATION_MESSAGE_ID] < 3) {
            bot_sendFirstMessage($userId);
        }
        //иначе игнорим, видимо нам что-то пишут просто так
    } else {
        $fileName = "";
        foreach ($payload as $payloadValue) {
            $fileName = $fileName . "_" . strval($payloadValue);
        }
        $fileName = trim($fileName, "_");
        if (empty($fileName)) {
            $fileName = "0";
        }
        log_msg("find file " . $fileName);
        sendMessageFromFile($fileName, $userId);
    }
}

function bot_sendFirstMessage($user_id) {
    try {
        sendMessageFromFile("0", $user_id);
    } catch (Exception $exc) {
        log_error($exc);
        throw $exc;
    }



//    $users_get_response = vkApi_usersGet($user_id);
//
//    $user = array_pop($users_get_response);
//    $msg = "Здравствуйте, {$user['first_name']}! Я помогу вам оперативно решить любой ваш вопрос";
//
//
//    $buttons = [];
//    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => "order_machine"], "Станок", "default"];
//    $buttons[1] = [[PAYLOAD_TYPE_QUESTION => "1"], "Комплектующие", "default"];
//    $buttons[2] = [[PAYLOAD_TYPE_QUESTION => "2"], "Вопросы по доставке", "default"];
//    $buttons[3] = [[PAYLOAD_TYPE_QUESTION => "3"], "Техническая поддержка", "default"];
//    $buttons[4] = [[PAYLOAD_TYPE_QUESTION => "4"], "Отзывы и предложения", "default"];
//
//    vkApi_messagesSendKeyboard($user_id, $msg, null, $buttons);
}

function _bot_uploadPhoto($user_id, $file_name) {
    $upload_server_response = vkApi_photosGetMessagesUploadServer($user_id);
    $upload_response = vkApi_upload($upload_server_response['upload_url'], $file_name);

    $photo = $upload_response['photo'];
    $server = $upload_response['server'];
    $hash = $upload_response['hash'];

    $save_response = vkApi_photosSaveMessagesPhoto($photo, $server, $hash);
    $photo = array_pop($save_response);

    return $photo;
}

function _bot_uploadVoiceMessage($user_id, $file_name) {
    $upload_server_response = vkApi_docsGetMessagesUploadServer($user_id, 'audio_message');
    $upload_response = vkApi_upload($upload_server_response['upload_url'], $file_name);

    $file = $upload_response['file'];

    $save_response = vkApi_docsSave($file, 'Voice message');
    $doc = array_pop($save_response);

    return $doc;
}

function sendMessageFromFile($fileName, $userId) {
    $filePath = BOT_MESSAGES_DIRECTORY . "/" . $fileName . ".json";
    if (file_exists($filePath)) {
        $messageContents = json_decode(file_get_contents($filePath), true);
        if (isset($messageContents['messages'])) {
            $allMessages = $messageContents['messages'];
            foreach ($allMessages as $oneMessage) {
                sendMessageFromJson($oneMessage, $fileName, $userId);
            }
        } else {
            sendMessageFromJson($messageContents, $fileName, $userId);
        }
    } else {
        log_error("have no file " . $filePath);
    }
}

function sendMessageFromJson($messageContents, $fileName, $userId) {
    $messageText = $messageContents["text"];
    $buttons;
    if (isset($messageContents["buttons"])) {
        $buttons = [];
        $payloadsNumbers = explode("_", $fileName);
        $currentPayloadNumber = count($payloadsNumbers);
        $buttonsData = $messageContents["buttons"];
        for ($i = 0; $i < count($buttonsData); $i++) {
            $currentButtonData = $buttonsData[$i];
            $message = $currentButtonData["text"];
            $payload = $payloadsNumbers;
            $payload[$currentPayloadNumber] = strval($currentButtonData["code"]);
            $color;
            if (isset($currentButtonData["color"])) {
                $color = $currentButtonData["color"];
            } else {
                $color = "default";
            }
            $payloadVK = array();
            if (strcmp($payload[$currentPayloadNumber], "START") == 0) {
                $payloadVK[0] = "0";
            } elseif (strcmp($payload[$currentPayloadNumber], "BACK") == 0) {
                if (count($payload) <= 2) {
                    $payloadVK[0] = "0";
                } else {
                    for ($j = 0; $j < count($payload) - 2; $j++) {
                        $payloadVK[strval($j)] = strval($payload[$j]);
                    }
                }
            } else {
                for ($j = 0; $j < count($payload); $j++) {
                    $payloadVK[strval($j)] = strval($payload[$j]);
                }
            }
            $buttons[$i] = [$payloadVK, $message, $color];
        }
    }
    $attachments;
    if (isset($messageContents["attachments"])) {
        $attachments = array();
        $attJson = $messageContents["attachments"];
        foreach ($attJson as $att) {
            array_push($attachments, $att['type'] . $att['value']);
        }
    }

    vkApi_messagesSendKeyboard($userId, $messageText, $attachments, $buttons);
}

function handleMachineMessage($payload, $userId) {
    if (is_null($payload->{"machine_size"})) {
        _machine_AskSize($userId);
    } else {
        _machine_WaitForHuman($userId);
    }
}

function _machine_AskSize($userId) {

    $msg = "Уточните размер рабочего поля";
    $buttons = [];
    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '400*400'], "400*400", "default"];
    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '600*400'], "600*400", "default"];
    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '900*600'], "900*600", "default"];
    $buttons[1] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '1000*800'], "1000*800", "default"];
    $buttons[2] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '1200*900'], "1200*900", "default"];
    $buttons[3] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '1300*900'], "1300*900", "default"];
    $buttons[4] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => '﻿1600*1000'], "﻿1600*1000", "default"];
    $buttons[4] = [[PAYLOAD_TYPE_QUESTION => "order_machine", "machine_size" => 'other'], "﻿Не стандартный", "default"];

    vkApi_messagesSendKeyboard($userId, $msg, null, $buttons);
}

function _machine_WaitForHuman($userId) {
    $msg = "Ожидайте, скоро наш специалист свяжется с вами";
    vkApi_messagesSend($userId, $msg);
}
