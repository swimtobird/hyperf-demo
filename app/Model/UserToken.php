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
namespace App\Model;

use Hyperf\DbConnection\Model\Model;

class UserToken extends Model
{
    const EFFECTIVE_TIME = 60 * 60 * 24 * 14;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'user_token';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

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
}
