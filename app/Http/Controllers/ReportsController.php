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
        // lista kategorii do selecta
        $kategorie = DB::table('kategorie')
            ->select('id_kategorii', 'nazwa')
            ->orderBy('nazwa')
            ->get();

        return view('raporty.index', [
            'defaultPeriod'   => $request->query('period', 'year'), // było 'month'
            'defaultFrom'     => $request->query('from', now()->firstOfYear()->format('Y-m-d')),
            'defaultTo'       => $request->query('to',   now()->format('Y-m-d')),
            'defaultRankBy'   => $request->query('rankBy', 'qty'),
            'defaultLimit'    => (int)$request->query('limit', 10),
            'defaultCategory' => (int)$request->query('category', 0),
            'kategorie'       => $kategorie,
        ]);
    }


    /** JSON: Obrót (suma_brutto) w czasie – dla pierwszego wykresu "Obrót" */
    public function obrotData(Request $request)
    {
        [$from, $to, $groupFormat] = $this->resolveRangeAndGroup($request);
        $category = (int)$request->query('category', 0);

        // jeśli wybrano kategorię – liczymy obrót tylko z pozycji tej kategorii
        if ($category > 0) {
            $rows = DB::table('zamowienia as z')
                ->join('zamowienia_pozycje as zp', 'zp.id_zamowienia', '=', 'z.id_zamowienia')
                ->join('produkty as p', 'p.id_produktu', '=', 'zp.id_produktu')
                ->selectRaw("DATE_FORMAT(z.data_wystawienia, '{$groupFormat}') as label")
                ->selectRaw('SUM(zp.wart_brutto) as value')
                ->whereBetween('z.data_wystawienia', [$from, $to])
                ->where('z.status', '!=', 'anulowane')
                ->where('p.id_kategorii', $category)
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        } else {
            // wszystkie kategorie – jak wcześniej
            $rows = DB::table('zamowienia')
                ->selectRaw("DATE_FORMAT(data_wystawienia, '{$groupFormat}') as label")
                ->selectRaw('SUM(suma_brutto) as value')
                ->whereBetween('data_wystawienia', [$from, $to])
                ->where('status', '!=', 'anulowane')
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('value')->map(fn($v) => round((float)$v, 2)),
        ]);
    }


    /** JSON: Liczba zamówień w czasie */
    public function zamowieniaData(Request $request)
    {
        [$from, $to, $groupFormat] = $this->resolveRangeAndGroup($request);
        $category = (int)$request->query('category', 0);

        if ($category > 0) {
            // liczymy zamówienia, które zawierają produkty z danej kategorii
            $rows = DB::table('zamowienia as z')
                ->join('zamowienia_pozycje as zp', 'zp.id_zamowienia', '=', 'z.id_zamowienia')
                ->join('produkty as p', 'p.id_produktu', '=', 'zp.id_produktu')
                ->selectRaw("DATE_FORMAT(z.data_wystawienia, '{$groupFormat}') as label")
                ->selectRaw('COUNT(DISTINCT z.id_zamowienia) as value')
                ->whereBetween('z.data_wystawienia', [$from, $to])
                ->where('z.status', '!=', 'anulowane')
                ->where('p.id_kategorii', $category)
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        } else {
            $rows = DB::table('zamowienia')
                ->selectRaw("DATE_FORMAT(data_wystawienia, '{$groupFormat}') as label")
                ->selectRaw('COUNT(*) as value')
                ->whereBetween('data_wystawienia', [$from, $to])
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('value')->map(fn($v) => (int)$v),
        ]);
    }


    /**
     * JSON: TOP produkty – wg ilości (qty) lub wartości (value) w zadanym okresie.
     * Korzysta z tablic: zamowienia (nagłówek), zamowienia_pozycje (pozycje).
     */
    public function topProduktyData(Request $request)
    {
        $rankBy   = $request->query('rankBy', 'qty'); // qty|value
        $limit    = (int)$request->query('limit', 10);
        $category = (int)$request->query('category', 0);
        [$from, $to] = $this->resolveRangeOnly($request);

        $selectAgg = $rankBy === 'value'
            ? 'SUM(zp.wart_brutto) as metric'
            : 'SUM(zp.ilosc) as metric';

        $rows = DB::table('zamowienia_pozycje as zp')
            ->join('zamowienia as z', 'z.id_zamowienia', '=', 'zp.id_zamowienia')
            ->join('produkty as p', 'p.id_produktu', '=', 'zp.id_produktu')
            ->selectRaw('zp.nazwa as label, ' . $selectAgg)
            ->whereBetween('z.data_wystawienia', [$from, $to])
            ->when($category > 0, function ($q) use ($category) {
                $q->where('p.id_kategorii', $category);
            })
            ->groupBy('zp.nazwa')
            ->orderByDesc('metric')
            ->limit($limit)
            ->get();

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('metric')->map(fn($v) => round((float)$v, 2)),
        ]);
    }


    /** JSON: Dochód (suma_brutto) w czasie – dla wykresu "Dochód wg okresu" */
    public function dochodyData(Request $request)
    {
        [$from, $to, $groupFormat] = $this->resolveRangeAndGroup($request);
        $category = (int)$request->query('category', 0);

        if ($category > 0) {
            $rows = DB::table('zamowienia as z')
                ->join('zamowienia_pozycje as zp', 'zp.id_zamowienia', '=', 'z.id_zamowienia')
                ->join('produkty as p', 'p.id_produktu', '=', 'zp.id_produktu')
                ->selectRaw("DATE_FORMAT(z.data_wystawienia, '{$groupFormat}') as label")
                ->selectRaw('SUM(zp.wart_brutto) as value')
                ->whereBetween('z.data_wystawienia', [$from, $to])
                ->where('z.status', '!=', 'anulowane')
                ->where('p.id_kategorii', $category)
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        } else {
            $rows = DB::table('zamowienia')
                ->selectRaw("DATE_FORMAT(data_wystawienia, '{$groupFormat}') as label")
                ->selectRaw('SUM(suma_brutto) as value')
                ->whereBetween('data_wystawienia', [$from, $to])
                ->where('status', '!=', 'anulowane')
                ->groupBy('label')
                ->orderBy('label')
                ->get();
        }

        return response()->json([
            'labels' => $rows->pluck('label'),
            'values' => $rows->pluck('value')->map(fn($v) => round((float)$v, 2)),
        ]);
    }


    /** Pomocnik: zakres dat + format grupowania dla MySQL DATE_FORMAT */
    /** Pomocnik: zakres dat + format grupowania dla MySQL DATE_FORMAT */
    private function resolveRangeAndGroup(Request $request): array
    {
        // period przychodzi z frontu: 'year' / 'month' / 'week' / 'day' / 'range' / 'quarter'
        $period = $request->query('period', 'year');

        // 1) Zakres dat – jak dotąd
        if ($period === 'range') {
            [$from, $to] = $this->resolveRangeOnly($request);
        } else {
            [$from, $to] = $this->defaultRangeForPeriod($period);
        }

        /**
         * 2) Poziom grupowania na osi X:
         *    - year   -> month   (12 miesięcy)
         *    - month  -> week    (tygodnie w miesiącu)
         *    - week   -> day     (dni w tygodniu)
         *    - reszta bez zmian
         */
        $groupLevel = match ($period) {
            'year'  => 'month',
            'month' => 'week',
            'week'  => 'day',
            default => $period,
        };

        // 3) Format DATE_FORMAT dla MySQL dla danego poziomu
        $format = match ($groupLevel) {
            'day'     => '%Y-%m-%d',   // dni
            'week'    => '%x-W%v',     // tygodnie ISO
            'month'   => '%Y-%m',      // miesiące
            'quarter' => 'Q%q %Y',
            'year'    => '%Y',
            'range'   => '%Y-%m-%d',
            default   => '%Y-%m',
        };

        if ($groupLevel === 'quarter' && ! $this->mysqlSupportsQuarterInDateFormat()) {
            $format = '%Y-%m';
        }

        return [$from, $to, $format];
    }


    /** Tylko zakres (from/to) – gdy period=range */
    private function resolveRangeOnly(Request $request): array
    {
        $from = $request->query('from', now()->firstOfMonth()->format('Y-m-d'));
        $to   = $request->query('to',   now()->format('Y-m-d'));
        return [$from . ' 00:00:00', $to . ' 23:59:59'];
    }

    /** Domyślny zakres dla predefiniowanych okresów */
    private function defaultRangeForPeriod(string $period): array
    {
        $today = now();
        return match ($period) {
            'day'     => [
                $today->copy()->startOfDay()->format('Y-m-d H:i:s'),
                $today->copy()->endOfDay()->format('Y-m-d H:i:s'),
            ],
            'week'    => [
                $today->copy()->startOfWeek()->format('Y-m-d H:i:s'),
                $today->copy()->endOfWeek()->format('Y-m-d H:i:s'),
            ],
            'month'   => [
                $today->copy()->firstOfMonth()->format('Y-m-d H:i:s'),
                $today->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ],
            'quarter' => [
                $today->copy()->firstOfQuarter()->format('Y-m-d H:i:s'),
                $today->copy()->lastOfQuarter()->format('Y-m-d H:i:s'),
            ],
            'year'    => [
                $today->copy()->firstOfYear()->format('Y-m-d H:i:s'),
                $today->copy()->endOfYear()->format('Y-m-d H:i:s'),
            ],
            default   => [
                $today->copy()->firstOfMonth()->format('Y-m-d H:i:s'),
                $today->copy()->endOfMonth()->format('Y-m-d H:i:s'),
            ],
        };
    }

    private function mysqlSupportsQuarterInDateFormat(): bool
    {
        // jeśli kiedyś zaktualizujesz MySQL i będzie wspierał %q, zmień na true
        return false;
    }
}
