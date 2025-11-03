<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Widok z filtrami i <canvas> */
    public function index(Request $request)
    {
        return view('raporty.index', [
            'defaultPeriod' => $request->query('period', 'year'), // było 'month'
            'defaultFrom'   => $request->query('from', now()->firstOfYear()->format('Y-m-d')),
            'defaultTo'     => $request->query('to',   now()->format('Y-m-d')),
            'defaultRankBy' => $request->query('rankBy', 'qty'),
            'defaultLimit'  => (int)$request->query('limit', 10),
        ]);
    }


    /** JSON: Obrót (suma_brutto) w czasie */
    public function obrotData(Request $request)
    {
        [$from, $to, $groupFormat] = $this->resolveRangeAndGroup($request);

        $rows = DB::table('zamowienia')
            ->selectRaw("DATE_FORMAT(data_wystawienia, '{$groupFormat}') as label")
            ->selectRaw('SUM(suma_brutto) as value')
            ->whereBetween('data_wystawienia', [$from, $to])
            ->where('status', '!=', 'anulowane')
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('value')->map(fn($v)=> round((float)$v,2)),
        ]);
    }

    /** JSON: Liczba zamówień w czasie */
    public function zamowieniaData(Request $request)
    {
        [$from, $to, $groupFormat] = $this->resolveRangeAndGroup($request);

        $rows = DB::table('zamowienia')
            ->selectRaw("DATE_FORMAT(data_wystawienia, '{$groupFormat}') as label")
            ->selectRaw('COUNT(*) as value')
            ->whereBetween('data_wystawienia', [$from, $to])
            ->groupBy('label')
            ->orderBy('label')
            ->get();

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('value')->map(fn($v)=> (int)$v),
        ]);
    }

    /**
     * JSON: TOP produkty – wg ilości (qty) lub wartości (value) w zadanym okresie.
     * Korzysta z tablic: zamowienia (nagłówek), zamowienia_pozycje (pozycje).
     */
    public function topProduktyData(Request $request)
    {
        $rankBy = $request->query('rankBy', 'qty'); // qty|value
        $limit  = (int)$request->query('limit', 10);
        [$from, $to] = $this->resolveRangeOnly($request);

        $selectAgg = $rankBy === 'value'
            ? 'SUM(zp.wart_brutto) as metric'
            : 'SUM(zp.ilosc) as metric';

        $rows = DB::table('zamowienia_pozycje as zp')
            ->join('zamowienia as z', 'z.id_zamowienia', '=', 'zp.id_zamowienia')
            ->selectRaw('zp.nazwa as label, '.$selectAgg)
            ->whereBetween('z.data_wystawienia', [$from, $to])
            ->groupBy('zp.nazwa')
            ->orderByDesc('metric')
            ->limit($limit)
            ->get();

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('metric')->map(fn($v)=> round((float)$v,2)),
        ]);
    }

    /** Pomocnik: zakres dat + format grupowania dla MySQL DATE_FORMAT */
    private function resolveRangeAndGroup(Request $request): array
    {
        $period = $request->query('period', 'month'); // day|week|month|quarter|year|range

        // zakres
        if ($period === 'range') {
            [$from, $to] = $this->resolveRangeOnly($request);
        } else {
            [$from, $to] = $this->defaultRangeForPeriod($period);
        }

        // format grupowania (MySQL)
        $format = match ($period) {
            'day'     => '%Y-%m-%d',
            'week'    => '%x-W%v',
            'month'   => '%Y-%m',
            'quarter' => 'Q%q %Y',   // uwaga: %q jest dostępne w MySQL 8.0.28+; alternatywa poniżej
            'year'    => '%Y',
            'range'   => '%Y-%m-%d', // w zakresie domyślnie dziennie
            default   => '%Y-%m',
        };

        // fallback dla kwartalnego – jeśli Twoja wersja MySQL nie wspiera %q:
        if ($period === 'quarter' && ! $this->mysqlSupportsQuarterInDateFormat()) {
            // ersatz: CONCAT('Q', QUARTER(data_wystawienia), ' ', YEAR(data_wystawienia))
            $format = '%Y-%m'; // użyj miesiąca albo niżej zróbmy raw:
            // Możesz też przerobić select na:
            // ->selectRaw("CONCAT('Q', QUARTER(data_wystawienia),' ',YEAR(data_wystawienia)) as label")
            // i wtedy pominąć DATE_FORMAT
        }

        return [$from, $to, $format];
    }

    /** Tylko zakres (from/to) – gdy period=range */
    private function resolveRangeOnly(Request $request): array
    {
        $from = $request->query('from', now()->firstOfMonth()->format('Y-m-d'));
        $to   = $request->query('to',   now()->format('Y-m-d'));
        return [$from.' 00:00:00', $to.' 23:59:59'];
    }

    /** Domyślny zakres dla predefiniowanych okresów */
    private function defaultRangeForPeriod(string $period): array
    {
        $today = now();
        return match ($period) {
            'day'     => [$today->copy()->startOfDay()->format('Y-m-d H:i:s'),
                $today->copy()->endOfDay()->format('Y-m-d H:i:s')],
            'week'    => [$today->copy()->startOfWeek()->format('Y-m-d H:i:s'),
                $today->copy()->endOfWeek()->format('Y-m-d H:i:s')],
            'month'   => [$today->copy()->firstOfMonth()->format('Y-m-d H:i:s'),
                $today->copy()->endOfMonth()->format('Y-m-d H:i:s')],
            'quarter' => [$today->copy()->firstOfQuarter()->format('Y-m-d H:i:s'),
                $today->copy()->lastOfQuarter()->format('Y-m-d H:i:s')],
            'year'    => [$today->copy()->firstOfYear()->format('Y-m-d H:i:s'),
                $today->copy()->endOfYear()->format('Y-m-d H:i:s')],
            default   => [$today->copy()->firstOfMonth()->format('Y-m-d H:i:s'),
                $today->copy()->endOfMonth()->format('Y-m-d H:i:s')],
        };
    }

    private function mysqlSupportsQuarterInDateFormat(): bool
    {
        return false;
    }
}
