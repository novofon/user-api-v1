<?php
namespace Novofon_API\Response;

/**
 * @deprecated
 */
class PbxRedirection extends Response
{
    public $current_status;
    public $pbx_id;
    public $pbx_name;
    public $type;
    public $destination;
    public $condition;
    public $voicemail_greeting;
    public $set_caller_id;
}