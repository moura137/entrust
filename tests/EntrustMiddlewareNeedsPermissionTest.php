<?php

use Zizaco\Entrust\Entrust;
use Zizaco\Entrust\Middleware\NeedsPermissionMiddleware;
use Mockery as m;

class EntrustMiddlewareNeedsPermissionTest extends PHPUnit_Framework_TestCase
{
    public function testHandlerAbort()
    {
        $this->setExpectedException('Exception', 'Forbidden', 403);

        $perms = 'insert|update';

        $entrust = m::mock('Zizaco\Entrust\Entrust');
        $entrust->shouldReceive('can')->once()->with(['insert','update'], false)->andReturn(false);

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('abort')->with(403)->andThrow(new \Exception('Forbidden', 403));
        
        $request = m::mock('Illuminate\Http\Request');
        $request->shouldReceive('result')->never();
        $next = function($req) { return $req->result(); };

        $middleware = new NeedsPermissionMiddleware($entrust, $app);
        $result = $middleware->handle($request, $next, $perms, false);
    }

    public function testHandlerSuccess()
    {
        $perms = 'insert|update';

        $entrust = m::mock('Zizaco\Entrust\Entrust');
        $entrust->shouldReceive('can')->once()->with(['insert','update'], true)->andReturn(true);

        $app = m::mock('Illuminate\Contracts\Foundation\Application');
        $app->shouldReceive('abort')->never();
        
        $request = m::mock('Illuminate\Http\Request');
        $request->shouldReceive('result')->once()->andReturn('Result Next Middleware');
        $next = function($req) { return $req->result(); };

        $middleware = new NeedsPermissionMiddleware($entrust, $app);
        $result = $middleware->handle($request, $next, $perms);

        $this->assertEquals('Result Next Middleware', $result);
    }

    public function tearDown()
    {
        m::close();
    }
}