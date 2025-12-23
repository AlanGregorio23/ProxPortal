<?php

namespace App\Http\Controllers;

use App\Models\ServiceRequest;
use App\Services\ProxmoxService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminRequestController extends Controller
{
    public function approve(ServiceRequest $serviceRequest, ProxmoxService $proxmoxService)
    {
        if (Auth::user()->type !== 'admin') {
            return redirect()->route('requests.index');
        }

        if ($serviceRequest->status === ServiceRequest::STATUS_APPROVED && $serviceRequest->vmid) {
            return redirect()->back()->with('status', 'richiesta già approvata');
        }

        try {
            $provisioning = $proxmoxService->provisionLxc($serviceRequest);

            $serviceRequest->update(array_merge(
                ['status' => ServiceRequest::STATUS_APPROVED],
                $provisioning
            ));

            return redirect()->back()->with('status', 'richiesta approvata');
        } catch (\Throwable $exception) {
            Log::error('errore creazione container', [
                'request_id' => $serviceRequest->id,
                'error' => $exception->getMessage(),
            ]);

            $serviceRequest->update(['status' => ServiceRequest::STATUS_PENDING]);

            return redirect()->back()->withErrors([
                'provisioning' => 'impossibile creare: ' . $exception->getMessage(),
            ]);
        }

    }

    public function reject(ServiceRequest $serviceRequest)
    {
        if (Auth::user()->type !== 'admin') {
            return redirect()->route('requests.index');
        }

        $serviceRequest->update(['status' => ServiceRequest::STATUS_REJECTED]);

        return redirect()->back()->with('status', 'richiesta rifiutata.');
    }

    public function downloadSshKey(ServiceRequest $serviceRequest)
    {
        if (
            Auth::id() !== $serviceRequest->user_id &&
            Auth::user()->type !== 'admin'
        ) {
            abort(403);
        }

        $path = storage_path('app/ssh_keys/request_' . $serviceRequest->id);

        if (!file_exists($path)) {
            abort(404, 'chiave non disponibile');
        }

        return response()->download(
            $path,
            "ssh-key-{$serviceRequest->hostname}.key",
            [
                'Content-Type' => 'application/octet-stream',
            ]
        );
    }

}
