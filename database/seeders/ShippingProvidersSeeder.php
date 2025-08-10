<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ShippingProvider;
use Illuminate\Support\Facades\DB;

class ShippingProvidersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // بيانات مزودي الشحن
        $providers = [
            [
                'name' => 'Aramex',
                'code' => 'aramex',
                'description' => 'شركة أرامكس للشحن الدولي والمحلي',
                'logo' => 'images/shipping/aramex.png',
                'is_active' => true,
                'config' => json_encode([
                    'account_number' => 'your_account_number',
                    'account_pin' => 'your_account_pin',
                    'account_entity' => 'SAU',
                    'account_country_code' => 'SA',
                    'username' => 'aramex_username',
                    'password' => 'aramex_password'
                ]),
                'api_url' => 'https://ws.aramex.net/ShippingAPI',
                'supports_tracking' => true,
                'supports_label_generation' => true,
                'supports_scheduling' => false,
                'priority' => 100,
            ],
            [
                'name' => 'DHL Express',
                'code' => 'dhl',
                'description' => 'شركة DHL للشحن السريع الدولي',
                'logo' => 'images/shipping/dhl.png',
                'is_active' => true,
                'config' => json_encode([
                    'username' => 'dhl_username',
                    'password' => 'dhl_password'
                ]),
                'api_url' => 'https://express.api.dhl.com/mydhlapi',
                'api_key' => 'your_dhl_api_key',
                'api_secret' => 'your_dhl_api_secret',
                'supports_tracking' => true,
                'supports_label_generation' => true,
                'supports_scheduling' => true,
                'priority' => 90,
            ],
            [
                'name' => 'SMSA Express',
                'code' => 'smsa',
                'description' => 'سمسا للشحن السريع داخل المملكة العربية السعودية',
                'logo' => 'images/shipping/smsa.png',
                'is_active' => true,
                'config' => json_encode([
                    'passkey' => 'your_smsa_passkey'
                ]),
                'api_url' => 'https://api.smsa.sa/v2',
                'api_key' => 'your_smsa_api_key',
                'supports_tracking' => true,
                'supports_label_generation' => true,
                'supports_scheduling' => false,
                'priority' => 80,
            ],
            [
                'name' => 'Saudi Post (SPL)',
                'code' => 'spl',
                'description' => 'البريد السعودي للشحن المحلي والدولي',
                'logo' => 'images/shipping/spl.png',
                'is_active' => true,
                'config' => json_encode([
                    'username' => 'spl_username',
                    'password' => 'spl_password'
                ]),
                'api_url' => 'https://api.splonline.com.sa/v2',
                'api_key' => 'your_spl_api_key',
                'supports_tracking' => true,
                'supports_label_generation' => false,
                'supports_scheduling' => false,
                'priority' => 70,
            ],
            [
                'name' => 'شحن محلي',
                'code' => 'local',
                'description' => 'خيار الشحن المحلي الخاص بالمتجر',
                'logo' => 'images/shipping/local.png',
                'is_active' => true,
                'config' => '{}',
                'supports_tracking' => false,
                'supports_label_generation' => false,
                'supports_scheduling' => false,
                'priority' => 50,
            ],
        ];

        foreach ($providers as $provider) {
            ShippingProvider::create($provider);
        }

        $this->command->info('تم إضافة مزودي الشحن بنجاح');
    }
}