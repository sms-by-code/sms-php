<?php
  class SMS_BY
  {
    private $token;
    private $API_URL = "https://app.sms.by/api/v1/";

    /**
     * $token - API KEY 
     */
    public function __construct($token) {
       if (!empty($token))
          $this->token = $token;
      else {
          exit("Код токена не указан. Вы можете получить его здесь: https://app.sms.by/user-api/token");
      }
    }

    /**
     * Отправляет команду на API_URL.
     * Если команда обработана успешно, возвращает ответ от API в виде объекта.
     * Если команда обработана неуспешно - передаёт ошибку методу error() и возвращает false.
     * $command - команда API
     * $params - ассоциативный массив, ключи которого являются названиями параметров команды кроме token, значения - их значениями.
     * token в $params передавать не нужно.
     * Необязательный параметр, так как для таких команд, как getLimit, getMessagesList, getPasswordObjects никаких параметров передавать не нужно.
     */
    private function sendRequest($command, $params = array(), $method = 'get') {
        if ($method=='get') {
            $url = $this->API_URL . $command . '?token=' . $this->token;
            if (!empty($params)) {
                foreach ($params as $k => $v)
                    $url .= '&' . $k . '=' . urlencode($v);
            }
        }
        else {
            $url = $this->API_URL . $command;
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 20);
        if ($method=='post') {
          $params['token'] = $this->token;
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result);
        if (isset($result->error)) {
            $this->error($result->error);
            return false;
        }
        else
            return $result;
    }

    /**
     * Error handling 
     * Обрабатывает ошибки.
     * Здесь может быть любой код, обрабатывающий пришедшую по API ошибку, соответствующий вашему приложению.
     * $error - текст ошибки
     */
    private function error($error) {
        trigger_error("<b>SMS.BY error:</b> $error");
    }

    /**
     * Get balance
     * Метод-обёртка для команды getBalance
     */
    public function getBalance() {
        return $this->sendRequest("getBalance");
    }

    /**
     * Метод-обёртка для команды getLimit
     */
    public function getLimit() {
        return $this->sendRequest('getLimit');
    }

    /**
     * Метод-обёртка для команды createSMSMessage
     * $message - текст создаваемого сообщения
     * $alphaname_id - ID альфа-имени, необязательный параметр
     */
    public function createSMSMessage($message, $alphaname_id = 0) {
        $params['message'] = $message;
        if (!empty($alphaname_id))
            $params['alphaname_id'] = (integer)$alphaname_id;
        return $this->sendRequest('createSmsMessage', $params);
    }

    /**
     * Метод-обёртка для команды checkSMSMessageStatus
     * $message_id - ID созданного сообщения
     */
    public function checkSMSMessageStatus($message_id) {
        $params['message_id'] = (integer)$message_id;
        return $this->sendRequest('checkSMSMessageStatus', $params);
    }

    /**
     * Метод-обёртка для команды getMessagesList
     */
    public function getMessagesList() {
        return $this->sendRequest('getMessagesList');
    }

    /**
     * Метод-обёртка для команды sendSms
     * $message_id - ID созданного сообщения
     * $phone - номер телефона в формате 375291234567
     */
    public function sendSms($message_id, $phone) {
        $params['message_id'] = (integer)$message_id;
        $params['phone'] = $phone;
        return $this->sendRequest('sendSms', $params);
    }

    /**
     * The fastest was to send single sms. Use this method if you send 1 sms to 1 user. If you send 1 message to Many users, please use 
     *   createSMSMessage and sendSms
     * Метод-обёртка для команды sendQuickSms - отправка смс-сообщения без предварительного его создания
     * $message - текст созданного сообщения
     * $phone - номер телефона в формате 375291234567
     */
    public function sendQuickSms($message, $phone) {
      if(!empty($message) && !empty($phone))
      {
        $params['message'] = $message;
        $params['phone'] = $phone;
        return $this->sendRequest('sendQuickSms', $params);
      }
      else
      {
         return null;
      }
    }

    /**
     * Метод-обёртка для команды checkSMS
     * $sms_id - ID отправленного SMS
     */
    public function checkSMS($sms_id) {
        $params['sms_id'] = (integer)$sms_id;
        return $this->sendRequest('checkSMS', $params);
    }

    /**
     * Метод-обёртка для команды createPasswordObject
     * $type_id - тип создаваемого объекта пароля, может принимать значения letters, numbers и both
     * $len - длина создаваемого объекта пароля, целое число от 1 до 16
     */
    public function createPasswordObject($type_id, $len) {
        $params['type_id'] = $type_id;
        $params['len'] = (integer)$len;
        return $this->sendRequest('createPasswordObject', $params);
    }

    /**
     * Метод-обёртка для команды editPasswordObject
     * $password_object_id - ID созданного объекта пароля
     * $type_id - тип создаваемого объекта пароля, может принимать значения letters, numbers и both
     * $len - длина создаваемого объекта пароля, целое число от 1 до 16
     */
    public function editPasswordObject($password_object_id, $type_id, $len) {
        $params['id'] = (integer)$password_object_id;
        $params['type_id'] = $type_id;
        $params['len'] = (integer)$len;
        return $this->sendRequest('editPasswordObject', $params);
    }

    /**
     * Метод-обёртка для команды deletePasswordObject
     * $password_object_id - ID созданного объекта пароля
     */
    public function deletePasswordObject($password_object_id) {
        $params['id'] = (integer)$password_object_id;
        return $this->sendRequest('deletePasswordObject', $params);
    }

    /**
     * Метод-обёртка для команды getPasswordObjects
     */
    public function getPasswordObjects() {
        return $this->sendRequest('getPasswordObjects');
    }

    /**
     * Метод-обёртка для команды getPasswordObject
     * $password_object_id - ID созданного объекта пароля
     */
    public function getPasswordObject($password_object_id) {
        $params['id'] = (integer)$password_object_id;
        return $this->sendRequest('getPasswordObject', $params);
    }

    /**
     * Метод-обёртка для команды sendSmsMessageWithCode
     * $message - message text / текст создаваемого сообщения
     * $password_object_id - ID of created Password Object / ID созданного объекта пароля
     * $phone - Phone number in international format / номер телефона в формате 375291234567
     * $alphaname_id - Sender ID (optional) // ID альфа-имени, необязательный параметр
     */
    public function sendSmsMessageWithCode($message, $password_object_id, $phone, $alphaname_id = 0) {
        $params['message'] = $message;
        $params['password_object_id'] = (integer)$password_object_id;
        $params['phone'] = $phone;
        if (!empty($alphaname_id))
            $params['alphaname_id'] = (integer)$alphaname_id;
        return $this->sendRequest('sendSmsMessageWithCode', $params);
    }

    /**
     * Метод-обёртка для команды getAlphaNames
     */
    public function getAlphaNames() {
        return $this->sendRequest('getAlphanames');
    }

    /**
     * Метод-обёртка для команды getAlphaNameId
     */
    public function getAlphaNameId($name) {
        $params['name'] = $name;
        return $this->sendRequest('getAlphanameId', $params);
    }

    /**
     * Метод-обёртка для команды flashCall
     * $phone - номер телефона в формате 375291234567
     * $code - код подтверждения, если не указан сгенерируется автоматически
     * $attempt - количество попыток для подтверждения кода, если не указано то 3
     * $time_valid - время действия кода подтверждения в секундах, если не указано то 90
     */
    public function flashCall($phone, $code = '', $attempt = 0, $time_valid = 0) {
        $params['phone'] = $phone;
        if (!empty($code))
            $params['code'] = $code;
        if (!empty($attempt))
            $params['attempt'] = (integer)$attempt;
        if (!empty($time_valid))
            $params['time_valid'] = (integer)$time_valid;
        return $this->sendRequest('flashCall', $params, 'post');
    }

    /**
     * Метод-обёртка для команды confirmFlashCall
     * $phone - номер телефона в формате 375291234567
     * $code - код подтверждения
     * $fclid - значение fclid из метода flashCall
     */
    public function confirmFlashCall($phone, $code, $fclid) {
        $params['phone'] = $phone;
        $params['code'] = $code;
        $params['fclid'] = $fclid;
        return $this->sendRequest('confirmFlashCall', $params, 'post');
    }

    /**
     * Метод-обёртка для команды getVibernames
     */
    public function getVibernames() {
        return $this->sendRequest('getVibernames');
    }

    /**
     * Метод-обёртка для команды createViberMessage
     * $message - текст viber-сообщения
     * $vibername_id - ID viber-имени
     */
    public function createViberMessage($message, $vibername_id) {
        $params['message'] = $message;
        $params['vibername_id'] = $vibername_id;
        return $this->sendRequest('createViberMessage', $params, 'post');
    }

    /**
     * Метод-обёртка для команды sendViberMessage
     * $phone - номер телефона в формате 375291234567
     * $viber_message_id - ID viber-сообщения из метода createViberMessage
     */
    public function sendViberMessage($phone, $viber_message_id) {
        $params['phone'] = $phone;
        $params['viber_message_id'] = $viber_message_id;
        return $this->sendRequest('sendViberMessage', $params, 'post');
    }

    /**
     * Метод-обёртка для команды sendQuickViberMessage - отправка viber-сообщения без предварительного его создания
     * $phone - номер телефона в формате 375291234567
     * $vibername_id - ID viber-имени
     * $message - текст viber-сообщения
     */
    public function sendQuickViberMessage($phone, $vibername_id, $message) {
        $params['phone'] = $phone;
        $params['vibername_id'] = $vibername_id;
        $params['message'] = $message;
        return $this->sendRequest('sendQuickViberMessage', $params, 'post');
    }

    /**
     * Метод-обёртка для команды sendViberMessageList
     * $message - информация об viber-сообщении
     * $name - название рассылки
     * $vibername_id - ID viber-имени
     * $list_id - ID списка рассылки
     * $d_schedule - планируемое время отправки
     */
    public function sendViberMessageList($message, $name, $vibername_id, $list_id, $d_schedule) {
        $params['name'] = $name;
        $params['vibername_id'] = $vibername_id;
        $params['list_id'] = $list_id;
        $params['d_schedule'] = $d_schedule;
        $params = array_merge($params, $message);
        return $this->sendRequest('sendViberMessageList', $params, 'post');
    }

    /**
     * Метод-обёртка для команды getViberMessageList
     */
    public function getViberMessageList() {
        return $this->sendRequest('getViberMessageList');
    }

  }
