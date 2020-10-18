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
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Http\Message\ResponseInterface as Psr7ResponseInterface;
use Psr\SimpleCache\CacheInterface;

/*
 * 获取Container
 */
if (! function_exists('di')) {
    /**
     * Finds an entry of the container by its identifier and returns it.
     * @param null|mixed $id
     * @return mixed|\Psr\Container\ContainerInterface
     */
    function di($id = null)
    {
        $container = ApplicationContext::getContainer();
        if ($id) {
            return $container->get($id);
        }
        return $container;
    }
}

if (! function_exists('success')) {
    /**
     * @param array $data
     */
    function success($data = []): Psr7ResponseInterface
    {
        if (empty($data)) {
            $data = ['status' => 0];
        } else {
            $data = array_merge(['status' => 0], $data);
        }

        $mcryptResult = encrypt($data);
        $result['output'] = $mcryptResult;
        if (is_debug()) {
            $result['output_debug']['sdk_version_name'] = '1.0.0';
            $result['output_debug']['privatekey'] = env('AES_PRIVATE_KEY');
            $result['output_debug']['data'] = $data;
        }

        /**
         * @param ResponseInterface $response
         */
        $response = di()->get(ResponseInterface::class);

        return $response->json($result);
    }
}

/*
 * 中国大陆手机号规则
 */
if (! function_exists('china_tel_regex')) {
    /**
     * @return string
     */
    function china_tel_regex()
    {
        return '/^(?:\+?86)?1(?:3\d{3}|5[^4\D]\d{2}|8\d{3}|7(?:[01356789]\d{2}|4(?:0\d|1[0-2]|9\d))
        |9[189]\d{2}|6[567]\d{2}|4[579]\d{2})\d{6}$/';
    }
}

/*
 * 缓存
 */
if (! function_exists('cache')) {
    /**
     * @return CacheInterface|mixed
     */
    function cache()
    {
        return di()->get(CacheInterface::class);
    }
}

if (! function_exists('is_debug')) {
    /**
     * @return bool
     */
    function is_debug()
    {
        return env('APP_ENV') !== 'prod';
    }
}

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

if (! function_exists('decrypt')) {
    /**
     * @param $data
     * @return false|string
     */
    function decrypt($data)
    {
        $encrypted = base64_decode($data);
        return json_decode(openssl_decrypt(
            $encrypted,
            'aes-128-ecb',
            env('AES_PRIVATE_KEY'),
            OPENSSL_RAW_DATA
        ), true);
    }
}
