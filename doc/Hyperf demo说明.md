# Hyperf demo说明

## 定义目录结构

```sh
├── app
│   ├── Controller 控制器目录
│   ├── Exception 异常以及异常处理器目录
│   ├── Listener 监听器目录
│   ├── Middleware 中间件目录
│   ├── Model 数据模型目录
│   ├── Request 请求验证器目录
│   ├── Service 服务目录，业务逻辑处理，所有业务模块公用
│   └── helpers.php 辅助方法
```



##  调用逻辑
控制器  —–>验证器—–> 服务 —–> 模型

## 编写注册

> 该涉及控制器、请求验证器、服务、模型、异常、异常处理器开发及其相关配置

### 需求

保存新用户手机号与密码

### 开发过程
数据库定义
```mysql
 CREATE TABLE `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `phone` varchar(15) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

生成迁移
`php bin/hyperf.php gen:migration create_users_table`
生成User控制器
`php bin/hyperf.php gen:controller UserController`

生成User请求验证器

`php bin/hyperf.php gen:request UserRequest`

生成User模型

`php bin/hyperf.php gen:model User`

编写迁移脚步

```php
   //migration/xxxx_xx_xx_xxxxxx_create_users_table
		public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('phone', 15)->nullable();
            $table->string('password', 100)->nullable(false);
            $table->timestamp('created_at')
                ->default(Db::raw('CURRENT_TIMESTAMP'));
            $table->timestamp('updated_at')
                ->default(Db::raw('CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP'));
        });
    }
```



编写验证规则

```php
		// app/Request/UserRequest.php
    public function rules(): array
    {
        return [
            'phone' => [
                'required',
                'regex:' . china_tel_regex(),
            ],
            'password' => 'required|string',
        ];
    }

    public function messages(): array
    {
        return [
            'phone.required' => '请填写手机号',
            'phone.regex' => '填写合法手机号',
            'password.required' => '请填写密码',
        ];
    }
```
创建App/Exception/BusinessException.php

```php
//App/Exception/BusinessException.php
use Hyperf\Server\Exception\ServerException;

class BusinessException extends ServerException
{
}
```

创建app/Exception/ValidationExceptionHandler.php

```php
//app/Exception/ValidationExceptionHandler.php
<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\Utils\Arr;
use Hyperf\Validation\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        //阻止异常冒泡
        $this->stopPropagation();
        //返回自定义错误数据
        $result = [
            'status' => 1,
            'error' => current(Arr::dot($throwable->errors())),
        ];

        return $response
            ->withAddedHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(json_encode($result, JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof ValidationException;
    }
}

```
创建app/Exception/BusinessExceptionHandler.php

```php
//app/Exception/BusinessExceptionHandler.php
<?php

declare(strict_types=1);

namespace App\Exception\Handler;

use App\Exception\BusinessException;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class BusinessExceptionHandler extends ExceptionHandler
{
    public function handle(Throwable $throwable, ResponseInterface $response)
    {
        //阻止异常冒泡
        $this->stopPropagation();
        //返回自定义错误数据
        $result = [
            'status' => 1,
            'error' => $throwable->getMessage(),
        ];

        return $response
            ->withAddedHeader('content-type', 'application/json')
            ->withBody(new SwooleStream(json_encode($result, JSON_UNESCAPED_UNICODE)));
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof BusinessException;
    }
}

```

注册异常处理器

目前仅支持配置文件的形式注册 `异常处理器(ExceptionHandler)`，配置文件位于 `config/autoload/exceptions.php`，将您的自定义异常处理器配置在对应的 `server` 下即可：

```php
 // config/autoload/exceptions.php
 return [
    'handler' => [
        'http' => [
          /**
           * ......
           */
            App\Exception\Handler\ValidationExceptionHandler::class,
            App\Exception\Handler\BusinessExceptionHandler::class
        ],
    ],
];
```

创建 app/Service/UserService.php，编写注册方法

```php
    // app/Service/UserService.php
		public function register(array $data)
    {
        $exists = User::query()->where('phone', Arr::get($data, 'phone'))->exists();

        if ($exists) {
            throw new BusinessException('手机号已被注册');
        }

        $data = Arr::set($data, 'password', md5($data['password']));

        $user = new User();

        $user->phone = Arr::get($data, 'phone');
        $user->password = Arr::get($data, 'password');
        $user->save();

        return $user;
    }
```

