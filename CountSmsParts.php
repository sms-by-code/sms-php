<?php
/**
*     Класс для подсчета длины смс, а также кол-ва частей в смс сообщении.
*/

class CountSmsParts
{
    public function __construct() {}


    protected function charCodeAt($str, $num) {
        return $this->utf8_ord($this->utf8_charAt($str, $num));
    }


    protected function utf8_ord($ch) {
        $len = strlen($ch);
        if ($len <= 0) return false;
        $h = ord($ch[0]);
        if ($h <= 0x7F) return $h;
        if ($h < 0xC2) return false;
        if ($h <= 0xDF && $len > 1) return ($h & 0x1F) << 6 | (ord($ch[1]) & 0x3F);
        if ($h <= 0xEF && $len > 2) return ($h & 0x0F) << 12 | (ord($ch[1]) & 0x3F) << 6 | (ord($ch[2]) & 0x3F);
        if ($h <= 0xF4 && $len > 3) return ($h & 0x0F) << 18 | (ord($ch[1]) & 0x3F) << 12 | (ord($ch[2]) & 0x3F) << 6 | (ord($ch[3]) & 0x3F);
        return false;
    }


    protected function utf8_charAt($str, $num) {
        return mb_substr($str, $num, 1, 'UTF-8');
    }


    public function countSmsParts($text) {
        $cutStrLength = 0;
        $s = [
            "cut" => true,
            "maxSmsNum" => 10,

            "counters" => [
                "message"=> 'smsCount',
                "character" => 'smsLength'
            ],

            "lengths" => [
                "ascii" => [160, 306, 459, 628, 785, 942, 1071, 1224, 1377, 1530],
                "unicode" => [70, 134, 201, 252, 315, 378, 469, 536, 603, 670],
            ]
        ];

        $smsLength = 0;
        $smsCount = -1;
        $isUnicode = false;

        $textLength = mb_strlen($text);

        for ($charPos = 0; $charPos < $textLength; $charPos++) {
            switch ($text[$charPos]) {
                case "\n":
                case "[":
                case "]":
                case "\\":
                case "^":
                case "{":
                case "}":
                case "|":
                case "€":
                    $smsLength += 2;
                    break;

                default:
                    $smsLength += 1;
            }

            if ($this->charCodeAt($text, $charPos) > 127 && $text[$charPos] != "€")
                $isUnicode = true;
        }

        $smsType = $isUnicode ? $s['lengths']['unicode'] : $s['lengths']['ascii'];

        for ($sCount = 0; $sCount < $s['maxSmsNum']; $sCount++) {
            $cutStrLength = $smsType[$sCount];
            if ($smsLength <= $smsType[$sCount]) {
                $smsCount = $sCount + 1;
                break;
            }
        }

        if ($s['cut']) mb_substr($text, 0, $cutStrLength);
        $smsCount == -1 && $smsCount = $s['maxSmsNum'];
        return $smsCount;
    }


    public function checkTextLength($text) {
        $cutStrLength = 0;
        $s = [
            "cut" => true,
            "maxSmsNum" => 10,

            "counters" => [
                "message"=> 'smsCount',
                "character" => 'smsLength'
            ],

            "lengths" => [
                "ascii" => [160, 306, 459, 628, 785, 942, 1071, 1224, 1377, 1530],
                "unicode" => [70, 134, 201, 252, 315, 378, 469, 536, 603, 670],
            ]
        ];

        $smsLength = 0;
        $smsCount = -1;
        $isUnicode = false;

        $textLength = mb_strlen($text);

        for ($charPos = 0; $charPos < $textLength; $charPos++) {
            switch ($text[$charPos]) {
                case "\n":
                case "[":
                case "]":
                case "\\":
                case "^":
                case "{":
                case "}":
                case "|":
                case "€":
                    $smsLength += 2;
                    break;

                default:
                    $smsLength += 1;
            }

            //!isUnicode && text.charCodeAt(charPos) > 127 && text[charPos] != "€" && (isUnicode = true)
            if ($this->charCodeAt($text, $charPos) > 127 && $text[$charPos] != "€")
                $isUnicode = true;
        }

        if($isUnicode && $textLength > 670) {
            return ['status' => 'error', 'len' => $textLength, 'max' => 670, 'type' => 'unicode'];
        }
        if(!$isUnicode && $textLength > 1530) {
            return ['status' => 'error', 'len' => $textLength, 'max' => 1530, 'type' => 'ascii'];
        }
        if($isUnicode) {
            foreach($s['lengths']['unicode'] as $k => $v) {
                if($textLength < $v) {
                    $smsCount = $k+1;
                    break;
                }
            }
        }
        if(!$isUnicode) {
            foreach($s['lengths']['ascii'] as $k => $v) {
                if($textLength < $v) {
                    $smsCount = $k+1;
                    break;
                }
            }
        }
        $smsCount = $this->countSmsParts($text);
        return ['status' => 'ok', 'parts' => $smsCount, 'len' => $textLength];
   }

}
