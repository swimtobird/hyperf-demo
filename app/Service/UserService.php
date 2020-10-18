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
namespace App\Service;

use App\Exception\BusinessException;
use App\Model\User;
use App\Model\UserToken;
use Hyperf\Utils\Arr;

class UserService
{
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
}