编写控制器调用处理方法

```php
//app/Controller/UserController.php
		public function store(UserRequest $request)
    {
        //返回通过检验的数组
        $userData = $request->validated();

        $this->userService->register($userData);

        return success();
    }
```

配置路由

```php
//config/route.php
Router::addRoute('POST', '/user', 'App\Controller\UserController@store');
```



## 编写解密中间件

> 该模块涉及中间件开发及配置

### 需求

在中间件中统一处理客户端加密的请求参数，解密后注入Request中

### 开发过程

生成解密中间件
`php bin/hyperf.php gen:middleware DecryptMiddleware`

在app/helpers.php添加解密方法

```php
if (! function_exists('encrypt')) {
    /**
     * @param $data
     * @return string
     */
    function encrypt($data)
    {
        $data = openssl_encrypt(
            json_encode($data),
            'aes-128-ecb',
            env('AES_PRIVATE_KEY'),
            OPENSSL_RAW_DATA
        );
        return base64_encode($data);
    }
}
```

编辑解密中间件方法

```php
//app/Middleware/DecryptMiddleware.php
<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Response;
use Hyperf\Utils\Context;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class DecryptMiddleware implements MiddlewareInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(ContainerInterface $container, RequestInterface $request)
    {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $requestData = $this->request->input('requestData');

        if ($requestData) {
            $requestData = decrypt($requestData);
            if ($requestData) {
                $parms = array_merge($this->request->all(), $requestData);
                $request = $request->withQueryParams($parms);

                /*
                 * 由于Hyperf\HttpServer\Request的getInputData是放协程中并且是自命名，需要指定更新改变对象才能传递上下文
                 */
                Context::override('http.request.parsedData', function () use ($request) {
                    if (is_array($request->getParsedBody())) {
                        $data = $request->getParsedBody();
                    } else {
                        $data = [];
                    }

                    return array_merge($data, $request->getQueryParams());
                });
                $request = Context::set(ServerRequestInterface::class, $request);
            }
        }

        return $handler->handle($request);
    }
}

```

配置中间件

```php
//config/autoload/middleware.php
return [
    'http' => [
          /**
           * ......
           */    
        App\Middleware\DecryptMiddleware::class,
    ],
];
```

## 编写登录

> 该涉及控制器、请求验证器、服务、模型、缓存、异常、异常处理器开发及其相关配置

### 需求
通过手机号与密码登录，返回登录成功凭证

### 开发过程

数据库定义

```mysql
CREATE TABLE `user_token` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `access_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `refresh_token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `effective_time` int(10) unsigned NOT NULL DEFAULT '604800' COMMENT '有限时长(单位秒)',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```
编写模型

```php
  //app/Model/UserToken 
		const EFFECTIVE_TIME = 60 * 60 * 24 * 14;

    public static function createToken($user_id)
    {
        $access_token = md5($user_id . time() . random_bytes(2));
        $refresh_token = md5($user_id . time() . random_bytes(2));

        $data = [
            'user_id' => $user_id,
            'access_token' => $access_token,
            'refresh_token' => $refresh_token,
            'effective_time' => self::EFFECTIVE_TIME,
        ];

        self::insert($data);
        
        return $data;
    }
```

编写登录方法

```php
    // app/Service/UserService.php
		public function login(array $data)
    {
        $user = User::getFirstByWhere(['phone' => Arr::get($data, 'phone')]);

        if (! $user) {
            throw new BusinessException('用户不存在');
        }

        $hash = password_hash(md5(Arr::get($data, 'password')), PASSWORD_DEFAULT);
        if (! password_verify($user->password, $hash)) {
            throw new BusinessException('密码错误');
        }

        $result = UserToken::createToken($user->id);

        $result = Arr::except($result, 'user_id');

        cache()->set(
            'token:' . Arr::get($result, 'access_token'),
            $user->id,
            Arr::get($result, 'effective_time')
        );

        return $result;
    }

```

编写控制器调用处理方法

```php
		//app/Controller/UserController.php
		public function login(UserRequest $request)
    {
        $userData = $request->validated();

        $result = $this->userService->login($userData);

        return success($result);
    }
```

配置路由

```php
//config/route.php
Router::addRoute('POST', '/user/token', 'App\Controller\UserController@login');
```

