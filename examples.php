<?php
  include('SMS_BY.php');
  require_once('Transliterate.php');
  require_once('CountSmsParts.php');

  // Your API-KEY or token which you can obtain here: https://app.sms.by/user-api/token
  $token = '';  // Код токена вы можете получить здесь: https://app.sms.by/user-api/token
  // Phone number where you will receive all test sms 
  $phone = '';  // Номер телефона для теста 

 
  $text = "Заглавная буква в начале текста"; // place here any sample text 
  $comment = "Пример работы транслитерации строки. \"$text\" "; // transliteration of russian text to english 
  $translit = Transliterate::getTransliteration($text);
  _echo($comment, $translit);


  $string = "Длина этого короткого текста на русском  примерно 70 символов или около того"  ;
  $oSize = new CountSmsParts($string);
  $res = $oSize->checkTextLength($string);
  _echo("Определяем размер сообщения");
  _echo("Текст: $string");
  _echo("Вызов функции CountSmsParts->checkTextLength:", "Частей = ".$res['parts'].", длина=".$res['len']);

  $sms = new SMS_BY($token);

  /** Get Balance / Получение баланса  */
  if (true) {
    $res = $sms->getBalance();
    _echo("Requesting balance", "Balance = " . $res->result[0]->balance . " ". $res->currency);
  }

  /** Send simple Sms message / Отправка простого сообщения */
  if (false) {
    $message = 'Hello from SMS.BY!';
    _echo("Send sms-message '$message' на номер: $phone");
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

  /**  Get a list of your messages / Получение списка своих сообщений */
  if (false) {
    $messages = $sms->getMessagesList();
    echo "<pre>";
    print_r($messages->result);
    echo "</pre>";
  }

  /** Get a list of Sender IDs Получение списка альфа-имён */
  if (false) {
    $alpha_names = $sms->getAlphaNames();
    echo "<pre>";
    print_r($alpha_names);
    echo "</pre>";
  }

  /**  Получение ID Альфа имени */
  if (false) {
    $name = '0'; // Ваше альфа-имя
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

  /**  Получение списка viber-имён */
  if (false) {
    $viver_names = $sms->getVibernames();
    echo "<pre>";
    print_r($viver_names);
    echo "</pre>";
  }

  /** Отправка простого viber-сообщения */
  if(false) {
    $message = 'Привет от sms.by!';
    $vibername_id = 0;  // ID вашего viber-имени
    $res = $sms->sendQuickViberMessage($phone, $vibername_id, $message);
    if (isset($res->status) and $res->status=='OK')
      _echo("Отправка sms-сообщения '$message' на номер: $phone", "Сообщение успешно отправлено, его ID: {$res->result->viber_id}");
    else
      _echo("Отправка sms-сообщения '$message' на номер: $phone", "Во время отправки сообщения произошла ошибка");
  }

  /**  Отправка viber-сообщения списку рассылки */
  if (false) {
    $message = [  // информация о передаваемом сообщении
      'type_message' => 'BUTTON',  // тип сообщения
      'message' => 'Привет от sms.by!',  // текст сообщения
      'button' => 'Нажми здесь',  // текст кнопки
      'button_link' => 'https://sms.by'  // ссылка кнопки
    ];
    $name = 'Тестовая рассылка';
    $vibername_id = 0;  // ID вашего viber-имени
    $list_id = 0;  // ID вашего списка рассылки
    $d_schedule = '2022-03-01 12:00';
    $res = $sms->sendViberMessageList($message, $name, $vibername_id, $list_id, $d_schedule);
    if (isset($res->status) and $res->status=='OK')
      _echo("Отправка viber-сообщения списку рассылки", "Рассылка успешно создана, её ID: {$res->result->viber_id}");
    else
      _echo("Отправка viber-сообщения списку рассылки", "Во время отправки сообщения произошла ошибка");
  }

  /**  Получение списка своих viber-сообщений */
  if (false) {
    $messages = $sms->getViberMessageList();
    echo "<pre>";
    print_r($messages);
    echo "</pre>";
  }


  function _echo($comment, $result="") {
    $web = $_SERVER['REQUEST_METHOD'] ?? false;
    $d = (!$web) ? "\n" : "<br />";
    echo "$d";
    echo "<b>Действие:</b> $comment $d" ;
    if(!empty($result))
      echo "<b>Результат:</b> $result $d $d";
  }
