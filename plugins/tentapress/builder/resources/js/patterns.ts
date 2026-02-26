import type { PatternDefinition } from './types';

export const BUILTIN_PATTERNS: PatternDefinition[] = [
    {
        id: 'hero-cta',
        name: 'Hero + CTA',
        description: 'Headline, supporting copy, and action buttons.',
        blocks: [
            {
                type: 'blocks/hero',
                version: 1,
                props: {
                    headline: 'Build beautiful pages faster',
                    subheadline: 'Use the visual builder to compose polished sections in minutes.',
                },
            },
            {
                type: 'blocks/cta',
                version: 1,
                props: {
                    headline: 'Start with your next section',
                    body: 'Insert blocks, tune spacing, and preview immediately.',
                },
            },
        ],
    },
    {
        id: 'features-proof',
        name: 'Features + Testimonial',
        description: 'Feature grid followed by social proof.',
        blocks: [
            {
                type: 'blocks/features',
                version: 1,
                props: {
                    headline: 'Why teams choose TentaPress',
                },
            },
            {
                type: 'blocks/testimonial',
                version: 1,
                props: {
                    quote: 'The builder made page creation fast and predictable.',
                    name: 'Product Team',
                },
            },
        ],
    },
    {
        id: 'faq-footer-cta',
        name: 'FAQ + Closing CTA',
        description: 'Answer common questions and close with a final action.',
        blocks: [
            {
                type: 'blocks/faq',
                version: 1,
                props: {
                    headline: 'Frequently asked questions',
                },
            },
            {
                type: 'blocks/cta',
                version: 1,
                props: {
                    headline: 'Ready to launch?',
                    body: 'Ship faster with predictable block-driven content.',
                },
            },
        ],
    },
];
