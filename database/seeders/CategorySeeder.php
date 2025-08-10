<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Main categories
        $categories = [
            [
                'name' => 'إلكترونيات',
                'slug' => 'electronics',
                'description' => 'أحدث الأجهزة الإلكترونية والتكنولوجيا',
                'display_order' => 1,
                'is_active' => true,
                'meta_title' => 'إلكترونيات وأجهزة',
                'meta_description' => 'تسوق لأحدث الأجهزة الإلكترونية والتكنولوجيا بأسعار منافسة',
                'meta_keywords' => 'إلكترونيات, هواتف, لابتوبات, تكنولوجيا'
            ],
            [
                'name' => 'ملابس',
                'slug' => 'clothing',
                'description' => 'ملابس رجالية ونسائية وأطفال',
                'display_order' => 2,
                'is_active' => true,
                'meta_title' => 'ملابس وأزياء',
                'meta_description' => 'تسوق لأحدث الموديلات من الملابس للرجال والنساء والأطفال',
                'meta_keywords' => 'ملابس, أزياء, ألبسة, موضة'
            ],
            [
                'name' => 'منزل وديكور',
                'slug' => 'home',
                'description' => 'أثاث منزل وديكورات',
                'display_order' => 3,
                'is_active' => true,
                'meta_title' => 'أثاث منزل وديكور',
                'meta_description' => 'أحدث التصميمات في الأثاث المنزل والديكورات الداخلية',
                'meta_keywords' => 'أثاث, منزل, ديكور, أثاث منزلي'
            ],
            [
                'name' => 'جمال和个人护理',
                'slug' => 'beauty',
                'description' => 'منتجات العناية الشخصية والجمال',
                'display_order' => 4,
                'is_active' => true,
                'meta_title' => 'منتجات جمال وعناية شخصية',
                'meta_description' => 'تسوق لأفضل منتجات العناية الشخصية ومنتجات الجمال',
                'meta_keywords' => 'جمال, عناية شخصية, مستحضرات تجميل'
            ],
            [
                'name' => 'أطفال',
                'slug' => 'kids',
                'description' => 'ملابس وألعاب وأدوات أطفال',
                'display_order' => 5,
                'is_active' => true,
                'meta_title' => 'منتجات أطفال',
                'meta_description' => 'تسوق لملابس وألعاب وأدوات أطفال بأفضل الأسعار',
                'meta_keywords' => 'أطفال, ملابس أطفال, ألعاب أطفال'
            ],
            [
                'name' => 'رياضة',
                'slug' => 'sports',
                'description' => 'معدات رياضية وملابس رياضية',
                'display_order' => 6,
                'is_active' => true,
                'meta_title' => 'معدات رياضية وملابس رياضية',
                'meta_description' => 'تسوق لأفضل المعدات الرياضية وملابس الرياضة',
                'meta_keywords' => 'رياضة, معدات رياضية, ملابس رياضية'
            ],
            [
                'name' => 'سيارات',
                'slug' => 'cars',
                'description' => 'قطع غيار سيارات وملحقات',
                'display_order' => 7,
                'is_active' => true,
                'meta_title' => 'قطع غيار سيارات وملحقات',
                'meta_description' => 'تسوق لأفضل قطع الغيار وملحقات السيارات',
                'meta_keywords' => 'سيارات, قطع غيار, ملحقات سيارات'
            ],
            [
                'name' => 'كتب',
                'slug' => 'books',
                'description' => 'كتب وكتب إلكترونية',
                'display_order' => 8,
                'is_active' => true,
                'meta_title' => 'كتب وكتب إلكترونية',
                'meta_description' => 'تسوق لأفضل الكتب والكتب الإلكترونية بجميع أنواعها',
                'meta_keywords' => 'كتب, كتب إلكترونية, روايات, كتب تعليمية'
            ]
        ];

        // Create main categories
        foreach ($categories as $category) {
            Category::create($category);
        }

        // Create subcategories for electronics
        $electronics = Category::where('slug', 'electronics')->first();
        if ($electronics) {
            $subcategories = [
                [
                    'name' => 'هواتف محمولة',
                    'slug' => 'mobile-phones',
                    'description' => 'أحدث الهواتف المحمولة',
                    'parent_id' => $electronics->id,
                    'display_order' => 1,
                    'is_active' => true,
                    'meta_title' => 'هواتف محمولة',
                    'meta_description' => 'تسوق لأحدث الهواتف المحمولة',
                    'meta_keywords' => 'هواتف, محمولة, أيفون, سامسونج'
                ],
                [
                    'name' => 'لابتوبات',
                    'slug' => 'laptops',
                    'description' => 'أجهزة لابتوب محمولة',
                    'parent_id' => $electronics->id,
                    'display_order' => 2,
                    'is_active' => true,
                    'meta_title' => 'لابتوبات وأجهلة محمولة',
                    'meta_description' => 'تسوق لأجهزة اللابتوب المحمولة',
                    'meta_keywords' => 'لابتوب, أجهلة محمولة, كمبيوتر'
                ],
                [
                    'name' => 'تلفزيونات',
                    'slug' => 'televisions',
                    'description' => 'تلفزيونات ذكية وأجهزة عرض',
                    'parent_id' => $electronics->id,
                    'display_order' => 3,
                    'is_active' => true,
                    'meta_title' => 'تلفزيونات وأجهزة عرض',
                    'meta_description' => 'تسوق لأحدث التلفزيونات الذكية وأجهزة العرض',
                    'meta_keywords' => 'تلفزيون, شاشات, أجهزة عرض'
                ]
            ];

            foreach ($subcategories as $subcategory) {
                Category::create($subcategory);
            }
        }

        // Create subcategories for clothing
        $clothing = Category::where('slug', 'clothing')->first();
        if ($clothing) {
            $subcategories = [
                [
                    'name' => 'ملابس رجالية',
                    'slug' => 'mens-clothing',
                    'description' => 'ملابس رجالية عصرية',
                    'parent_id' => $clothing->id,
                    'display_order' => 1,
                    'is_active' => true,
                    'meta_title' => 'ملابس رجالية',
                    'meta_description' => 'تسوق لأحدث الموديلات من الملابس الرجالية',
                    'meta_keywords' => 'ملابس رجالية, رجالي, أزياء رجالية'
                ],
                [
                    'name' => 'ملابس نسائية',
                    'slug' => 'womens-clothing',
                    'description' => 'ملابس نسائية عصرية',
                    'parent_id' => $clothing->id,
                    'display_order' => 2,
                    'is_active' => true,
                    'meta_title' => 'ملابس نسائية',
                    'meta_description' => 'تسوق لأحدث الموديلات من الملابس النسائية',
                    'meta_keywords' => 'ملابس نسائية, نسائي, أزياء نسائية'
                ],
                [
                    'name' => 'ملابس أطفال',
                    'slug' => 'kids-clothing',
                    'description' => 'ملابس أطفال مريحة وجميلة',
                    'parent_id' => $clothing->id,
                    'display_order' => 3,
                    'is_active' => true,
                    'meta_title' => 'ملابس أطفال',
                    'meta_description' => 'تسوق لملابس الأطفال المريحة والجميلة',
                    'meta_keywords' => 'ملابس أطفال, أطفال, ملابس صبيان و بنات'
                ]
            ];

            foreach ($subcategories as $subcategory) {
                Category::create($subcategory);
            }
        }
    }
}
