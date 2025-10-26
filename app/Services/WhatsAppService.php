<?php

namespace App\Services;

class WhatsAppService
{
    /**
     * Generate a WhatsApp CTA URL with a pre-filled message.
     *
     * @param array<string, mixed> $context
     */
    public function generateCtaUrl(array $context = []): ?string
    {
        if (!config('services.whatsapp.enabled', false)) {
            return null;
        }

        $rawNumber = config('services.whatsapp.business_number');

        if (!$rawNumber) {
            return null;
        }

        $number = preg_replace('/\D+/', '', (string) $rawNumber);

        if (!$number) {
            return null;
        }

        $message = $this->buildMessage($context);
        $query = http_build_query(['text' => $message], '', '&', PHP_QUERY_RFC3986);

        return sprintf('https://wa.me/%s?%s', $number, $query);
    }

    /**
     * Build the WhatsApp message body.
     *
     * @param array<string, mixed> $context
     */
    protected function buildMessage(array $context): string
    {
        $template = config(
            'services.whatsapp.default_message',
            'Halo, saya tertarik dengan simulasi produk :product (ID: :simulation_id).'
        );

        $replacements = [
            ':product' => $context['product_name'] ?? 'produk skincare',
            ':simulation_id' => $context['simulation_id'] ?? 'N/A',
            ':user_name' => $context['user_name'] ?? 'client',
            ':company' => $context['company'] ?? 'Perusahaan Anda',
        ];

        $message = strtr($template, $replacements);

        if (!empty($context['additional_notes'])) {
            $message .= "\n\nCatatan: " . $context['additional_notes'];
        }

        return $message;
    }
}
