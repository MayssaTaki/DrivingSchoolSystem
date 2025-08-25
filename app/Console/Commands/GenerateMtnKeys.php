<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateMtnKeys extends Command
{
    protected $signature = 'mtn:keys {bits=1024}';
    protected $description = 'Generate RSA key pair for MTN Payment API';

    public function handle()
    {
        $bits = (int)$this->argument('bits');

        $dir = storage_path('keys');
        if (!is_dir($dir)) {
            mkdir($dir, 0700, true);
        }

        $res = openssl_pkey_new([
            'private_key_bits' => $bits,
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
        ]);

        if (!$res) {
            $this->error('Failed to generate RSA key pair.');
            return self::FAILURE;
        }

        openssl_pkey_export($res, $privatePem);

        $details = openssl_pkey_get_details($res);
        $publicPem = $details['key'] ?? null;

        if (!$publicPem) {
            $this->error('Failed to extract public key.');
            return self::FAILURE;
        }

       $privatePath = storage_path(env('MTN_PRIVATE_KEY_PATH', 'keys/mtn_private.pem'));
$publicPath  = storage_path(env('MTN_PUBLIC_KEY_PATH', 'keys/mtn_public.pem'));


        file_put_contents($privatePath, $privatePem);
        chmod($privatePath, 0600);

        file_put_contents($publicPath, $publicPem);
        chmod($publicPath, 0644);

        $publicOneLine = preg_replace('/-----BEGIN PUBLIC KEY-----|-----END PUBLIC KEY-----|\s+/', '', $publicPem);

        $this->info("Private Key: {$privatePath}");
        $this->info("Public  Key: {$publicPath}");
        $this->info("Public Key (one-line, base64 body):");
        $this->line($publicOneLine);

        return self::SUCCESS;
    }
}
