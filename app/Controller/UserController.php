<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace App\Controller;

use App\Request\UserRequest;
use App\Service\UserService;
use Hyperf\Di\Annotation\Inject;

class UserController extends AbstractController
{
    /**
     * @Inject
     * @var UserService
     */
    private $userService;

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function store(UserRequest $request)
    {
        $userData = $request->validated();

        $this->userService->register($userData);

        return success();
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function login(UserRequest $request)
    {
        $userData = $request->validated();

        $result = $this->userService->login($userData);

        return success($result);
    }
}
