<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Sale;
use Carbon\Carbon;

class FetchSalesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fetch:sales {dateFrom} {dateTo} {--page=1} {--limit=500}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch sales from external API and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dateFrom = $this->argument('dateFrom');
        $dateTo   = $this->argument('dateTo');
        $page     = $this->option('page');
        $limit    = $this->option('limit');

        $apiKey = env('EXTERNAL_API_KEY');
        $apiUrl = env('EXTERNAL_API_SALES_URL');

        $this->info("Fetching sales from {$dateFrom} to {$dateTo} (Page: {$page}, Limit: {$limit})");

        $response = Http::get($apiUrl, [
            'dateFrom' => $dateFrom,
            'dateTo'   => $dateTo,
            'page'     => $page,
            'limit'    => $limit,
            'key'      => $apiKey,
        ]);

        if (!$response->successful()) {
            $this->error("API request failed with status: " . $response->status());
            return 1;
        }

        $data = $response->json();
        $sales = $data['data'] ?? $data;

        if (empty($sales)) {
            $this->info("No sales found.");
            return 0;
        }

        foreach ($sales as $saleData) {
            if (empty($saleData['sale_id'])) {
                $this->warn("Skipping sale record due to missing sale_id.");
                continue;
            }

            try {
                Sale::updateOrCreate(
                    ['sale_number' => $saleData['sale_id']],
                    [
                        'amount' => $saleData['total_price'] ?? 0,
                        'created_at' => isset($saleData['date'])
                                        ? Carbon::createFromFormat('Y-m-d', $saleData['date'])
                                        : Carbon::now(),
                        'discount_percent' => $saleData['discount_percent'] ?? null,
                        'is_supply' => $saleData['is_supply'] ?? false,
                        'is_realization' => $saleData['is_realization'] ?? false,
                        'warehouse_name' => $saleData['warehouse_name'] ?? null,
                        'for_pay' => $saleData['for_pay'] ?? null,
                        'finished_price' => $saleData['finished_price'] ?? null,
                        'price_with_disc' => $saleData['price_with_disc'] ?? null,
                        'nm_id' => $saleData['nm_id'] ?? null,
                        'subject' => $saleData['subject'] ?? null,
                        'category' => $saleData['category'] ?? null,
                        'brand' => $saleData['brand'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error processing sale: " . $e->getMessage());
            }
        }

        $this->info("Sales fetched and stored successfully.");
        return 0;
    }
}
