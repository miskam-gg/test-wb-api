<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\Income;
use Carbon\Carbon;

class FetchIncomesCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fetch:incomes {dateFrom} {dateTo} {--page=1} {--limit=500}';

    /**
     * The console command description
     */
    protected $description = 'Fetch incomes from external API and store them in the database';

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
        $apiUrl = env('EXTERNAL_API_INCOMES_URL');

        $this->info("Fetching incomes from {$dateFrom} to {$dateTo} (Page: {$page}, Limit: {$limit})");

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
        $incomes = $data['data'] ?? $data;

        if (empty($incomes)) {
            $this->info("No incomes found.");
            return 0;
        }

        foreach ($incomes as $incomeData) {
            if (empty($incomeData['income_id'])) {
                $this->warn("Skipping income record due to missing income_id.");
                continue;
            }

            try {
                Income::updateOrCreate(
                    ['income_id' => $incomeData['income_id']],
                    [
                        'number'           => $incomeData['number'] ?? null,
                        'date'             => !empty($incomeData['date']) ? Carbon::parse($incomeData['date']) : null,
                        'last_change_date' => !empty($incomeData['last_change_date']) ? Carbon::parse($incomeData['last_change_date']) : null,
                        'supplier_article' => $incomeData['supplier_article'] ?? null,
                        'tech_size'        => $incomeData['tech_size'] ?? null,
                        'barcode'          => $incomeData['barcode'] ?? null,
                        'quantity'         => $incomeData['quantity'] ?? 0,
                        'total_price'      => $incomeData['total_price'] ?? 0,
                        'date_close'       => !empty($incomeData['date_close']) ? Carbon::parse($incomeData['date_close']) : null,
                        'warehouse_name'   => $incomeData['warehouse_name'] ?? null,
                        'nm_id'            => $incomeData['nm_id'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                $this->error("Error processing income: " . $e->getMessage());
            }
        }

        $this->info("Incomes fetched and stored successfully.");
        return 0;
    }
}
