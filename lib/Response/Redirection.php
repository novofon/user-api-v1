<?php

namespace Novofon_API\Response;

/**
 * @deprecated
 */
class Redirection extends Response
{
    public $sip_id;
    public $status;
    public $condition;
    public $destination;
    public $destination_value;
}