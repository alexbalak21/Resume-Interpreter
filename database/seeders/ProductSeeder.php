<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            [
                'reference'    => 'WEB-001',
                'name'         => 'Website Design',
                'description'  => 'Full responsive website design, up to 5 pages.',
                'product_unit' => 'project',
                'price'        => 150000, // €1500.00
                'page_url'     => '/products/web-design',
            ],
            [
                'reference'    => 'WEB-002',
                'name'         => 'Website Maintenance',
                'description'  => 'Monthly website maintenance and updates.',
                'product_unit' => 'month',
                'price'        => 9900, // €99.00
                'page_url'     => '/products/web-maintenance',
            ],
            [
                'reference'    => 'DEV-001',
                'name'         => 'Custom Development',
                'description'  => 'Custom feature development, billed per hour.',
                'product_unit' => 'hour',
                'price'        => 8500, // €85.00
                'page_url'     => '/products/custom-dev',
            ],
            [
                'reference'    => 'SEO-001',
                'name'         => 'SEO Audit',
                'description'  => 'Full SEO audit with recommendations report.',
                'product_unit' => 'report',
                'price'        => 45000, // €450.00
                'page_url'     => '/products/seo-audit',
            ],
        ];

        foreach ($products as $product) {
            DB::table('products')->insertOrIgnore(array_merge($product, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
