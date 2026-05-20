<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClientContact;
use App\Models\Customer;
use App\Services\ClientContactService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ClientContactController extends Controller
{
    public function __construct(
        protected ClientContactService $contactService
    ) {}

    /**
     * List contacts for a customer
     */
    public function index(Customer $customer): JsonResponse
    {
        $contacts = $this->contactService->getContacts($customer);

        return response()->json(['data' => $contacts]);
    }

    /**
     * Get a single contact
     */
    public function show(Customer $customer, ClientContact $contact): JsonResponse
    {
        if ($contact->customer_id !== $customer->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        return response()->json(['data' => $contact]);
    }

    /**
     * Create a new contact for a customer
     */
    public function store(Request $request, Customer $customer): JsonResponse
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'email' => 'required|email|max:255',
            'phone' => 'nullable|string|max:30',
            'title' => 'nullable|string|max:100',
            'is_primary' => 'boolean',
            'can_view_invoices' => 'boolean',
            'can_make_payments' => 'boolean',
            'can_manage_services' => 'boolean',
        ]);

        $contact = $this->contactService->createContact($customer, $validated);

        return response()->json(['data' => $contact], Response::HTTP_CREATED);
    }

    /**
     * Update a contact
     */
    public function update(Request $request, Customer $customer, ClientContact $contact): JsonResponse
    {
        if ($contact->customer_id !== $customer->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:100',
            'last_name' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:255',
            'phone' => 'nullable|string|max:30',
            'title' => 'nullable|string|max:100',
            'is_primary' => 'boolean',
            'can_view_invoices' => 'boolean',
            'can_make_payments' => 'boolean',
            'can_manage_services' => 'boolean',
        ]);

        $contact = $this->contactService->updateContact($contact, $validated);

        return response()->json(['data' => $contact]);
    }

    /**
     * Delete a contact
     */
    public function destroy(Customer $customer, ClientContact $contact): Response
    {
        if ($contact->customer_id !== $customer->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        try {
            $this->contactService->deleteContact($contact);
        } catch (\RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        return response()->noContent();
    }

    /**
     * Make a contact the primary contact
     */
    public function makePrimary(Customer $customer, ClientContact $contact): JsonResponse
    {
        if ($contact->customer_id !== $customer->id) {
            abort(Response::HTTP_NOT_FOUND);
        }

        $contact = $this->contactService->makePrimary($contact);

        return response()->json(['data' => $contact, 'message' => 'Contact set as primary.']);
    }
}
