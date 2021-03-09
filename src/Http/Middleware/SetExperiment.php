<?php

namespace Ringierimu\Experiments\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cookie;

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
        // all experiment user groups set in the cookie
        $cookieExperiments = $this->getCookieExperiments($request);

        // only set new experiment user groups for
        // experiments not in the cookie already
        $newExperiments = Collection::make(get_running_experiments())
            ->filter(
                function ($experimentKey) use ($cookieExperiments) {
                    return !in_array($experimentKey, $cookieExperiments);
                }
            )
            ->flatMap(
                function ($experimentKey) {
                    return [
                        "experiment_{$experimentKey}" => $this->randomUserGroup(),
                    ];
                }
            )
            ->all();

        // no new experiments to set
        if (!$newExperiments) {
            return $next($request);
        }

        Cookie::queue(
            'experiments',
            json_encode(
                array_merge(
                    $newExperiments,
                    $cookieExperiments
                )
            ),
            2628000 // forever
        );

        return $next($request);
    }

    /**
     * @param Request $request
     *
     * @return array
     */
    protected function getCookieExperiments($request): array
    {
        $experiments = $request->cookie('experiments') ?? '';

        if (!$experiments) {
            return [];
        }

        return (array) json_decode($experiments);
    }

    /**
     * @return string
     */
    protected function randomUserGroup(): string
    {
        return mt_rand(0, 1) ? 'test' : 'control';
    }
}
