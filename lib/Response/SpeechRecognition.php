<?php

namespace Novofon_API\Response;


class SpeechRecognition extends Response
{
    public $lang;
    public $recognitionStatus;
    public $otherLangs;
    public $phrases;
    public $words;
}