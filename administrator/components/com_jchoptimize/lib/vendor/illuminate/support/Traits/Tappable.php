<?php

namespace _JchOptimizeVendor\Illuminate\Support\Traits;

use function _JchOptimizeVendor\tap;
trait Tappable
{
    /**
     * Call the given Closure with this instance then return the instance.
     *
     * @param  callable|null  $callback
     * @return mixed
     */
    public function tap($callback = null)
    {
        return tap($this, $callback);
    }
}
