<?php

use Zizaco\Entrust\Entrust;
use Zizaco\Entrust\Middleware\NeedsRoleOrPermissionMiddleware;
use Mockery as m;

class EntrustMiddlewareNeedsRoleOrPermissionTest extends PHPUnit_Framework_TestCase
{
    public function testHandlerAbort()
    {
        $this->setExpectedException('Exception', 'Forbidden', 403);

        $roles = 'admin|member';
        $perms = 'insert|update';

        $entrust = m::mock('Zizaco\Entrust\Entrust');
        $entrust->shouldReceive('hasRolePerm')
            ->once()
            ->with(['admin','member'], ['insert','update'], false)
            ->andReturn(false);

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('abort')->with(403)->andThrow(new \Exception('Forbidden', 403));
        
        $request = m::mock('Illuminate\Http\Request');
        $request->shouldReceive('result')->never();
        $next = function($req) { return $req->result(); };

        $middleware = new NeedsRoleOrPermissionMiddleware($entrust, $app);
        $result = $middleware->handle($request, $next, $roles, $perms, false);
    }

    public function testHandlerSuccess()
    {
        $roles = 'admin|member';
        $perms = 'insert|update';

        $entrust = m::mock('Zizaco\Entrust\Entrust');
        $entrust->shouldReceive('hasRolePerm')
            ->once()
            ->with(['admin','member'], ['insert','update'], true)
            ->andReturn(true);

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('abort')->never();
        
        $request = m::mock('Illuminate\Http\Request');
        $request->shouldReceive('result')->once()->andReturn('Result Next Middleware');
        $next = function($req) { return $req->result(); };

        $middleware = new NeedsRoleOrPermissionMiddleware($entrust, $app);
        $result = $middleware->handle($request, $next, $roles, $perms);

        $this->assertEquals('Result Next Middleware', $result);
    }

    public function tearDown()
    {
        m::close();
    }
}