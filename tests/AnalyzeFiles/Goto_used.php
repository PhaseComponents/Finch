<?php

namespace AnalyzeFiles;

class Goto_used
{
    public function used()
    {
        goto hell;
    }
}

hell: echo("Burn!");
