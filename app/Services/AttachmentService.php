<?php

namespace App\Services;

use App\Models\FinancialAttachment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function storeUploaded(Model $attachable, UploadedFile $file, array $context = [], $user = null): FinancialAttachment
    {
        $disk = $context['disk'] ?? 'public';
        $directory = $context['directory'] ?? $this->directoryFor($attachable, $context);
        $name = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs($directory, $name, $disk);

        return $this->recordStoredFile($attachable, array_merge($context, [
            'disk' => $disk,
            'file_path' => $path,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
        ]), $user);
    }

    public function recordStoredFile(Model $attachable, array $context, $user = null): FinancialAttachment
    {
        $user = $user ?: auth()->user();

        return FinancialAttachment::create([
            'tenant_id' => $context['tenant_id'] ?? $this->valueFrom($attachable, ['tenant_id', 'tenants_id']) ?? ($user->tenant_id ?? $user->tenants_id ?? null),
            'organization_id' => $context['organization_id'] ?? $this->valueFrom($attachable, ['organization_id']),
            'attachable_type' => get_class($attachable),
            'attachable_id' => $attachable->getKey(),
            'voucher_id' => $context['voucher_id'] ?? $this->valueFrom($attachable, ['voucher_id']),
            'attachment_kind' => $context['attachment_kind'] ?? 'document',
            'disk' => $context['disk'] ?? 'public',
            'file_path' => $context['file_path'],
            'original_name' => $context['original_name'] ?? basename($context['file_path']),
            'mime_type' => $context['mime_type'] ?? null,
            'file_size' => $context['file_size'] ?? $this->size($context['disk'] ?? 'public', $context['file_path']),
            'note' => $context['note'] ?? null,
            'created_by' => $user?->id,
            'updated_by' => $user?->id,
        ]);
    }

    public function delete(FinancialAttachment $attachment, $user = null): bool
    {
        $attachment->updated_by = $user?->id ?: auth()->id();
        $attachment->save();

        return (bool) $attachment->delete();
    }

    private function directoryFor(Model $attachable, array $context): string
    {
        $tenantId = $context['tenant_id'] ?? $this->valueFrom($attachable, ['tenant_id', 'tenants_id']) ?? 'global';

        return 'attachments/' . $tenantId . '/' . Str::snake(class_basename($attachable)) . '/' . $attachable->getKey();
    }

    private function size(string $disk, string $path): ?int
    {
        return Storage::disk($disk)->exists($path) ? Storage::disk($disk)->size($path) : null;
    }

    private function valueFrom(Model $model, array $keys)
    {
        foreach ($keys as $key) {
            if (!empty($model->{$key})) {
                return $model->{$key};
            }
        }

        return null;
    }
}
