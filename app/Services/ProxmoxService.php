<?php

namespace App\Services;

use App\Models\ServiceRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProxmoxService
{
    protected string $baseUrl;

    protected ?string $ticket = null;

    protected ?string $csrf = null;

    protected const PROFILES = [
        'bronze' => [
            'cores' => 1,
            'cpuunits' => 512,
            'memory' => 384,
            'disk' => 3,
        ],
        'silver' => [
            'cores' => 1,
            'cpuunits' => 1024,
            'memory' => 768,
            'disk' => 5,
        ],
        'gold' => [
            'cores' => 1,
            'cpuunits' => 1400,
            'memory' => 1200,
            'disk' => 8,
        ],
    ];

    public function __construct()
    {
        $this->baseUrl = sprintf(
            '%s://%s:%s/api2/json',
            env('PROXMOX_SCHEME', 'https'),
            env('PROXMOX_HOST'),
            env('PROXMOX_PORT', 8006)
        );
    }

    public function connect(): self
    {
        $this->ensureAuthenticated();

        return $this;
    }

    public function listNodes(): array
    {
        return $this->get('nodes');
    }


    public function provisionLxc(ServiceRequest $serviceRequest): array
    {
        $this->validateConfiguration();

        $plan = self::PROFILES[$serviceRequest->profile] ?? self::PROFILES['bronze'];
        $node = env('PROXMOX_NODE', 'px1');

        $storage = env('PROXMOX_STORAGE', 'zpool');
        $vmid = $this->nextVmid();

        $hostname = sprintf('px-%s-%s', $serviceRequest->profile, $vmid);
        $username = env('PROXMOX_LXC_USER', 'root');
        $password = Str::random(16);

        $payload = [
            'vmid' => $vmid,
            'hostname' => $hostname,
            'ostemplate' => env('PROXMOX_TEMPLATE'),
            'password' => $password,
            'cores' => $plan['cores'],
            'memory' => $plan['memory'],
            'cpuunits' => $plan['cpuunits'],
            'rootfs' => sprintf('%s:%d', $storage, $plan['disk']),
            'net0' => sprintf('name=eth0,bridge=%s,ip=dhcp', env('PROXMOX_BRIDGE', 'vmbr0')),
            'unprivileged' => 1,
            'start' => 1,
            'description' => 'Service Request #' . $serviceRequest->id,
        ];

        $keyPair = $this->generateSshKeyPair($serviceRequest->id);
        $payload['ssh-public-keys'] = str_replace(
            ["\r", "\n"],
            '',
            $keyPair['public']
        );

        if ($nameserver = env('PROXMOX_NAMESERVER')) {
            $payload['nameserver'] = $nameserver;
        }

        $this->post("nodes/{$node}/lxc", $payload);

        $ipAddress = $this->fetchIpAddress($node, $vmid);

        return [
            'node' => $node,
            'vmid' => (string) $vmid,
            'hostname' => $hostname,
            'ip_address' => $ipAddress ?? 'creazione in corso',
            'username' => $username,
            'password' => $password,
            'ssh_key' => basename($keyPair['private_path']),
            'provisioned_at' => now(),
        ];


    }

    protected function validateConfiguration(): void
    {
        if (! env('PROXMOX_TEMPLATE')) {
            throw new \RuntimeException('PROXMOX_TEMPLATE vuoto.');
        }

        if (! env('PROXMOX_HOST') || ! env('PROXMOX_USER') || ! env('PROXMOX_PASSWORD')) {
            throw new \RuntimeException('manca config Proxmox (host, user o password).');
        }
    }

    protected function nextVmid(): int
    {
        $response = $this->get('cluster/nextid');

        $candidate = is_array($response) ? ($response['id'] ?? reset($response)) : $response;

        if (is_numeric($candidate)) {
            return (int) $candidate;
        }

        return (int) (200 + ($response ?? random_int(1, 500)));
    }

    protected function fetchIpAddress(string $node, int $vmid, int $attempts = 10, int $waitSeconds = 5): ?string
    {
        for ($i = 0; $i < $attempts; $i++) {
            $ip = $this->getIpAddressFromProxmox($node, $vmid);

            if ($ip && filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }

            if ($waitSeconds > 0) {
                sleep($waitSeconds);
            }
        }

        return null;
    }

    public function refreshIp(ServiceRequest $serviceRequest): void
    {
        if ($serviceRequest->status !== ServiceRequest::STATUS_APPROVED) {
            return;
        }

        if (! empty($serviceRequest->ip_address) && $serviceRequest->ip_address !== 'creazione in corso') {
            return;
        }

        if (! $serviceRequest->node || ! $serviceRequest->vmid) {
            return;
        }

        $ip = $this->fetchIpAddress($serviceRequest->node, (int) $serviceRequest->vmid, 1, 0);

        Log::warning('refreship chiamato', [
            'id' => $serviceRequest->id,
            'ip' => $serviceRequest->ip_address,
        ]);


        if ($ip) {
            $serviceRequest->update(['ip_address' => $ip]);
        }
    }

    protected function getIpAddressFromProxmox(string $node, int $vmid): ?string
    {
        try {
            $interfaces = $this->get("nodes/{$node}/lxc/{$vmid}/interfaces");

            foreach ($interfaces as $iface) {
                if (($iface['name'] ?? '') === 'lo') {
                    continue;
                }

                if (!empty($iface['ip-addresses']) && is_array($iface['ip-addresses'])) {
                    foreach ($iface['ip-addresses'] as $addr) {
                        if (
                            ($addr['ip-address-type'] ?? '') === 'inet' &&
                            filter_var($addr['ip-address'], FILTER_VALIDATE_IP)
                        ) {
                            return $addr['ip-address'];
                        }
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('IP error', [
                'node' => $node,
                'vmid' => $vmid,
                'error' => $e->getMessage(),
            ]);
        }

        return null;
    }

    protected function ensureAuthenticated(): void
    {
        if ($this->ticket && $this->csrf) {
            return;
        }

        $response = $this->httpClient()
            ->asForm()
            ->post($this->baseUrl . '/access/ticket', [
                'username' => sprintf('%s@%s', env('PROXMOX_USER'), env('PROXMOX_REALM', 'pam')),
                'password' => env('PROXMOX_PASSWORD'),
            ])
            ->throw()
            ->json('data');

        $this->ticket = $response['ticket'] ?? null;
        $this->csrf = $response['CSRFPreventionToken'] ?? null;
    }

    protected function get(string $path, array $query = [])
    {
        $this->ensureAuthenticated();

        $response = $this->httpClient()
            ->withHeaders($this->headers())
            ->get($this->baseUrl . '/' . $path, $query)
            ->throw();

        return $this->parseData($response);
    }


    protected function post(string $path, array $payload = []): array
    {
        $this->ensureAuthenticated();

        $response = $this->httpClient()
            ->withHeaders($this->headers(true))
            ->asForm()
            ->post($this->baseUrl . '/' . $path, $payload)
            ->throw();

        return $this->parseData($response);
    }

    protected function parseData(\Illuminate\Http\Client\Response $response): array
    {
        $data = $response->json('data');

        if (is_array($data)) {
            return $data;
        }

        if (is_string($data)) {
            return ['message' => $data];
        }

        return [];
    }

    protected function headers(bool $write = false): array
    {
        $headers = [
            'Cookie' => 'PVEAuthCookie=' . $this->ticket,
        ];

        if ($write) {
            $headers['CSRFPreventionToken'] = $this->csrf;
        }

        return $headers;
    }

    protected function httpClient(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withOptions([
            'verify' => false,
            'timeout' => 5,
            'connect_timeout' => 3,
        ]);
    }

    protected function generateSshKeyPair(int $requestId): array
    {
        $dir = storage_path('app/ssh_keys');
        $base = $dir . "/request_{$requestId}";

        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $cmd = sprintf(
            'ssh-keygen -t ed25519 -N "" -f %s',
            escapeshellarg($base)
        );

        exec($cmd, $out, $code);

        if ($code !== 0) {
            throw new \RuntimeException('errore creazione chiave SSH');
        }

        return [
            'public'  => trim(file_get_contents($base . '.pub')),
            'private_path' => $base,
        ];
    }




}
