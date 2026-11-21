<?php

namespace App\Http\Controllers;

use App\Enums\InquiryStatus;
use App\Enums\ListingStatus;
use App\Models\AuditLog;
use App\Models\Inquiry;
use App\Models\Listing;
use App\Models\Professional;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    public function store(Request $request, Listing $listing): RedirectResponse
    {
        $this->ensurePublished($listing);

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
        } elseif ($user->isCompany()) {
            $query = Inquiry::query()->whereHas('listing', fn ($q) => $q->where('company_id', $user->company_id));
        } elseif ($user->isProfessional()) {
            $professional = Professional::where('user_id', $user->id)->first();
            $query = $professional
                ? Inquiry::query()->where('professional_id', $professional->id)
                : Inquiry::query()->whereRaw('1 = 0');
        } else {
            $query = $user->isCustomer()
                ? Inquiry::query()->where('user_id', $user->id)
                : abort(403);
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
        if (! $inquiry->viewableBy($request->user())) {
            abort(403);
        }

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
        if (! $inquiry->updatableBy($request->user())) {
            abort(403);
        }

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
        $allowed = [
            InquiryStatus::New->value => [InquiryStatus::Contacted->value, InquiryStatus::Closed->value],
            InquiryStatus::Contacted->value => [InquiryStatus::Closed->value],
            InquiryStatus::Closed->value => [InquiryStatus::Contacted->value],
        ];

        return in_array($to, $allowed[$from] ?? [], true);
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
