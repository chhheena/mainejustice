<?php

namespace _JchOptimizeVendor\Illuminate\View\Engines;

use _JchOptimizeVendor\Illuminate\Contracts\View\Engine;
class FileEngine implements Engine
{
    /**
     * Get the evaluated contents of the view.
     *
     * @param  string  $path
     * @param  array  $data
     * @return string
     */
    public function get($path, array $data = [])
    {
        return \file_get_contents($path);
    }
}
