<?php

namespace Novofon_API\Response;

/**
 * @deprecated
 */
class NumberLookup extends Response
{
    public $mcc;
    public $mnc;
    public $mccName;
    public $mncName;
    public $ported;
    public $roaming;
    public $errorDescription;
    public $status;
}