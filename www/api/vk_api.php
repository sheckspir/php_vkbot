<?php

define('VK_API_VERSION', '5.87'); //Используемая версия API
define('VK_API_ENDPOINT', 'https://api.vk.com/method/');

define('VK_PAYLOAD', 'payload');
define('VK_CONVERSATION_MESSAGE_ID', 'conversation_message_id');
define('VK_FROM_USER_ID', 'from_id');

function vkApi_messagesSend($peer_id, $message, $attachments = array()) {
    return _vkApi_call('messages.send', array(
        'peer_id'    => $peer_id,
        'message'    => $message,
        'attachment' => implode(',', $attachments)
    ));
}

function vkApi_messagesSendKeyboard($peer_id, $message, $attachments = array(), $buttonsInfo) {
    if (is_null($attachments)) {
        $attachments = array();
    }
    
    $buttons1 = [];
    $i = 0;
    foreach ($buttonsInfo as $item) {
        log_msg("info item = " . implode($item));
        $buttons1[$i][0]['action']['type'] = 'text';
        $buttons1[$i][0]['action']['payload'] = json_encode($item[0], JSON_FORCE_OBJECT);
        $buttons1[$i][0]['action']['label'] = $item[1];
        $buttons1[$i][0]['color'] = $item[2];
        $i++;
    }
    $buttons = array(
        "one_time" => true,
        "buttons" => $buttons1);
    $buttons = json_encode($buttons, JSON_UNESCAPED_UNICODE);

    log_msg("buttons = " . $buttons);
    return _vkApi_call('messages.send', array(
        'peer_id'    => $peer_id,
        'message'    => $message,
    'attachment' => implode(',', $attachments),
    'keyboard'   => $buttons
    ));
}

function vkApi_messagesSendEmpty($peer_id, $message) {
    return _vkApi_call('messages.send', array(
        'peer_id'    => $peer_id,
        'message'    => $message,
    ));
}

function vkApi_usersGet($user_id) {
  return _vkApi_call('users.get', array(
    'user_ids' => $user_id,
  ));
}

function vkApi_photosGetMessagesUploadServer($peer_id) {
  return _vkApi_call('photos.getMessagesUploadServer', array(
    'peer_id' => $peer_id,
  ));
}

function vkApi_photosSaveMessagesPhoto($photo, $server, $hash) {
  return _vkApi_call('photos.saveMessagesPhoto', array(
    'photo'  => $photo,
    'server' => $server,
    'hash'   => $hash,
  ));
}

function vkApi_docsGetMessagesUploadServer($peer_id, $type) {
  return _vkApi_call('docs.getMessagesUploadServer', array(
    'peer_id' => $peer_id,
    'type'    => $type,
  ));
}

function vkApi_docsSave($file, $title) {
  return _vkApi_call('docs.save', array(
    'file'  => $file,
    'title' => $title,
  ));
}

function _vkApi_call($method, $params = array()) {
  $params['access_token'] = VK_API_ACCESS_TOKEN;
  $params['v'] = VK_API_VERSION;

  $query = http_build_query($params);
  $url = VK_API_ENDPOINT.$method.'?'.$query;

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    throw new Exception("Failed {$method} request");
  }

  curl_close($curl);

  log_msg($url.'\n'.$json);

  $response = json_decode($json, true);
  if (!$response || !isset($response['response'])) {
    log_error($json);
    throw new Exception("Invalid response for {$method} request");
  }


  return $response['response'];
}

function vkApi_upload($url, $file_name) {
  if (!file_exists($file_name)) {
    throw new Exception('File not found: '.$file_name);
  }

  $curl = curl_init($url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_POSTFIELDS, array('file' => new CURLfile($file_name)));
  $json = curl_exec($curl);
  $error = curl_error($curl);
  if ($error) {
    log_error($error);
    throw new Exception("Failed {$url} request");
  }

  curl_close($curl);

  $response = json_decode($json, true);
  if (!$response) {
    throw new Exception("Invalid response for {$url} request");
  }

  return $response;
}
