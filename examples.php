<?php

include('SMS_BY.php');
require_once('Transliterate.php');
require_once('CountSmsParts.php');


// Код токена вы можете получить здесь: https://app.sms.by/user-api/token
$token = ''; // КОД_ВАШЕГО_ТОКЕНА
// Номер телефона для теста
$phone = ''; // НОМЕР ТЕЛ ДЛЯ ТЕСТА

$text    = "Заглавная буква в начале текста";
$comment = "Пример работы транслитерации строки. \"$text\" ";
$translit = Transliterate::getTransliteration($text);
_echo($comment,$translit);

$text    = "прописная буква в начале текста";
$comment = "Пример работы транслитерации строки. \"$text\" ";
$translit = Transliterate::getTransliteration($text);
_echo($comment,$translit);


$string = "Длина этого короткого текста на русском  примерно 70 символов или около того"  ;
$oSize = new CountSmsParts($string);
$res = $oSize->checkTextLength($string);
_echo("Определяем размер сообщения");
_echo("Текст: $string");

_echo("Вызов функции CountSmsParts->checkTextLength:","Частей = ".$res['parts'].", длина=".$res['len']);



// баланс
$sms = new SMS_BY($token);
$res = $sms->getBalance();
//echo ;
//var_dump ($res);
_echo("Получаем баланс:","Баланс: " . $res->result[0]->balance . " ". $res->currency);


if(true)
{
    $message = 'Привет от sms.by!';
    _echo("Отправка sms-сообщения '$message' на номер: $phone");
    /** Отправка простого сообщения: */
    $sms = new SMS_BY($token);
    $res = $sms->createSMSMessage($message);
    $message_id = $res->message_id;

    $res = $sms->sendSms($message_id, $phone);

    if ($res == false) {
        _echo ("Во время отправки сообщения произошла ошибка" );
    } else {
        _echo ("Сообщение успешно отправлено, его ID: {$res->sms_id}");
    }
}



if (false)
{
  _echo("Отправка сообщения с паролем от альфа-имени с ID = 0");
  /** Если у вас пока нет собственного Альфа-имени, то вы можете тестировать от системного Альфа-имени с id=0 */
  $sms = new SMS_BY($token);
  $alphaname_id = 0;
  $res = $sms->createPasswordObject('both', 5);
  $password_object_id = $res->result->password_object_id;
  $res2 = $sms->sendSmsMessageWithCode('Ваш пароль: %CODE%', $password_object_id, $phone, $alphaname_id);

  if ($res2 == false) {
      _echo ("Во время отправки сообщения произошла ошибка");
  } else {
      _echo ("Сообщение успешно отправлено, его ID: {$res2[0]->sms_id}");
  }
}

if (false)
{
  /**  Получение списка своих сообщений: */
  $sms = new SMS_BY($token);
  $messages = $sms->getMessagesList();
  echo "<pre>";
  print_r($messages->result);
  echo "</pre>";
}

if (false)
{
  /**  Получение списка Альфа-имен с ID */
  $sms = new SMS_BY($token);
  $alpha_names = $sms->getAlphaNames();
  echo "<pre>";
  print_r($alpha_names);
  echo "</pre>";
}



if (false)
{
  /**  Получение ID Альфа имени */
  $sms = new SMS_BY($token);
  $name = '0'; // Ваше Альфа-имя
  $alphaNameId = $sms->getAlphaNameId($name);
  echo "<pre>";
  print_r($alphaNameId);
  echo "</pre>";

}

function _echo($comment, $result="")
{
    $web = $_SERVER['REQUEST_METHOD'] ?? false;

    if(!$web)
    {
       $d = "\n";
    }
    else {
       $d = "<br />";

    }
    echo "$d";
    echo "Действие: $comment $d" ;

    if(!empty($result))
      echo "Результат: $result $d $d";

}
