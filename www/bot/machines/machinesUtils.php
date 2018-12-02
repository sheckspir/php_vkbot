<?php

function handleMachineMessage($data) {
    if (is_null($data[VK_PAYLOAD][MACHINES_SIZE_TITLE])) {
        _machine_AskSize($data);
    } else {
        _machine_WaitForHuman($data);
    }
}

function _machine_AskSize($data) {
    $user_id = $data[VK_FROM_USER_ID];

    $msg = "Уточните размер рабочего поля";
    $buttons = [];
    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '400*400'], "400*400", "default"];
    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '600*400'], "600*400", "default"];
    $buttons[0] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '900*600'], "900*600", "default"];
    $buttons[1] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '1000*800'], "1000*800", "default"];
    $buttons[2] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '1200*900'], "1200*900", "default"];
    $buttons[3] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '1300*900'], "1300*900", "default"];
    $buttons[4] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => '﻿1600*1000'], "﻿1600*1000", "default"];
    $buttons[4] = [[PAYLOAD_TYPE_QUESTION => MACHINES_TYPE, MACHINES_SIZE_TITLE => 'other'], "﻿Не стандартный", "default"];

    vkApi_messagesSendKeyboard($user_id, $msg, null, $buttons);

}

function _machine_WaitForHuman($data) {
    $user_id = $data[VK_FROM_USER_ID];

    $msg = "Ожидайте, скоро наш специалист свяжется с вами";
    vkApi_messagesSend($user_id, $msg);
}