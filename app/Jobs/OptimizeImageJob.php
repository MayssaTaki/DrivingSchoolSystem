<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Spatie\ImageOptimizer\OptimizerChain;
use Spatie\ImageOptimizer\Optimizers\Jpegoptim;
use Spatie\ImageOptimizer\Optimizers\Pngquant;
use Spatie\ImageOptimizer\Optimizers\Optipng;
use Spatie\ImageOptimizer\Optimizers\Gifsicle;
use Symfony\Component\Process\Exception\ProcessFailedException;

class OptimizeImageJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected string $path;

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function handle(): void
    {
        Log::info("🚀 بدء ضغط الصورة: " . basename($this->path));

        if (!file_exists($this->path)) {
            Log::error('❌ الملف غير موجود: ' . $this->path);
            return;
        }

        $binaryPath = config('image-optimizer.binary_path');
        $originalSize = filesize($this->path);
        $originalDimensions = $this->getImageDimensions($this->path);
        $extension = strtolower(pathinfo($this->path, PATHINFO_EXTENSION));

        Log::info("📊 قبل الضغط - الحجم: {$this->formatBytes($originalSize)} | الأبعاد: {$originalDimensions} | النوع: " . strtoupper($extension));

        try {
            $optimizerChain = (new OptimizerChain())
                ->setTimeout(config('image-optimizer.timeout'))
                ->useLogger(app('log'));

            $optimizerChain->addOptimizer(
                (new Jpegoptim(config('image-optimizer.optimizers.' . Jpegoptim::class)))
              ->setBinaryPath($binaryPath)
            );

            $optimizerChain->addOptimizer(
                (new Pngquant(config('image-optimizer.optimizers.' . Pngquant::class)))
             ->setBinaryPath($binaryPath)
            );

            $optimizerChain->addOptimizer(
                (new Optipng(config('image-optimizer.optimizers.' . Optipng::class)))
            ->setBinaryPath($binaryPath)
            );

            $optimizerChain->addOptimizer(
                (new Gifsicle(config('image-optimizer.optimizers.' . Gifsicle::class)))
             ->setBinaryPath($binaryPath)
            );

            $optimizerChain->optimize($this->path);

            $compressedSize = filesize($this->path);
            $compressedDimensions = $this->getImageDimensions($this->path);
            $reduction = $originalSize > 0
                ? round((($originalSize - $compressedSize) / $originalSize) * 100, 2)
                : 0;

            Log::info("✅ بعد الضغط - الحجم: " . $this->formatBytes($compressedSize) . " | الأبعاد: {$compressedDimensions}");
            Log::info("📉 نسبة الضغط: {$reduction}%");
            Log::info("💾 المساحة المحفوظة: " . $this->formatBytes($originalSize - $compressedSize));

        } catch (ProcessFailedException $e) {
            Log::error('🔥 فشل في ضغط الصورة: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('⚠️ خطأ غير متوقع: ' . $e->getMessage());
        }
    }

    private function getImageDimensions(string $path): string
    {
        try {
            $dimensions = getimagesize($path);
            return $dimensions ? "{$dimensions[0]}x{$dimensions[1]}" : 'غير معروف';
        } catch (\Exception $e) {
            return 'غير معروف';
        }
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $pow = $bytes > 0 ? floor(log($bytes, 1024)) : 0;
        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
