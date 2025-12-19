<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Mail\NewContactMessageMail;
use App\Models\ContactMessage;
use App\Models\MasterCategory;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Admin - get message statistics for dashboard charts
     * 
     * @queryParam period string Filter by period: daily, weekly, monthly. Default: daily
     * @queryParam start_date date Start date (YYYY-MM-DD). Default: 30 days ago
     * @queryParam end_date date End date (YYYY-MM-DD). Default: today
     */
    public function stats(Request $request)
    {
        $request->validate([
            'period' => 'in:daily,weekly,monthly',
            'start_date' => 'date|date_format:Y-m-d',
            'end_date' => 'date|date_format:Y-m-d|after_or_equal:start_date',
        ]);

        $period = $request->get('period', 'daily');
        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfDay();
        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->startOfDay()
            : $endDate->copy()->subDays(29)->startOfDay();

        // Generate date labels based on period
        $labels = $this->generateLabels($startDate, $endDate, $period);

        // Get all categories for grouping
        $categories = MasterCategory::pluck('name', 'id')->toArray();

        // Build the base query
        $dateFormat = $this->getDateFormat($period);

        $rawData = ContactMessage::query()
            ->select([
                DB::raw("{$dateFormat} as period_label"),
                DB::raw('COUNT(*) as total'),
                'product_id',
            ])
            ->leftJoin('products', 'contact_messages.product_id', '=', 'products.id')
            ->whereBetween('contact_messages.created_at', [$startDate, $endDate])
            ->groupBy('period_label', 'product_id', 'products.master_category_id')
            ->addSelect('products.master_category_id')
            ->get();

        // Initialize datasets
        $datasets = [
            'total' => array_fill_keys($labels, 0),
            'by_category' => [],
        ];

        // Add "No Product" category and all master categories
        $datasets['by_category']['No Product'] = array_fill_keys($labels, 0);
        foreach ($categories as $categoryName) {
            $datasets['by_category'][$categoryName] = array_fill_keys($labels, 0);
        }

        // Populate datasets from raw data
        foreach ($rawData as $row) {
            $label = $row->period_label;
            if (!in_array($label, $labels)) {
                continue;
            }

            $datasets['total'][$label] += $row->total;

            if ($row->product_id === null) {
                $datasets['by_category']['No Product'][$label] += $row->total;
            } elseif ($row->master_category_id && isset($categories[$row->master_category_id])) {
                $categoryName = $categories[$row->master_category_id];
                $datasets['by_category'][$categoryName][$label] += $row->total;
            } else {
                $datasets['by_category']['No Product'][$label] += $row->total;
            }
        }

        // Convert associative arrays to indexed arrays (preserve order)
        $datasets['total'] = array_values($datasets['total']);
        foreach ($datasets['by_category'] as $cat => $values) {
            $datasets['by_category'][$cat] = array_values($values);
        }

        // Remove empty categories (all zeros)
        $datasets['by_category'] = array_filter($datasets['by_category'], function ($values) {
            return array_sum($values) > 0;
        });

        // Get summary statistics
        $summary = [
            'total_messages' => ContactMessage::whereBetween('created_at', [$startDate, $endDate])->count(),
            'by_status' => ContactMessage::query()
                ->whereBetween('created_at', [$startDate, $endDate])
                ->select('status', DB::raw('COUNT(*) as count'))
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray(),
        ];

        return ApiResponse::success([
            'labels' => $labels,
            'datasets' => $datasets,
            'summary' => $summary,
            'period' => $period,
            'date_range' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d'),
            ],
        ]);
    }

    /**
     * Generate date labels based on period type
     */
    private function generateLabels(Carbon $start, Carbon $end, string $period): array
    {
        $labels = [];

        switch ($period) {
            case 'weekly':
                $current = $start->copy()->startOfWeek();
                while ($current <= $end) {
                    $labels[] = $current->format('Y-W');
                    $current->addWeek();
                }
                break;

            case 'monthly':
                $current = $start->copy()->startOfMonth();
                while ($current <= $end) {
                    $labels[] = $current->format('Y-m');
                    $current->addMonth();
                }
                break;

            default: // daily
                $datePeriod = CarbonPeriod::create($start, $end);
                foreach ($datePeriod as $date) {
                    $labels[] = $date->format('Y-m-d');
                }
        }

        return $labels;
    }

    /**
     * Get SQL date format expression based on period
     */
    private function getDateFormat(string $period): string
    {
        $driver = DB::getDriverName();

        if ($driver === 'pgsql') {
            return match ($period) {
                'weekly' => "TO_CHAR(contact_messages.created_at, 'IYYY-IW')",
                'monthly' => "TO_CHAR(contact_messages.created_at, 'YYYY-MM')",
                default => "TO_CHAR(contact_messages.created_at, 'YYYY-MM-DD')",
            };
        }

        // MySQL / SQLite
        return match ($period) {
            'weekly' => "DATE_FORMAT(contact_messages.created_at, '%Y-%u')",
            'monthly' => "DATE_FORMAT(contact_messages.created_at, '%Y-%m')",
            default => "DATE(contact_messages.created_at)",
        };
    }

    /**
     * Public endpoint - submit contact form
     * Rate limited via route middleware
     */
    public function store(StoreContactRequest $request)
    {
        $contact = ContactMessage::create($request->validated());

        // Load product if product_id was provided (needed for email template)
        if ($contact->product_id) {
            $contact->load('product:id,name');
        }

        // Send email notification immediately (no queue)
        $notificationEmail = config('contact.notification_email');
        if ($notificationEmail) {
            Mail::to($notificationEmail)->send(new NewContactMessageMail($contact));
        }

        return ApiResponse::success(
            [
                'id' => $contact->id,
                'product_name' => $contact->product?->name,
                'created_at' => $contact->created_at,
            ],
            'Message sent successfully',
            201
        );
    }

    /**
     * Admin - list all contact messages with filtering
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);

        $query = ContactMessage::query()
            ->with('product:id,name')
            ->orderBy('created_at', 'desc');

        // Search by name or email
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('filter.status')) {
            $query->where('status', $request->input('filter.status'));
        }

        // Date range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        return ApiResponse::success($query->paginate($perPage));
    }

    /**
     * Admin - show single contact message
     */
    public function show($id)
    {
        $contact = ContactMessage::with('product:id,name')->findOrFail($id);

        // Auto-mark as read
        if ($contact->status === ContactMessage::STATUS_NEW) {
            $contact->update(['status' => ContactMessage::STATUS_READ]);
        }

        return ApiResponse::success($contact);
    }

    /**
     * Admin - update contact message status
     */
    public function update(Request $request, $id)
    {
        $contact = ContactMessage::findOrFail($id);

        $data = $request->validate([
            'status' => 'required|in:new,read,replied',
        ]);

        $contact->update($data);

        return ApiResponse::success($contact, 'Contact message updated');
    }

    /**
     * Admin - delete contact message
     */
    public function destroy($id)
    {
        $contact = ContactMessage::findOrFail($id);
        $contact->delete();

        return ApiResponse::success(null, 'Contact message deleted');
    }
}
