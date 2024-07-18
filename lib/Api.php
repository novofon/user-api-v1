<?php

namespace Novofon_API;

use Novofon_API\Response\Balance;
use Novofon_API\Response\DirectNumber;
use Novofon_API\Response\IncomingCallsStatistics;
use Novofon_API\Response\NumberLookup;
use Novofon_API\Response\PbxInfo;
use Novofon_API\Response\PbxInternal;
use Novofon_API\Response\PbxRecording;
use Novofon_API\Response\PbxRecordRequest;
use Novofon_API\Response\PbxRedirection;
use Novofon_API\Response\PbxStatistics;
use Novofon_API\Response\PbxStatus;
use Novofon_API\Response\Price;
use Novofon_API\Response\Redirection;
use Novofon_API\Response\SipRedirection;
use Novofon_API\Response\SipRedirectionStatus;
use Novofon_API\Response\RequestCallback;
use Novofon_API\Response\Sip;
use Novofon_API\Response\SipCaller;
use Novofon_API\Response\SipStatus;
use Novofon_API\Response\Sms;
use Novofon_API\Response\SpeechRecognition;
use Novofon_API\Response\Statistics;
use Novofon_API\Response\Tariff;
use Novofon_API\Response\Timezone;
use Novofon_API\Response\WebrtcKey;
use Novofon_API\Response\Zcrm;
use Novofon_API\Webhook\AbstractNotify;
use Novofon_API\Webhook\NotifyAnswer;
use Novofon_API\Webhook\NotifyEnd;
use Novofon_API\Webhook\NotifyInternal;
use Novofon_API\Webhook\NotifyIvr;
use Novofon_API\Webhook\NotifyOutEnd;
use Novofon_API\Webhook\NotifyOutStart;
use Novofon_API\Webhook\NotifyRecord;
use Novofon_API\Webhook\NotifyStart;

class Api extends Client
{
    const VERSION = 'v1';

    const PBX_REDIRECTION_NO_GREETING = 'no';
    const PBX_REDIRECTION_STANDART_GREETING = 'standart';
    const PBX_REDIRECTION_OWN_GREETING = 'own';

    const IN_CALLS = 'in';
    const OUT_CALLS = 'out';

    /**
     * Return user balance.
     *
     * @return Balance
     * @throws ApiException
     */
    public function getBalance()
    {
        $data = $this->request('info/balance');
        return new Balance($data);
    }

    /**
     * Return user's timezone.
     *
     * @return Timezone
     * @throws ApiException
     */
    public function getTimezone()
    {
        $data = $this->request('info/timezone');
        return new Timezone($data);
    }

    /**
     * Request a callback.
     * @see https://zadarma.com/en/services/calls/callback/
     *
     * @param string from Your phone/SIP number, the PBX extension number or the PBX scenario,
     *  to which the CallBack is made.
     * @param string to The phone or SIP number that is being called.
     * @param null|string sip SIP user's number or the PBX extension number,
     *  which is used to make the call.
     * @param null|string predicted If this flag is specified the request is predicted
     *  (the system calls the “to” number, and only connects it to your SIP, or your phone number,
     *  if the call is successful.);
     * @return RequestCallback
     * @throws ApiException
     */
    public function requestCallback($from, $to, $sip = null, $predicted = null)
    {
        $params = [
            'from' => $from,
            'to' => self::filterNumber($to),
        ];
        $params = $params + self::filterParams([
            'sip' => is_null($sip) ? null : self::filterNumber($sip),
            'predicted' => $predicted,
        ]);
        $data = $this->request('request/callback', $params);
        return new RequestCallback($data);
    }

    /**
     * Return the list of user's SIP-numbers.
     *
     * @return array
     * @throws ApiException
     */
    public function getSip()
    {
        $data = $this->request('sip');
        unset($data['status']);
        if (is_array($data['sips']) && $data['sips']) {
            foreach ($data['sips'] as &$sipData) {
                $sipData = new Sip($sipData);
            }
        }
        return $data;
    }

