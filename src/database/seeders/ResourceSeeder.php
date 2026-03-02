<?php
namespace Database\Seeders;

use App\Models\Resource;
use Illuminate\Database\Seeder;

class ResourceSeeder extends Seeder
{
    public function run(): void
    {
        $resources = [
            ['name' => 'Столик у окна',   'type' => 'standard', 'capacity' => 2,  'location' => 'Основной зал', 'price_per_hour' => 200,  'description' => 'Уютный столик с видом на улицу'],
            ['name' => 'Круглый столик',  'type' => 'standard', 'capacity' => 4,  'location' => 'Основной зал', 'price_per_hour' => 300,  'description' => 'Просторный столик в центре зала'],
            ['name' => 'VIP-кабинка',     'type' => 'vip',      'capacity' => 6,  'location' => 'VIP-зона',     'price_per_hour' => 800,  'description' => 'Отдельная кабинка с мягкими диванами'],
            ['name' => 'Столик на террасе','type' => 'terrace',  'capacity' => 4,  'location' => 'Терраса',      'price_per_hour' => 400,  'description' => 'Открытая терраса с видом на сад'],
            ['name' => 'Приватный зал',   'type' => 'private',  'capacity' => 15, 'location' => 'Отдельный зал','price_per_hour' => 1500, 'description' => 'Зал для корпоративов и торжеств'],
        ];

        foreach ($resources as $r) {
            Resource::create($r);
        }
    }
}
