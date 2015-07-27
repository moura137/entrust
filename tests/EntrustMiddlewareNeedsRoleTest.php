<?php

use Zizaco\Entrust\Entrust;
use Zizaco\Entrust\Middleware\NeedsRoleMiddleware;
use Mockery as m;

class EntrustMiddlewareNeedsRoleTest extends PHPUnit_Framework_TestCase
{
    public function testHandlerAbort()
    {
        $this->setExpectedException('Exception', 'Forbidden', 403);

        $roles = 'admin|member';

        $entrust = m::mock('Zizaco\Entrust\Entrust');
        $entrust->shouldReceive('hasRole')->once()->with(['admin','member'], false)->andReturn(false);

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('abort')->with(403)->andThrow(new \Exception('Forbidden', 403));
        
        $request = m::mock('Illuminate\Http\Request');
        $request->shouldReceive('result')->never();
        $next = function($req) { return $req->result(); };

        $middleware = new NeedsRoleMiddleware($entrust, $app);
        $result = $middleware->handle($request, $next, $roles, false);
    }

    public function testHandlerSuccess()
    {
        $roles = 'admin|member';

        $entrust = m::mock('Zizaco\Entrust\Entrust');
        $entrust->shouldReceive('hasRole')->once()->with(['admin','member'], true)->andReturn(true);

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('abort')->never();
        
        $request = m::mock('Illuminate\Http\Request');
        $request->shouldReceive('result')->once()->andReturn('Result Next Middleware');
        $next = function($req) { return $req->result(); };

        $middleware = new NeedsRoleMiddleware($entrust, $app);
        $result = $middleware->handle($request, $next, $roles);

        $this->assertEquals('Result Next Middleware', $result);
    }

    public function tearDown()
    {
        m::close();
    }
}