    /**
     * Return the user's SIP number online status.
     *
     * @param $sipId
     * @return SipStatus
     * @throws ApiException
     */
    public function getSipStatus($sipId)
    {
        $data = $this->request('sip/' . self::filterNumber($sipId) . '/status');
        return new SipStatus($data);
    }

    /**
     * Return information about the user's phone numbers.
     * @return DirectNumber[]
     * @throws ApiException
     */
    public function getDirectNumbers()
    {
        $data = $this->request('direct_numbers');
        return self::arrayToResultObj($data['info'], DirectNumber::class);
    }

    /**
     * Return online status of the PBX extension number.
     * @return PbxInternal
     * @throws ApiException
     */
    public function getPbxInternal()
    {
        $data = $this->request('pbx/internal');
        return new PbxInternal($data);
    }

    /**
     * Return online status of the PBX extension number.
     * @param $pbxId
     * @return PbxStatus
     * @throws ApiException
     */
    public function getPbxStatus($pbxId)
    {
        $data = $this->request('pbx/internal/' . self::filterNumber($pbxId) . '/status');
        return new PbxStatus($data);
    }

    /**
     * Return call recording file request.
     * @param string|null $callId Unique call ID, it is specified in the name of the file with the call
     *  recording (unique for every recording)
     * @param string|null $pbxCallId Permanent ID of the external call to the PBX
     * @param integer|null $lifetime The link's lifetime in seconds (minimum - 180, maximum - 5184000, default - 1800)
     * @return PbxRecordRequest
     * @throws ApiException
     */
    public function getPbxRecord($callId, $pbxCallId, $lifetime = null)
    {
        $params = array_filter([
            'call_id' => $callId,
            'pbx_call_id' => $pbxCallId,
        ]);
        if (!$params) {
            throw new ApiException('callId or pbxCallId required');
        }
        if ($lifetime) {
            $params['lifetime'] = $lifetime;
        }
        $data = $this->request('pbx/record/request', $params);
        return new PbxRecordRequest($data);
    }

    /**
     * Return overall statistics.
     * Maximum period of getting statistics is - 1 month. If the limit in the request is exceeded, the time period
     * automatically decreases to 30 days. If the start date is not specified, the start of the current month will be
     * selected. If the end date is not specified, the current date and time will be selected.
     *
     * @param string|null $start The start date of the statistics display (format - y-m-d H:i:s)
     * @param string|null $end The end date of the statistics display (format - y-m-d H:i:s)
     * @param integer|null $sip Filter based on a specific SIP number
     * @param bool|null $costOnly Display only the amount of funds spent during a specific period
     * @param string|null $type Request type: overall (is not specified in the request), toll and ru495
     * @param integer|null $skip Number of lines to be skipped in the sample. The output begins from skip +1 line.
     * @param integer|null $limit The limit on the number of input lines
     *  (the maximum value is 1000, the default value is 1000)
     * @return Statistics
     * @throws ApiException
     */
    public function getStatistics(
        $start = null,
        $end = null,
        $sip = null,
        $costOnly = null,
        $type = null,
        $skip = null,
        $limit = null
    ) {
        $params = [
            'start' => $start,
            'end' => $end,
            'sip' => is_null($sip) ? null : self::filterNumber($sip),
            'cost_only' => $costOnly,
            'type' => $type,
            'skip' => $skip,
            'limit' => $limit,
        ];
        $data = $this->request('statistics', self::filterParams($params));
        return new Statistics($data);
    }

