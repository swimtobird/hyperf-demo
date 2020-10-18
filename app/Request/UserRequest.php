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
namespace App\Request;

use Hyperf\Validation\Request\FormRequest;

class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
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
}
