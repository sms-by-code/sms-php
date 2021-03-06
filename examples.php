<?php
  include('SMS_BY.php');
  require_once('Transliterate.php');
  require_once('CountSmsParts.php');

  $token = '';  // Код токена вы можете получить здесь: https://app.sms.by/user-api/token
  $phone = '';  // Номер телефона для теста


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
  _echo("Вызов функции CountSmsParts->checkTextLength:", "Частей = ".$res['parts'].", длина=".$res['len']);


  /** баланс */
  $sms = new SMS_BY($token);
  $res = $sms->getBalance();
  _echo("Получаем баланс", "Баланс: " . $res->result[0]->balance . " ". $res->currency);

  /** Отправка простого сообщения */
  if(false) {
    $message = 'Привет от sms.by!';
    _echo("Отправка sms-сообщения '$message' на номер: $phone");
    $res = $sms->createSMSMessage($message);
    $message_id = $res->message_id;
    $res = $sms->sendSms($message_id, $phone);

    if ($res == false)
      _echo ("Во время отправки сообщения произошла ошибка" );
    else
      _echo ("Сообщение успешно отправлено, его ID: {$res->sms_id}");
  }

  /** Отправка сообщения с паролем от альфа-имени */
  if (false) {
    _echo("Отправка сообщения с паролем от альфа-имени с ID = 0");
    /** Если у вас пока нет собственного Альфа-имени, то вы можете тестировать от системного Альфа-имени с id=0 */
    $alphaname_id = 0;
    $res = $sms->createPasswordObject('both', 5);
    $password_object_id = $res->result->password_object_id;

    $res = $sms->sendSmsMessageWithCode('Ваш пароль: %CODE%', $password_object_id, $phone, $alphaname_id);
    if ($res == false)
      _echo("Во время отправки сообщения произошла ошибка");
    else
      _echo("Сообщение успешно отправлено, его ID: {$res[0]->sms_id}");
  }

  /**  Получение списка своих сообщений: */
  if (false) {
    $messages = $sms->getMessagesList();
    echo "<pre>";
    print_r($messages->result);
    echo "</pre>";
  }

  /**  Получение списка Альфа-имен с ID */
  if (false) {
    $alpha_names = $sms->getAlphaNames();
    echo "<pre>";
    print_r($alpha_names);
    echo "</pre>";
  }

  /**  Получение ID Альфа имени */
  if (false) {
    $name = '0'; // Ваше Альфа-имя
    $alphaNameId = $sms->getAlphaNameId($name);
    echo "<pre>";
    print_r($alphaNameId);
    echo "</pre>";
  }

  /** FlashCall */
  if (false) {
    $res = $sms->flashCall($phone);  // Создание FlashCall
    if ($res->status=='success') {
      $code = $res->data->code;
      $fclid = $res->data->fclid;

      $res = $sms->confirmFlashCall($phone, $code, $fclid);  // Проверка кода FlashCall
      if ($res->status=='success')
        _echo("Код подтверждён");
      else
        _echo("Во время проверки кода произошла ошибка");
    }
    else
      _echo("Во время создания FlashCall произошла ошибка");
  }


  function _echo($comment, $result="") {
    $web = $_SERVER['REQUEST_METHOD'] ?? false;
    $d = (!$web) ? "\n" : "<br />";
    echo "$d";
    echo "Действие: $comment $d" ;
    if(!empty($result))
      echo "Результат: $result $d $d";
  }
