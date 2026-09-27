<?php

namespace App\Http\Controllers;

use App\Enums\PaymentIssueStatus;
use App\Enums\PaymentIssueType;
use App\Http\Requests\StorePaymentIssueRequest;
use App\Models\Serviceman;
use App\Models\ServicemanPaymentIssue;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\Rule;

class ServicemanPaymentIssueController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentIssueRequest $request, Serviceman $serviceman): RedirectResponse
    {
        $data = $request->validated();

        $serviceman->paymentIssues()->create([
            'type' => $data['type'],
            'description' => $data['description'],
            'status' => PaymentIssueStatus::InProgress,
        ]);

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Проблему з виплатами додано.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Serviceman $serviceman, ServicemanPaymentIssue $paymentIssue): RedirectResponse
    {
        Gate::authorize('update', $serviceman);
        abort_unless($paymentIssue->serviceman_id === $serviceman->id, 404);

        $data = $request->validate([
            'type' => ['sometimes', Rule::enum(PaymentIssueType::class)],
            'description' => ['sometimes', 'string'],
            'status' => ['sometimes', Rule::enum(PaymentIssueStatus::class)],
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $paymentIssue->update(collect($data)->only(['type', 'description', 'status'])->all());

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Проблему з виплатами оновлено.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Serviceman $serviceman, ServicemanPaymentIssue $paymentIssue): RedirectResponse
    {
        Gate::authorize('update', $serviceman);
        abort_unless($paymentIssue->serviceman_id === $serviceman->id, 404);

        $data = $request->validate([
            'return_to' => ['nullable', 'in:show,edit'],
        ]);

        $paymentIssue->delete();

        return Redirect::route($this->returnRoute($data), $serviceman)->with('status', 'Проблему з виплатами видалено.');
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function returnRoute(array $data): string
    {
        return ($data['return_to'] ?? null) === 'edit' ? 'servicemen.edit' : 'servicemen.show';
    }
}
