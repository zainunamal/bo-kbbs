<?php

namespace App\Http\Controllers\API\client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TransactionClientController extends Controller
{
    public function index(Request $request)
    {
        $userId = Auth::id();

        // Ambil limit dari input, default 100, maksimal 100
        $limit = (int) $request->input('limit', 100);
        $limit = min(max($limit, 1), 100);

        // Cek data paling akhir (tanggal terbaru di DB)
        $latestDateRow = DB::connection('mysql2')->table('QRIS_TRANSACTION_AQUERIER_MAIN')->max('UPDATED_AT');

        if (!$latestDateRow) {
            return response()->json(['transaction' => []]); // tidak ada data sama sekali
        }

        $latestDate = \Carbon\Carbon::parse($latestDateRow)->endOfDay();
        $minDate = $latestDate->copy()->subDays(90)->startOfDay();

        // Ambil tanggal dari request (optional override)
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        try {
            $start = $startDate
                ? \Carbon\Carbon::parse($startDate)->startOfDay()
                : $minDate;

            $end = $endDate
                ? \Carbon\Carbon::parse($endDate)->endOfDay()
                : $latestDate;
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid date format. Use YYYY-MM-DD.'], 422);
        }

        // Validasi: pastikan tidak lebih dari 90 hari dari latest data
        if ($start->lt($minDate) || $end->gt($latestDate)) {
            return response()->json([
                'error' => 'Date range must be within 90 days before the latest data in the database (' . $latestDate->toDateString() . ').'
            ], 422);
        }

        // Query dasar
        $query = DB::connection('mysql2')->table('QRIS_TRANSACTION_AQUERIER_MAIN')
            ->join('user_has_merchant', 'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID', '=', 'user_has_merchant.MERCHANT_ID')
            ->join('users', 'user_has_merchant.USER_ID', '=', 'users.id')
            ->select('QRIS_TRANSACTION_AQUERIER_MAIN.*')
            ->whereBetween('QRIS_TRANSACTION_AQUERIER_MAIN.UPDATED_AT', [$start, $end])
            ->orderByDesc('QRIS_TRANSACTION_AQUERIER_MAIN.UPDATED_AT')
            ->limit($limit);

        if ($userId !== null && $userId != 1) {
            $query->where('users.id', $userId);
        }

        $data = $query->get();

        return response()->json(['transaction' => $data]);
    }

    public function status($id)
    {
        Log::channel('apiclient')->info('[REQUEST STATUS] Incoming status request : ', [
            'transaction_id' => $id
        ]);

        if (empty($id)) {
            return response()->json([
                'RC' => '0005',
                'RCM' => 'Transaction ID is required'
            ], 400);
        }

        try {
            $data = DB::connection('mysql2')->table('QRIS_TRANSACTION_AQUERIER_MAIN')
                ->leftJoin('QRIS_MERCHANT', 'QRIS_MERCHANT.ID', '=', 'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID')
                ->select(
                    'QRIS_TRANSACTION_AQUERIER_MAIN.STATUS',
                    DB::raw("CASE WHEN QRIS_TRANSACTION_AQUERIER_MAIN.STATUS = 1 THEN 'PAID' ELSE 'NOT PAID' END AS STATUS_DESC"),
                    DB::raw('QRIS_TRANSACTION_AQUERIER_MAIN.UPDATED_AT AS TRANSACTION_DATE'),
                    'QRIS_TRANSACTION_AQUERIER_MAIN.AMOUNT',
                    'QRIS_TRANSACTION_AQUERIER_MAIN.TRANSACTION_ID',
                    'QRIS_TRANSACTION_AQUERIER_MAIN.CUSTOMER_PAN',
                    'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ID',
                    'QRIS_TRANSACTION_AQUERIER_MAIN.MERCHANT_ACC_NUMBER',
                    'QRIS_MERCHANT.MERCHANT_NAME'
                )
                ->where('QRIS_TRANSACTION_AQUERIER_MAIN.TRANSACTION_ID', $id)
                ->orderBy('TRANSACTION_DATE', 'DESC')
                ->first();

            Log::channel('apiclient')->info('[RESPONSE STATUS] Status response : ', [
                'transaction_id' => $id,
                'response' => $data
            ]);

            if (!$data) {
                return response()->json([
                    'RC' => '0014',
                    'RCM' => 'Transaction not found'
                ], 404);
            }

            return response()->json([
                'RC' => '0000',
                'RCM' => 'Success',
                'data' => $data
            ]);
        } catch (\Exception $e) {
            Log::channel('apiclient')->error('[ERROR STATUS] Status error : ', [
                'message' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to get transaction status'
            ], 500);
        }
    }
}