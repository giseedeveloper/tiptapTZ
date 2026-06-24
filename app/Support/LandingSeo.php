<?php

namespace App\Support;

class LandingSeo
{
    /**
     * @param array{
     *     market: string,
     *     title: string,
     *     description: string,
     *     canonical_url: string,
     *     logo_url: string,
     *     locale: string,
     *     geo_region: string,
     *     geo_place_name: string,
     *     whatsapp_url: string,
     *     social: array<string, string|null>,
     *     offices: array<string, array{name: string, city: string, lines: array<int, string>}>,
     *     faq: array{items: array<int, array{question: string, answer: string}>},
     *     currency_code?: string,
     * } $context
     * @return array<string, mixed>
     */
    public static function structuredData(array $context): array
    {
        $canonical = (string) $context['canonical_url'];
        $market = (string) $context['market'];
        $primaryOffice = $context['offices'][$market] ?? reset($context['offices']) ?: null;

        $graph = [
            self::organizationNode($context, $primaryOffice),
            self::websiteNode($context),
            self::softwareApplicationNode($context),
        ];

        if (! empty($context['faq']['items'])) {
            $graph[] = self::faqPageNode($context);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @param  array{name: string, city: string, lines: array<int, string>}|null  $office
     * @return array<string, mixed>
     */
    protected static function organizationNode(array $context, ?array $office): array
    {
        $sameAs = array_values(array_filter($context['social'] ?? []));

        $node = [
            '@type' => 'Organization',
            '@id' => $context['canonical_url'].'#organization',
            'name' => 'TipTap Africa',
            'url' => $context['canonical_url'],
            'logo' => $context['logo_url'],
            'description' => $context['description'],
            'areaServed' => [
                '@type' => 'Country',
                'name' => $context['geo_place_name'],
            ],
        ];

        if ($office) {
            $node['address'] = [
                '@type' => 'PostalAddress',
                'addressLocality' => $office['city'],
                'streetAddress' => implode(', ', $office['lines']),
                'addressCountry' => $context['geo_region'],
            ];
        }

        if ($sameAs !== []) {
            $node['sameAs'] = $sameAs;
        }

        if (filled($context['whatsapp_url'] ?? null)) {
            $node['contactPoint'] = [
                '@type' => 'ContactPoint',
                'contactType' => 'customer support',
                'availableLanguage' => ['English', 'Swahili'],
                'url' => $context['whatsapp_url'],
            ];
        }

        return $node;
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected static function websiteNode(array $context): array
    {
        return [
            '@type' => 'WebSite',
            '@id' => $context['canonical_url'].'#website',
            'url' => $context['canonical_url'],
            'name' => 'TipTap',
            'description' => $context['description'],
            'inLanguage' => $context['locale'],
            'publisher' => [
                '@id' => $context['canonical_url'].'#organization',
            ],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected static function softwareApplicationNode(array $context): array
    {
        return [
            '@type' => 'SoftwareApplication',
            '@id' => $context['canonical_url'].'#software',
            'name' => 'TipTap',
            'applicationCategory' => 'BusinessApplication',
            'operatingSystem' => 'Web, WhatsApp',
            'description' => $context['description'],
            'url' => $context['canonical_url'],
            'image' => $context['logo_url'],
            'offers' => [
                '@type' => 'Offer',
                'price' => '0',
                'priceCurrency' => (string) ($context['currency_code'] ?? 'TZS'),
                'description' => '14-day free trial',
                'url' => $context['canonical_url'].'#pricing',
            ],
            'featureList' => [
                'QR table ordering',
                'TipTap Rafiki WhatsApp bot',
                'Kitchen display system',
                'Manager dashboard and analytics',
            ],
            'areaServed' => $context['geo_place_name'],
        ];
    }

    /**
     * @param  array<string, mixed>  $context
     * @return array<string, mixed>
     */
    protected static function faqPageNode(array $context): array
    {
        $entities = [];

        foreach ($context['faq']['items'] as $item) {
            $entities[] = [
                '@type' => 'Question',
                'name' => (string) ($item['question'] ?? ''),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => (string) ($item['answer'] ?? ''),
                ],
            ];
        }

        return [
            '@type' => 'FAQPage',
            '@id' => $context['canonical_url'].'#faq',
            'mainEntity' => $entities,
        ];
    }
}
