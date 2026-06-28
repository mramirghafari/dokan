<?php

namespace App\Http\Controllers\Api;

use App\Services\ActivityLogService;

use App\Http\Controllers\Controller;
use App\Models\CrmLead;
use App\Models\CrmOpportunity;
use App\Models\CrmServiceTicket;
use App\Services\CrmPublicApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CrmPublicApiController extends Controller
{
    public function meta(string $clientCode, Request $request, CrmPublicApiService $service): JsonResponse
    {
        $client = $service->resolveClient($clientCode, $request, 'leads.write');

        return response()->json([
            'data' => [
                'client' => $client->code,
                'scopes' => $client->scopes,
                'lead_sources' => CrmLead::SOURCES,
                'lead_priorities' => CrmLead::PRIORITIES,
                'ticket_types' => CrmServiceTicket::TYPES,
                'ticket_priorities' => CrmServiceTicket::PRIORITIES,
                'opportunity_stages' => CrmOpportunity::STAGES,
            ],
        ]);
    }

    public function storeLead(string $clientCode, Request $request, CrmPublicApiService $service): JsonResponse
    {
        $client = $service->resolveClient($clientCode, $request, 'leads.write');
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:160'],
            'name' => ['required', 'string', 'max:180'],
            'company_name' => ['nullable', 'string', 'max:180'],
            'mobile' => ['nullable', 'string', 'max:30'],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:160'],
            'city' => ['nullable', 'string', 'max:120'],
            'source' => ['nullable', 'in:' . implode(',', array_keys(CrmLead::SOURCES))],
            'campaign' => ['nullable', 'string', 'max:160'],
            'score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'priority' => ['nullable', 'in:' . implode(',', array_keys(CrmLead::PRIORITIES))],
            'owner_user_id' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);
        $lead = $service->storeLead($client, $data, $request);

        return response()->json(['data' => ['id' => $lead->id, 'code' => $lead->code, 'status' => $lead->status, 'duplicate_status' => $lead->duplicate_status]], 201);
    }

    public function storeTicket(string $clientCode, Request $request, CrmPublicApiService $service): JsonResponse
    {
        $client = $service->resolveClient($clientCode, $request, 'tickets.write');
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:160'],
            'customer_id' => ['nullable', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'type' => ['nullable', 'in:' . implode(',', array_keys(CrmServiceTicket::TYPES))],
            'channel' => ['nullable', 'in:' . implode(',', array_keys(CrmServiceTicket::CHANNELS))],
            'priority' => ['nullable', 'in:' . implode(',', array_keys(CrmServiceTicket::PRIORITIES))],
            'subject' => ['required', 'string', 'max:180'],
            'contact_name' => ['nullable', 'string', 'max:180'],
            'contact_phone' => ['nullable', 'string', 'max:40'],
            'due_at' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);
        $ticket = $service->storeTicket($client, $data, $request);

        return response()->json(['data' => ['id' => $ticket->id, 'code' => $ticket->code, 'status' => $ticket->status]], 201);
    }

    public function storeOpportunity(string $clientCode, Request $request, CrmPublicApiService $service): JsonResponse
    {
        $client = $service->resolveClient($clientCode, $request, 'opportunities.write');
        $data = $request->validate([
            'external_id' => ['nullable', 'string', 'max:160'],
            'customer_id' => ['required', 'integer'],
            'assigned_user_id' => ['nullable', 'integer'],
            'title' => ['required', 'string', 'max:180'],
            'stage' => ['nullable', 'in:' . implode(',', array_keys(CrmOpportunity::STAGES))],
            'priority' => ['nullable', 'in:' . implode(',', array_keys(CrmOpportunity::PRIORITIES))],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'probability_percent' => ['nullable', 'integer', 'min:0', 'max:100'],
            'expected_close_date_en' => ['nullable', 'date'],
            'next_action_date_en' => ['nullable', 'date'],
            'description' => ['nullable', 'string'],
        ]);
        $opportunity = $service->storeOpportunity($client, $data, $request);

        return response()->json(['data' => ['id' => $opportunity->id, 'code' => $opportunity->code, 'status' => $opportunity->status]], 201);
    }
}
