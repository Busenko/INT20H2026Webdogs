<?php

declare(strict_types=1);

namespace App\Validators;

use App\Core\BaseValidator;

class TaxValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
          
            'composite_tax_rate' => [
                'required',
                'numeric',
                'min' => 0
            ],

            'state_rate' => [
                'numeric',
                'min' => 0
            ],
            
            'county_rate' => [
                'numeric',
                'min' => 0
            ],
            
            'city_rate' => [
                'numeric',
                'min' => 0
            ],
            
            'special_rates' => [
                'numeric',
                'min' => 0
            ],

       
            'jurisdictions' => [
                'noTags',  
                'maxLength' => 255
            ]
        ];
    }
}