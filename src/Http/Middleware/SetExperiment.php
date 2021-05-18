<?php

namespace Ringierimu\Experiments\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Ringierimu\Experiments\Facades\SdcExperiments;

class SetExperiment
{
    /**
     * Handle an incoming request.
     *
     * @param  Request $request
     * @param  \Closure $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        SdcExperiments::queueRequestCookie();

        return $response;
    }
}
