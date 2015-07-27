<?php

namespace Zizaco\Entrust\Middleware;

use Closure;
use Zizaco\Entrust\Entrust;
use Illuminate\Contracts\Foundation\Application;

class NeedsRoleOrPermissionMiddleware
{
    /**
     * @var Entrust
     */
    protected $entrust;

    /**
     * Create a new middleware instance.
     *
     * @param  Entrust $entrust
     * @return void
     */
    public function __construct(Entrust $entrust, Application $app)
    {
        $this->entrust = $entrust;

        $this->app = $app;
    }

    /**
     * Handle an incoming request.
     *
     * @param  Illuminate\Http\Request $request
     * @param  Closure $next
     * @param  string  $permissions
     * @param  bool    $requireAll
     *
     * @return mixed
     */
    public function handle($request, Closure $next, $roles, $perms, $requireAll = true)
    {
        $roles = is_array($roles) ? $roles : explode("|", $roles);
        $perms = is_array($perms) ? $perms : explode("|", $perms);

        if (! $this->entrust->hasRolePerm($roles, $perms, $requireAll)) {
            $this->app->abort(403);
        }

        return $next($request);
    }
}
