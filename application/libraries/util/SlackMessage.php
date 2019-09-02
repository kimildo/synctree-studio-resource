<?php

    namespace libraries\util;

    //use Interop\Container\ContainerInterface;
    use Psr\Container\ContainerInterface;
    use Slim\Http\Request;
    use libraries\log\LogMessage;

    class SlackMessage
    {
        const DEFAULT_CHANNEL_URL = 'https://hooks.slack.com/services/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';
        const DEFAULT_CHANNEL = '#error_webhook';
        const DEFAULT_USER = 'Error Bot';

        private $webHookUrl;
        private $channel;
        private $userName;
        private $message;
        private $iconEmoji;
        private $iconUrl;

        public function __construct($webHookUrl = self::DEFAULT_CHANNEL_URL, $userName = self::DEFAULT_USER, $channel = self::DEFAULT_CHANNEL)
        {
            $this->webHookUrl = $webHookUrl;
            $this->userName = $userName;
            $this->channel = $channel;
            $this->iconEmoji = '';
            $this->iconUrl = '';
        }

        public function setWebHookUrl($webHookUrl)
        {
            $this->webHookUrl = $webHookUrl;
        }

        public function setChannel($channel)
        {
            $this->channel = $channel;
        }

        public function setUserName($userName)
        {
            $this->userName = $userName;
        }

        public function setMessage($message, $len = 0, $suffix = '…')
        {
            $_message = strip_tags(trim($message));

            if ($len > 0) {
                $arrStr = preg_split("//u", $_message, -1, PREG_SPLIT_NO_EMPTY);
                $strLen = count($arrStr);

                if ($strLen >= $len) {
                    $sliceStr = array_slice($arrStr, 0, $len);
                    $str = join('', $sliceStr);

                    $_message = $str . ($strLen > $len ? $suffix : '');
                } else {
                    $_message = join('', $arrStr);
                }
            }

            $this->message = $_message;
        }

        private function setProtocol($url)
        {
            if ( ! $url) {
                return $url;
            }
            if ( ! preg_match('#^(http|https|ftp|telnet|news|mms)\://#i', $url)) {
                $url = 'http://' . $url;
            }

            return $url;
        }

        public function setLink($url = '', $title = '')
        {
            $url = trim($url);
            $title = trim($title);
            if ($url) {
                $url = str_replace(['<', '>'], '', $url);
                $_message = $this->message;
                $_message .= "\n<" . $this->setProtocol($url);
                if ($title) {
                    $title = str_replace(['<', '>'], '', $title);
                    $_message .= '|' . $title;
                }

                $_message .= '>';
            }

            $this->message = $_message;
        }

        public function setIconEmoji($iconEmoji)
        {
            $iconEmoji = strip_tags(trim($iconEmoji));

            if ($iconEmoji) {
                $this->iconEmoji = $iconEmoji;
            }
        }

        public function setIconUrl($iconUrl)
        {
            $iconUrl = strip_tags(trim($iconUrl));
            if ($iconUrl) {
                $this->iconUrl = $iconUrl;
            }
        }

        public function send()
        {
            $result = false;
            $data = [
                'channel'    => $this->channel,
                'username'   => $this->userName,
                'icon_emoji' => $this->iconEmoji,
                'icon_url'   => $this->iconUrl,
                'text'       => $this->message
            ];

            try {

                if ($this->webHookUrl) {
                    $command = 'curl';
                    $command .= ' -d \'payload=' . json_encode($data, JSON_UNESCAPED_UNICODE) . '\'';
                    $command .= ' -X POST ' . $this->webHookUrl . ' -s > /dev/null 2>&1 &';
                    LogMessage::info('curl command :: ' . $command);
                    $result = passthru($command);
                }

            } catch (\Exception $ex) {
                LogMessage::info('Slack Send Fail :: ' . json_encode($data, JSON_UNESCAPED_UNICODE));
            }

            return $result;

        }
    }