<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TaxRate;

class TaxService
{
    public function isTaxEnabled(): bool
    {
        return (bool) setting('tax_system_enabled', true);
    }

    public function taxAppliesToDelivery(): bool
    {
        return (bool) setting('tax_applies_to_delivery', false);
    }

    public function getActiveRates()
    {
        return TaxRate::where('is_active', true)->orderBy('is_default', 'desc')->orderBy('name', 'asc')->get();
    }

    public function getDefaultTaxRate(): ?TaxRate
    {
        return TaxRate::where('is_active', true)->where('is_default', true)->first();
    }

    public function getEffectiveTaxRateForProduct(?Product $product): ?TaxRate
    {
        if (!$product) {
            return $this->getDefaultTaxRate();
        }

        if ($product->is_tax_exempt) {
            return null; // Product is tax-exempt
        }

        if ($product->tax_rate_id && $product->relationLoaded('taxRate') && $product->taxRate && $product->taxRate->is_active) {
            return $product->taxRate;
        }

        if ($product->tax_rate_id) {
            $specific = TaxRate::where('id', $product->tax_rate_id)->where('is_active', true)->first();
            if ($specific) {
                return $specific;
            }
        }

        return $this->getDefaultTaxRate();
    }

    public function calculateTax($items = [], float $subtotal = 0.0, float $discount = 0.0, float $shippingFee = 0.0): array
    {
        if (!$this->isTaxEnabled()) {
            return [
                'tax_enabled' => false,
                'tax_name' => 'No Tax',
                'tax_rate' => 0.00,
                'tax_amount' => 0.00,
                'formatted_tax_amount' => format_price(0.00),
                'tax_applies_to_delivery' => false,
                'snapshot' => [
                    'tax_enabled' => false,
                    'tax_name' => 'Tax Disabled',
                    'tax_rate' => 0.00,
                    'tax_amount' => 0.00,
                    'tax_applies_to_delivery' => false,
                ]
            ];
        }

        $defaultTax = $this->getDefaultTaxRate();
        $defaultRatePercent = $defaultTax ? (float) $defaultTax->rate : 0.00;
        $defaultTaxName = $defaultTax ? $defaultTax->name : 'VAT';

        $totalTaxAmount = 0.00;
        $netSubtotal = max(0.0, $subtotal - $discount);

        // Calculate tax per item if items are provided, or apply rate on net subtotal
        if (!empty($items) && is_iterable($items)) {
            $taxableSubtotal = 0.0;
            $itemsTaxSum = 0.0;

            foreach ($items as $item) {
                $product = is_object($item) ? ($item->product ?? ($item instanceof Product ? $item : null)) : null;
                $effectiveTaxRate = $this->getEffectiveTaxRateForProduct($product);
                
                $unitPrice = is_object($item) && isset($item->price) ? (float)$item->price : ($product ? (float)$product->price : 0.0);
                $qty = is_object($item) && isset($item->quantity) ? (int)$item->quantity : 1;
                $itemTotal = $unitPrice * $qty;

                if ($effectiveTaxRate) {
                    $itemTaxRatePercent = (float) $effectiveTaxRate->rate;
                    $itemsTaxSum += ($itemTotal * ($itemTaxRatePercent / 100));
                    $taxableSubtotal += $itemTotal;
                }
            }

            // Proportionally adjust for subtotal discount
            if ($subtotal > 0 && $netSubtotal < $subtotal) {
                $discountRatio = $netSubtotal / $subtotal;
                $totalTaxAmount = $itemsTaxSum * $discountRatio;
            } else {
                $totalTaxAmount = $itemsTaxSum;
            }
        } else {
            // Apply default tax rate on net subtotal
            $totalTaxAmount = $netSubtotal * ($defaultRatePercent / 100);
        }

        // Check if tax applies to delivery charge
        if ($this->taxAppliesToDelivery() && $shippingFee > 0) {
            $deliveryTaxRate = $defaultRatePercent;
            $totalTaxAmount += ($shippingFee * ($deliveryTaxRate / 100));
        }

        $totalTaxAmount = round($totalTaxAmount, 2);

        return [
            'tax_enabled' => true,
            'tax_name' => $defaultTaxName,
            'tax_rate' => $defaultRatePercent,
            'tax_amount' => $totalTaxAmount,
            'formatted_tax_amount' => format_price($totalTaxAmount),
            'tax_applies_to_delivery' => $this->taxAppliesToDelivery(),
            'snapshot' => [
                'tax_enabled' => true,
                'tax_name' => $defaultTaxName,
                'tax_rate' => $defaultRatePercent,
                'tax_amount' => $totalTaxAmount,
                'tax_applies_to_delivery' => $this->taxAppliesToDelivery(),
            ]
        ];
    }
}
