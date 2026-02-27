<?php
declare(strict_types=1);
namespace App\Validators;

use App\Core\BaseValidator;

class OrderValidator extends BaseValidator
{
    protected function rules(): array
    {
        return [
            'latitude' => [
                'required',       
                'numeric',          
                'min' => -90,       
                'max' => 90        
            ],

            'longitude' => [
                'required',         
                'numeric',         
                'min' => -180,      
                'max' => 180      
            ],

            'subtotal' => [
                'required',       
                'numeric',          
                'min' => 0         
            ],

            'tax_amount' => [        
                'numeric',
                'min' => 0
            ],

            'total_amount' => [
                'numeric',
                'min' => 0
            ],

            'id_tax' => [
                'integer',
                'min' => 1          
            ],
            
            'created_at' => [
       
            ]
        ];
    }
}