    /**
     * Return PBX statistics.
     * @see Api::getStatistics() For $start, $end, $skip, $limit parameters details.
     *
     * @param string|null $start
     * @param string|null $end
     * @param true|bool $newFormat Format of the statistics result.
     * @param integer|null $skip
     * @param integer|null $limit
     * @param string|null $callType IN_CALLS for incoming calls, OUT_CALLS for outgoing, null for both
     * @return PbxStatistics
     * @throws ApiException
     */
    public function getPbxStatistics(
        $start = null,
        $end = null,
        $newFormat = true,
        $callType = null,
        $skip = null,
        $limit = null
    ) {
        $params = [
            'start' => $start,
            'end' => $end,
            'version' => $newFormat ? 2 : 1,
            'skip' => $skip,
            'limit' => $limit,
            'call_type' => $callType,
        ];
        $data = $this->request('statistics/pbx', self::filterParams($params));
        return new PbxStatistics($data);
    }

    /**
     * Return CallBack widget statistics.
     * @see Api::getStatistics() For $start and $end parameters details.
     *
     * @param string|null $start
     * @param string|null $end
     * @param string|null $widget_id
     * @return PbxStatistics
     * @throws ApiException
     */
    public function getCallbackWidgetStatistics($start = null, $end = null, $widget_id = null)
    {
        $params = [
            'start' => $start,
            'end' => $end,
            'widget_id' => $widget_id,
        ];
        $data = $this->request('statistics/callback_widget', self::filterParams($params));
        return new PbxStatistics($data);
    }

    /**
     * Return overall incoming calls statistics.
     * Maximum period of getting statistics is - 1 month. If the limit in the request is exceeded, the time period
     * automatically decreases to 30 days. If the start date is not specified, the start of the current month will be
     * selected. If the end date is not specified, the current date and time will be selected.
     *
     * @param string|null $start The start date of the statistics display (format - y-m-d H:i:s)
     * @param string|null $end The end date of the statistics display (format - y-m-d H:i:s)
     * @param integer|null $sip Filter based on a specific SIP number
     * @param integer|null $skip Number of lines to be skipped in the sample. The output begins from skip +1 line.
     * @param integer|null $limit The limit on the number of input lines
     *  (the maximum value is 1000, the default value is 1000)
     * @return IncomingCallsStatistics
     * @throws ApiException
     */
    public function getIncomingCallStatistics($start = null, $end = null, $sip = null, $skip = null, $limit = null)
    {
        $params = [
            'start' => $start,
            'end' => $end,
            'sip' => is_null($sip) ? null : self::filterNumber($sip),
            'skip' => $skip,
            'limit' => $limit,
        ];
        $data = $this->request('statistics/incoming-calls', self::filterParams($params));
        return new IncomingCallsStatistics($data);
    }

    /**
     * Make request to api with error checking.
     *
     * @param $method
     * @param array $params
     * @param string $requestType
     * @return array
     * @throws ApiException
     */
    public function request($method, $params = [], $requestType = 'get')
    {
        $result = $this->call('/' . self::VERSION . '/' . $method . '/', $params, $requestType);

        $result = json_decode($result, true);
        if ((!empty($result['status']) && $result['status'] == 'error') || $this->getHttpCode() >= 400) {
            throw new ApiException($result['message'], $this->getHttpCode());
        }
        if ($result === null) {
            throw new ApiException('Wrong response', $this->getHttpCode());
        }
        return $result;
    }

    /**
     * Filter from non-digit symbols.
     *
     * @param string $number
     * @return string
     * @throws ApiException
     */
    protected static function filterNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);
        if (!$number) {
            throw new ApiException('Wrong number format.');
        }
        return $number;
    }

    /**
     * Remove null value items from params.
     * @param $params
     * @return mixed
     */
    protected static function filterParams($params)
    {
        foreach ($params as $k => $v) {
            if (is_null($v)) {
                unset($params[$k]);
            }
        }
        return $params;
    }

    /**
     * Convert items of array to object of given class name.
     *
     * @param array $array
     * @param string $resultClassName
     * @return array
     */
    protected static function arrayToResultObj($array, $resultClassName)
    {
        foreach ($array as &$item) {
            $item = new $resultClassName($item);
        }
        return $array;
    }
}
