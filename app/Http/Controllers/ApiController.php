<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Stock;
use Carbon\Carbon;

class ApiController extends Controller
{
    public function orders(Request $request)
    {
        $dateFrom = $request->query('dateFrom');
        $dateTo   = $request->query('dateTo');
        $limit    = $request->query('limit', 500);
        $page     = $request->query('page', 1);

        try {
            $from = Carbon::createFromFormat('Y-m-d', $dateFrom);
            $to   = Carbon::createFromFormat('Y-m-d', $dateTo);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        $orders = Order::whereBetween('created_at', [$from, $to])
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($orders);
    }

    public function stocks(Request $request)
    {
        $dateFrom = $request->query('dateFrom'); // ожидаем дату в формате Y-m-d
        $limit    = $request->query('limit', 500);
        $page     = $request->query('page', 1);

        try {
            $from = Carbon::createFromFormat('Y-m-d', $dateFrom);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format'], 400);
        }

        // Для "складов" выгружаем данные за текущий день (или дату, переданную в dateFrom)
        $stocks = Stock::whereDate('created_at', $from->toDateString())
            ->paginate($limit, ['*'], 'page', $page);

        return response()->json($stocks);
    }

    // Методы sales() и incomes() реализуйте аналогичным образом.
    public function sales(Request $request)
    {
        // Реализуйте логику получения данных для продаж
        return response()->json(['message' => 'Sales endpoint']);
    }

    public function incomes(Request $request)
    {
        // Реализуйте логику получения данных для доходов
        return response()->json(['message' => 'Incomes endpoint']);
    }
}
