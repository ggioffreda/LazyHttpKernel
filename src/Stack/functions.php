<?php

namespace Stack;

function lazy(callable $factory, $terminable = false)
{
    return $terminable ? new LazyTerminableHttpKernel($factory) : new LazyHttpKernel($factory);
}
