<?php

namespace CaptJM\ClearCacheBundle;

use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

class CaptJMClearCacheBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return __DIR__;
    }
}