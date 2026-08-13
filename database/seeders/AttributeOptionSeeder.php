<?php

namespace Database\Seeders;

use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AttributeOptionSeeder extends Seeder
{
    public function run(): void
    {
        $attributesData = [
            [
                'name' => 'Size',
                'type' => 'button',
                'values' => [
                    ['value' => 'XS'],
                    ['value' => 'S'],
                    ['value' => 'M'],
                    ['value' => 'L'],
                    ['value' => 'XL'],
                    ['value' => 'XXL'],
                    ['value' => '3XL'],
                    ['value' => '28'],
                    ['value' => '30'],
                    ['value' => '32'],
                    ['value' => '34'],
                    ['value' => '36'],
                    ['value' => '38'],
                    ['value' => '40'],
                    ['value' => '42'],
                ]
            ],
            [
                'name' => 'Color',
                'type' => 'color',
                'values' => [
                    ['value' => 'Black', 'color_code' => '#000000'],
                    ['value' => 'White', 'color_code' => '#FFFFFF'],
                    ['value' => 'Red', 'color_code' => '#EF4444'],
                    ['value' => 'Blue', 'color_code' => '#3B82F6'],
                    ['value' => 'Navy Blue', 'color_code' => '#1E3A8A'],
                    ['value' => 'Sky Blue', 'color_code' => '#38BDF8'],
                    ['value' => 'Grey', 'color_code' => '#6B7280'],
                    ['value' => 'Green', 'color_code' => '#10B981'],
                    ['value' => 'Maroon', 'color_code' => '#800000'],
                    ['value' => 'Olive', 'color_code' => '#808000'],
                    ['value' => 'Pink', 'color_code' => '#EC4899'],
                    ['value' => 'Khaki', 'color_code' => '#F0E68C'],
                    ['value' => 'Brown', 'color_code' => '#78350F'],
                    ['value' => 'Beige', 'color_code' => '#F5F5DC'],
                    ['value' => 'Lavender', 'color_code' => '#E6E6FA'],
                ]
            ],
            [
                'name' => 'Fabric',
                'type' => 'select',
                'values' => [
                    ['value' => 'Cotton'],
                    ['value' => 'Polo'],
                    ['value' => 'Jersey'],
                    ['value' => 'Linen'],
                    ['value' => 'Polyester'],
                    ['value' => 'Cotton Blend'],
                    ['value' => 'Oxford'],
                    ['value' => 'Denim'],
                    ['value' => 'Flannel'],
                    ['value' => 'Gabardine'],
                    ['value' => 'Chino'],
                    ['value' => 'Georgette'],
                    ['value' => 'Chiffon'],
                    ['value' => 'Silk'],
                    ['value' => 'Rayon'],
                    ['value' => 'Crepe'],
                ]
            ],
            [
                'name' => 'Fit',
                'type' => 'select',
                'values' => [
                    ['value' => 'Regular Fit'],
                    ['value' => 'Slim Fit'],
                    ['value' => 'Oversized'],
                    ['value' => 'Relaxed Fit'],
                    ['value' => 'Comfort Fit'],
                    ['value' => 'Straight Fit'],
                    ['value' => 'Skinny Fit'],
                    ['value' => 'A-Line'],
                    ['value' => 'Regular'],
                    ['value' => 'Slim'],
                    ['value' => 'Relaxed'],
                ]
            ],
            [
                'name' => 'Sleeve',
                'type' => 'select',
                'values' => [
                    ['value' => 'Full Sleeve'],
                    ['value' => 'Half Sleeve'],
                    ['value' => 'Sleeveless'],
                    ['value' => '3/4 Sleeve'],
                ]
            ],
            [
                'name' => 'Length',
                'type' => 'select',
                'values' => [
                    ['value' => 'Regular'],
                    ['value' => 'Ankle'],
                    ['value' => 'Full Length'],
                    ['value' => 'Mini'],
                    ['value' => 'Midi'],
                    ['value' => 'Maxi'],
                ]
            ],
        ];

        foreach ($attributesData as $data) {
            $attr = Attribute::firstOrCreate(
                ['code' => Str::slug($data['name'])],
                ['name' => $data['name'], 'type' => $data['type']]
            );

            foreach ($data['values'] as $valData) {
                AttributeValue::firstOrCreate(
                    [
                        'attribute_id' => $attr->id,
                        'value' => $valData['value'],
                    ],
                    [
                        'color_code' => $valData['color_code'] ?? null,
                    ]
                );
            }
        }
    }
}
