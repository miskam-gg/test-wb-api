<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use Carbon\Carbon;

class FetchOrdersCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fetch:orders {dateFrom} {dateTo} {--page=1} {--limit=500}';

    /**
     * The console command description
     */
    protected $description = 'Fetch orders from external API and store them in the database';

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
        $apiUrl = env('EXTERNAL_API_ORDERS_URL');

        $this->info("Fetching orders from {$dateFrom} to {$dateTo} (Page: {$page}, Limit: {$limit})");

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
        $orders = $data['data'] ?? $data;

        if (empty($orders)) {
            $this->info("No orders found.");
            return 0;
        }

        foreach ($orders as $orderData) {
            if (empty($orderData['g_number'])) {
                $this->warn("Skipping order record due to missing g_number.");
                continue;
            }

            try {
                Order::updateOrCreate(
                    ['g_number' => $orderData['g_number']],
                    [
                        'date'            => isset($orderData['date']) ? Carbon::parse($orderData['date']) : Carbon::now(),
                        'last_change_date'=> isset($orderData['last_change_date']) ? Carbon::parse($orderData['last_change_date']) : null,
                        'supplier_article'=> $orderData['supplier_article'] ?? null,
                        'tech_size'       => $orderData['tech_size'] ?? null,
                        'barcode'         => $orderData['barcode'] ?? null,
                        'total_price'     => $orderData['total_price'] ?? 0,
                        'discount_percent'=> $orderData['discount_percent'] ?? 0,
                        'warehouse_name'  => $orderData['warehouse_name'] ?? null,
                        'oblast'          => $orderData['oblast'] ?? null,
                        'income_id'       => $orderData['income_id'] ?? null,
                        'odid'            => $orderData['odid'] ?? null,
                        'nm_id'           => $orderData['nm_id'] ?? null,
                        'subject'         => $orderData['subject'] ?? null,
                        'category'        => $orderData['category'] ?? null,
                        'brand'           => $orderData['brand'] ?? null,
                        'is_cancel'       => $orderData['is_cancel'] ?? false,
                        'cancel_dt'       => !empty($orderData['cancel_dt']) ? Carbon::parse($orderData['cancel_dt']) : null,
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error processing order: " . $e->getMessage());
            }
        }

        $this->info("Orders fetched and stored successfully.");
        return 0;
    }
}
