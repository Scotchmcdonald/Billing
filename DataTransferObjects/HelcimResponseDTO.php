<?php

namespace Modules\Billing\DataTransferObjects;

class HelcimResponseDTO
{
    public function __construct(
        public readonly string $transactionId,
        public readonly string $status, // APPROVED, DECLINED, ERROR
        public readonly float $amount,
        public readonly string $currency,
        public readonly ?string $cardToken = null,
        public readonly ?string $cardNumber = null,
        public readonly ?string $approvalCode = null,
        public readonly ?string $invoiceNumber = null,
        public readonly array $rawResponse = []
    ) {}

    public function isSuccess(): bool
    {
        return $this->status === 'APPROVED';
    }

    public static function fromArray(array $data): self
    {
        return new self(
            transactionId: $data['transactionId'] ?? uniqid('txn_'),
            status: $data['status'] ?? 'ERROR',
            amount: (float) ($data['amount'] ?? 0),
            currency: $data['currency'] ?? 'USD',
            cardToken: $data['cardToken'] ?? null,
            cardNumber: $data['cardNumber'] ?? null,
            approvalCode: $data['approvalCode'] ?? null,
            invoiceNumber: $data['invoiceNumber'] ?? null,
            rawResponse: $data
        );
    }
}
