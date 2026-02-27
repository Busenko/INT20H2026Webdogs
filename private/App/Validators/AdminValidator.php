<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\BaseValidator;

class AdminValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            
            'login' => [
                'required',
                'maxLength' => 100
            ],
        
            'password' => [
                'required',
                'minLength' => 8,
                'noTags'
            ]

        ];
    }
}