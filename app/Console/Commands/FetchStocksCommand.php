<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Stock;
use Carbon\Carbon;

class FetchStocksCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fetch:stocks {date} {--page=1} {--limit=500}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch stocks from external API and store them in the database';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $date  = $this->argument('date');
        $page  = $this->option('page');
        $limit = $this->option('limit');

        $apiKey = env('EXTERNAL_API_KEY');
        $apiUrl = env('EXTERNAL_API_STOCKS_URL');

        $this->info("Fetching stocks for date {$date} (Page: {$page}, Limit: {$limit})");

        $response = Http::get($apiUrl, [
            'dateFrom' => $date,
            'page'     => $page,
            'limit'    => $limit,
            'key'      => $apiKey,
        ]);

        if (!$response->successful()) {
            $this->error("API request failed with status: " . $response->status());
            return 1;
        }

        $data = $response->json();
        $stocks = $data['data'] ?? $data;

        if (empty($stocks)) {
            $this->info("No stocks found.");
            return 0;
        }

        foreach ($stocks as $stockData) {
            if (empty($stockData['barcode'])) {
                $this->warn("Skipping stock record due to missing barcode.");
                continue;
            }

            try {
                Stock::updateOrCreate(
                    [
                        'barcode'        => $stockData['barcode'],
                        'warehouse_name' => $stockData['warehouse_name'],
                        'date'           => $stockData['date'],
                    ],
                    [
                        'last_change_date'  => !empty($stockData['last_change_date']) ? Carbon::parse($stockData['last_change_date']) : null,
                        'quantity'          => $stockData['quantity'] ?? 0,
                        'quantity_full'     => $stockData['quantity_full'] ?? null,
                        'is_supply'         => $stockData['is_supply'] ?? null,
                        'is_realization'    => $stockData['is_realization'] ?? null,
                        'in_way_to_client'  => $stockData['in_way_to_client'] ?? null,
                        'in_way_from_client'=> $stockData['in_way_from_client'] ?? null,
                        'nm_id'             => $stockData['nm_id'] ?? null,
                        'sc_code'           => $stockData['sc_code'] ?? null,
                        'price'             => $stockData['price'] ?? null,
                        'discount'          => $stockData['discount'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error processing stock: " . $e->getMessage());
            }
        }

        $this->info("Stocks fetched and stored successfully.");
        return 0;
    }
}
