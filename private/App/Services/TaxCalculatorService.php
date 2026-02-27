<?php
declare(strict_types=1);

namespace App\Services;

use App\Gateways\TaxGateway;

class TaxCalculatorService
{
    private static array $taxCache = [];

    public function __construct(private TaxGateway $taxGateway) {}

    public function calculate(?string $jurisdiction, float $subtotal): ?array
    {
        if ($jurisdiction === null) return null;

        $normalized = mb_strtolower(trim($jurisdiction));
        
        $nycDistricts = ['kings', 'queens', 'bronx', 'richmond', 'new york'];
        $lookupName = in_array($normalized, $nycDistricts) ? 'New York City' : $jurisdiction;

        // ВИПРАВЛЕНО: Узгодження назв між GeoJSON та базою податків
        if ($normalized === 'st lawrence') {
            $lookupName = 'St. Lawrence';
        }

        if (!isset(self::$taxCache[$lookupName])) {
            $tax = $this->taxGateway->getByJurisdiction($lookupName);

            if (!$tax) return null;

            self::$taxCache[$lookupName] = $tax;
        }

        $tax = self::$taxCache[$lookupName];

        // ВИПРАВЛЕНО: Точне округлення сум для уникнення розбіжностей у 1 копійку
        $taxRate = (float)$tax->composite_tax_rate;
        $taxAmount = round($subtotal * $taxRate, 2);
        $totalAmount = round($subtotal + $taxAmount, 2);

        return [
            'id_tax'       => $tax->id,
            'jurisdiction' => $tax->jurisdictions,
            'tax_rate'     => $taxRate,
            'tax_amount'   => $taxAmount,
            'total_amount' => $totalAmount
        ];
    }
}