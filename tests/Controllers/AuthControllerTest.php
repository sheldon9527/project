<?php

class AuthControllerTest extends TestCase
{
    public function tearDown()
    {
        Mockery::close();
        parent::tearDown();
    }

    public function testLogin()
    {
        // 什么参数都不传
        $this->POST('api/auth/login')
            ->seeJsonEquals([
                'errors' => [
                    'The Password field is required.',
                    'The User Name field is required.',
                ],
            ])
            ->assertResponseStatus(400);

        // 密码格式不正确
        // 密码太短
        $params = [
            'username' => 1234567,
            'password' => '1234',
        ];
        $this->POST('api/auth/login', $params)
            ->seeJsonEquals([
                'errors' => ['The Password must be between 6 and 20 characters.'],
            ])
            ->assertResponseStatus(400);

        // 密码太长
        $params = [
            'username' => 1234567,
            'password' => str_random(21),
        ];
        $this->POST('api/auth/login', $params)
            ->seeJsonEquals([
                'errors' => ['The Password must be between 6 and 20 characters.'],
            ])
            ->assertResponseStatus(400);

        // 密码格式不对
        $params = [
            'username' => 13281898731,
            'password' => 'qwe123&*()',
        ];
        $this->POST('api/auth/login', $params)
            ->seeJsonEquals([
                'errors' => ['The Password may only contain letters and numbers.'],
            ])
            ->assertResponseStatus(400);

        $authAttributes = [];
        $authResult = false;
        JWTAuth::shouldReceive('attempt')->andReturnUsing(function ($credentials) use (&$authResult, &$authAttributes) {
            $authAttributes = $credentials;

            return $authResult;
        });

        // 手机号，格式正确密码不对
        $params = [
            'username' => 12345678,
            'password' => 123456,
        ];

        $expected = [
            'cellphone' => 12345678,
            'password' => 123456,
        ];

        $this->POST('api/auth/login', $params)
            ->seeJsonEquals([
                'errors' => ['Incorrect username or password'],
            ])
            ->assertResponseStatus(401);

        $this->assertEquals($expected, $authAttributes);

        // 邮箱，格式正确密码不对
        $params = [
            'username' => 'foo@bar.com',
            'password' => 123456,
        ];

        $expected = [
            'email' => 'foo@bar.com',
            'password' => 123456,
        ];

        $this->POST('api/auth/login', $params)
            ->seeJsonEquals([
                'errors' => ['Incorrect username or password'],
            ])
            ->assertResponseStatus(401);

        $this->assertEquals($expected, $authAttributes);

        // 密码正确
        $authResult = true;

        $updateResult = [];
        $userObject = (object) ['id' => 1, 'im_token' => 'im-token-success'];

        \Auth::shouldReceive('user')->andReturn($userObject);
        $userRepMock = Mockery::mock('App\Repositories\Contracts\UserRepositoryContract');
        $userRepMock->shouldReceive('update')->once()->andReturnUsing(function ($user, $attributes) use (&$updateResult) {
            $updateResult['user'] = $user;
            $updateResult['attributes'] = $attributes;

            return;
        });
        $this->app->instance('App\Repositories\Contracts\UserRepositoryContract', $userRepMock);

        $metaResult = [];
        $itemResult = null;
        $responseMock = Mockery::mock('Dingo\Api\Http\Response\Factory');
        $responseMock
            ->shouldReceive('addMeta')
            ->twice()
            ->andReturnUsing(function ($key, $value) use (&$metaResult, $responseMock) {
                $metaResult[$key] = $value;

                return $responseMock;
            })
            ->shouldReceive('item')
            ->once()
            ->andReturnUsing(function ($user) use (&$itemResult, $responseMock) {
                $itemResult = $user;

                return $responseMock;
            });
        $this->app->instance('Dingo\Api\Http\Response\Factory', $responseMock);

        $params = [
            'username' => 'foo@bar.com',
            'password' => 123456,
        ];

        $this->POST('api/auth/login', $params)
            ->assertResponseStatus(200);

        $this->assertEquals($metaResult, ['token' => 1, 'im_token' => 'im-token-success']);
        $this->assertEquals($itemResult, $userObject);
    }
}
