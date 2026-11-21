<?php

namespace App\Http\Controllers;

use App\Enums\InquiryStatus;
use App\Enums\ListingStatus;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Services\InquiryNotifier;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Throwable;

class InquiryController extends Controller
{
    /**
     * Public consultation / inspection request form for a listing.
     */
    public function create(Listing $listing): View
    {
        $this->ensurePublished($listing);

        return view('inquiries.create', ['listing' => $listing]);
    }

    /**
     * Store a consultation / inspection request for a listing.
     */
    public function store(Request $request, Listing $listing, InquiryNotifier $notifier): RedirectResponse
    {
        $this->ensurePublished($listing);

        if ($request->filled('website')) {
            return redirect()
                ->route('listings.show', $listing->slug)
                ->with('status', __('Thank you! Your inquiry has been sent.'));
        }

        $validated = $request->validate([
            'type' => ['required', 'string', Rule::in(['consultation', 'inspection'])],
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'message' => ['required', 'string'],
            'preferred_date' => ['nullable', 'date'],
        ]);

        $inquiry = new Inquiry($validated);
        $inquiry->listing_id = $listing->id;
        $inquiry->user_id = $request->user()?->id;
        $inquiry->save();

        try {
            $notifier->notify($inquiry);
        } catch (Throwable $exception) {
            Log::warning('Inquiry notification failed.', [
                'inquiry_id' => $inquiry->id,
                'error' => $exception->getMessage(),
            ]);
        }

        return redirect()
            ->route('listings.show', $listing->slug)
            ->with('status', __('Thank you! Your inquiry has been sent.'));
    }

    /**
     * List inquiries scoped to the authenticated user's role.
     */
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->isSuperAdmin()) {
            $query = Inquiry::query();
        } elseif ($user->isCompany() || $user->isProfessional() || $user->isCustomer()) {
            // List scoping lives in Inquiry::viewableBy()/whereViewableBy();
            // authorization for a single inquiry goes through InquiryPolicy.
            $query = Inquiry::query()->whereViewableBy($user);
        } else {
            abort(403);
        }

        $query->with(['listing', 'professional']);

        if ($request->filled('status') && InquiryStatus::tryFrom($request->input('status'))) {
            $query->where('status', $request->input('status'));
        }

        $inquiries = $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('inquiries.index', [
            'inquiries' => $inquiries,
            'statuses' => InquiryStatus::cases(),
            'currentStatus' => $request->input('status'),
        ]);
    }

    /**
     * Show a single inquiry detail.
     */
    public function show(Inquiry $inquiry, Request $request): View
    {
        $this->authorize('view', $inquiry);

        $inquiry->load(['listing', 'professional', 'user']);

        $isRecipient = $this->isRecipient($inquiry);

        $whatsappLink = null;
        if ($isRecipient && $inquiry->phone) {
            $phone = preg_replace('/\D+/', '', $inquiry->phone);
            if ($phone !== '') {
                $message = rawurlencode(
                    'Hello '.$inquiry->name.'! Regarding your inquiry about '.
                    ($inquiry->listing?->title ?? 'our listing').'. '
                );
                $whatsappLink = "https://wa.me/{$phone}?text={$message}";
            }
        }

        return view('inquiries.show', [
            'inquiry' => $inquiry,
            'isRecipient' => $isRecipient,
            'whatsappLink' => $whatsappLink,
        ]);
    }

    /**
     * Update the inquiry status.
     */
    public function updateStatus(Request $request, Inquiry $inquiry): RedirectResponse
    {
        $this->authorize('update', $inquiry);

        $newStatus = $request->input('status');

        // Validate status is a valid enum value
        $allowedValues = array_column(InquiryStatus::cases(), 'value');
        ! in_array($newStatus, $allowedValues) && abort(422, __('The selected status is invalid.'));

        $oldStatus = $inquiry->status->value;

        if (! $this->isAllowedTransition($oldStatus, $newStatus)) {
            return back()
                ->withErrors(['status' => __('Invalid status transition.')])
                ->withInput();
        }

        $inquiry->status = $newStatus;
        $inquiry->save();

        $this->logStatusChange($inquiry, $oldStatus, $newStatus);

        return redirect()->route('inquiries.show', $inquiry)
            ->with('status', __('Inquiry status updated.'));
    }

    /**
     * Draft, archived and other non-public listings are not inquiry targets.
     */
    private function ensurePublished(Listing $listing): void
    {
        if ($listing->status !== ListingStatus::Published) {
            abort(404);
        }
    }

    /**
     * Check if the current user is a recipient (company, professional, or super_admin) of this inquiry.
     */
    private function isRecipient(Inquiry $inquiry): bool
    {
        $user = request()->user();

        if ($user->isSuperAdmin()) {
            return true;
        }

        if ($inquiry->listing_id !== null) {
            $listing = $inquiry->listing;
            if ($listing?->company_id !== null && $user->company_id === $listing->company_id) {
                return true;
            }
        }

        if ($inquiry->professional_id !== null) {
            $professional = $inquiry->professional;
            if ($professional?->user_id === $user->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Validate allowed status transitions.
     */
    private function isAllowedTransition(string $from, string $to): bool
    {
        $status = InquiryStatus::tryFrom($from);

        if ($status === null) {
            return false;
        }

        $allowed = array_map(
            fn (InquiryStatus $transition): string => $transition->value,
            $status->allowedTransitions(),
        );

        return in_array($to, $allowed, true);
    }

    /**
     * Write an audit log entry for the status change.
     */
    private function logStatusChange(Inquiry $inquiry, string $oldStatus, string $newStatus): void
    {
        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'inquiry.status.updated',
            'auditable_type' => Inquiry::class,
            'auditable_id' => $inquiry->id,
            'old_values' => ['status' => $oldStatus],
            'new_values' => ['status' => $newStatus],
            'ip_address' => request()->ip(),
        ]);
    }
}
