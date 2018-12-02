<?php

define("PAYLOAD_TYPE_QUESTION", "type_question");

function log_msg($message) {
  if (is_array($message)) {
    $message = json_encode($message);

    if (is_array($message)) {
        $message = json_encode($message);
    }
  }

    $trace = debug_backtrace();
    $function_name = isset($trace[2]) ? $trace[2]['function'] : '-';
    $mark = date("H:i:s") . ' [' . $function_name . ']';
    $log_name = BOT_LOGS_DIRECTORY.'/log_I_' . date("j.n.Y") . '.txt';
    file_put_contents($log_name, $mark . " : " . '[INFO] ' . $message . "\n", FILE_APPEND);
}

function log_error($message) {
  if (is_array($message)) {
    $message = json_encode($message);
  }

    $trace = debug_backtrace();
    $function_name = isset($trace[2]) ? $trace[2]['function'] : '-';
    $mark = date("H:i:s") . ' [' . $function_name . ']';
    $log_name = BOT_LOGS_DIRECTORY.'/log_E_' . date("j.n.Y") . '.txt';
    file_put_contents($log_name, $mark . " : " . '[ERROR] ' . $message . "\n", FILE_APPEND);
}

function _log_write($message) {
  $trace = debug_backtrace();
  $function_name = isset($trace[2]) ? $trace[2]['function'] : '-';
  $mark = date("H:i:s") . ' [' . $function_name . ']';
  $log_name = BOT_LOGS_DIRECTORY.'/log_' . date("j.n.Y") . '.txt';
  file_put_contents($log_name, $mark . " : " . $message . "\n", FILE_APPEND);
}
