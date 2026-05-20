<?php

use App\Models\Order;

it('preserves a full jid including lid suffix', function () {
    expect(Order::normalizeWhatsAppJid('165515876151525@lid', null))
        ->toBe('165515876151525@lid');
});

it('appends s.whatsapp.net when only digits are provided as jid', function () {
    expect(Order::normalizeWhatsAppJid('255700000020', null))
        ->toBe('255700000020@s.whatsapp.net');
});

it('builds jid from customer phone when jid is empty', function () {
    expect(Order::normalizeWhatsAppJid(null, '+255 700 000 020'))
        ->toBe('255700000020@s.whatsapp.net');
});

it('maps tanzania local 0-prefix mobile to 255 for whatsapp net jid', function () {
    expect(Order::normalizeWhatsAppJid(null, '0678165524'))
        ->toBe('255678165524@s.whatsapp.net');
});

it('returns null when there is no jid and no usable phone', function () {
    expect(Order::normalizeWhatsAppJid(null, null))->toBeNull();
    expect(Order::normalizeWhatsAppJid('', ''))->toBeNull();
});
