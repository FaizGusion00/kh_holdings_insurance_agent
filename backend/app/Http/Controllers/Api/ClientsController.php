<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Client;
use App\Models\GatewayPayment;

class ClientsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) ($request->query('per_page', 15));
        $clients = Client::where('agent_id', auth()->id())
            ->orderByDesc('created_at')
            ->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $clients,
            'message' => 'Clients retrieved successfully'
        ]);
    }

    public function show($id)
    {
        $client = Client::where('agent_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $client,
            'message' => 'Client retrieved successfully'
        ]);
    }

    public function payments($id)
    {
        $client = Client::where('agent_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        // Include both per-client rows and aggregate registration row as fallback
        $payments = GatewayPayment::where(function ($q) use ($client) {
                $q->where('client_id', $client->id)
                  ->orWhere(function ($q2) use ($client) {
                      $q2->whereNull('client_id')
                         ->where('registration_id', $client->registration_id);
                  });
            })
            ->orderByDesc('created_at')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $payments,
            'message' => 'Client payments retrieved successfully'
        ]);
    }

    public function downloadCard($id)
    {
        $client = Client::where('agent_id', auth()->id())
            ->where('id', $id)
            ->firstOrFail();

        $svg = $this->generateCardSvg($client);
        $filename = 'medical-card-' . preg_replace('/[^A-Za-z0-9]/', '', $client->full_name) . '.svg';

        return response($svg, 200, [
            'Content-Type' => 'image/svg+xml',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"'
        ]);
    }

    private function generateCardSvg(Client $client): string
    {
        $name = htmlspecialchars($client->full_name, ENT_QUOTES);
        $plan = htmlspecialchars($client->plan_name, ENT_QUOTES);
        $agent = (string) auth()->user()?->agent_code ?: '';
        $nric = htmlspecialchars($client->nric, ENT_QUOTES);
        $today = now()->toDateString();
        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="860" height="540">
  <defs>
    <linearGradient id="grad" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" style="stop-color:#10b981;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#059669;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="860" height="540" rx="24" fill="url(#grad)" />
  <rect x="24" y="24" width="812" height="492" rx="18" fill="#ffffff" opacity="0.95" />
  <text x="48" y="90" font-family="Arial, Helvetica, sans-serif" font-size="28" fill="#065f46">MediPlan Coop - Medical Card</text>
  <text x="48" y="150" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#064e3b">Name:</text>
  <text x="200" y="150" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#111827">$name</text>
  <text x="48" y="190" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#064e3b">NRIC:</text>
  <text x="200" y="190" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#111827">$nric</text>
  <text x="48" y="230" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#064e3b">Plan:</text>
  <text x="200" y="230" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#111827">$plan</text>
  <text x="48" y="270" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#064e3b">Agent Code:</text>
  <text x="200" y="270" font-family="Arial, Helvetica, sans-serif" font-size="22" fill="#111827">$agent</text>
  <text x="48" y="310" font-family="Arial, Helvetica, sans-serif" font-size="20" fill="#6b7280">Issued:</text>
  <text x="200" y="310" font-family="Arial, Helvetica, sans-serif" font-size="20" fill="#6b7280">$today</text>
  <rect x="620" y="100" width="180" height="180" rx="12" fill="#10b981" opacity="0.15" />
  <text x="635" y="195" font-family="Arial, Helvetica, sans-serif" font-size="16" fill="#047857">MediCard</text>
  <text x="635" y="220" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#065f46">Scan in app</text>
  <text x="48" y="420" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#6b7280">This is a digital card. For assistance, contact support.</text>
  <text x="48" y="445" font-family="Arial, Helvetica, sans-serif" font-size="12" fill="#6b7280">© MediPlan Coop</text>
</svg>
SVG;
    }
}


