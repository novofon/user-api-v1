<?php

use Novofon_API\Api;
use Novofon_API\Webhook\AbstractNotify;
use Novofon_API\Webhook\NotifyStart;
use Novofon_API\Webhook\Request;

require_once __DIR__.DIRECTORY_SEPARATOR.'include.php';

class WebhookExample {
    private static $api = null;

    /**
     * The system dictates the last 3 digits of the caller's number and exits.
     */
    public static function example()
    {
        /** @var NotifyStart $notify */
        $notify = self::getEvent([AbstractNotify::EVENT_START]);
        if (!$notify) {
            return;
        }
        $request = new Request();
        if ($notify->event == AbstractNotify::EVENT_START) {
            $request->setIvrSayDigits(mb_substr($notify->caller_id, -3), 'ru');
        }

        $request->send();
    }

    private static function getEvent($allowedTypes)
    {
        if (self::$api === null) {
            self::$api = new Api(KEY, SECRET, true);
        }
        return self::$api->getWebhookEvent($allowedTypes);
    }
}

WebhookExample::example();