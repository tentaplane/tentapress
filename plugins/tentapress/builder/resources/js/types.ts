export type EditorResource = 'pages' | 'posts' | 'global-content';

export type BlockField = {
    key: string;
    label: string;
    type: string;
    help?: string;
    placeholder?: string;
    options?: Array<{ value: string; label: string } | string>;
    columns?: BlockField[];
};

export type BlockDefinition = {
    type: string;
    name: string;
    description: string;
    version: number;
    fields: BlockField[];
    defaults?: Record<string, unknown>;
    example?: { props?: Record<string, unknown> };
};

export type MediaOption = {
    id: number;
    value: string;
    label: string;
    original_name?: string;
    mime_type?: string;
    is_image?: boolean;
};

export type BuilderBlock = {
    type: string;
    version: number;
    variant?: string;
    props: Record<string, unknown>;
    _key: string;
};

export type PatternDefinition = {
    id: string;
    name: string;
    description: string;
    blocks: Array<Omit<BuilderBlock, '_key'>>;
};

export type BuilderConfig = {
    initialJson: string;
    resource: EditorResource;
    snapshotEndpoint: string;
    storageKey: string;
    hiddenFieldId: string;
    definitions: BlockDefinition[];
    mediaOptions: MediaOption[];
    globalContentDetachUrl?: string;
    globalContentEditUrlTemplate?: string;
};

export type BuilderPreviewStyle = {
    href: string;
    media?: string;
};

export type BuilderPreviewBlockMap = {
    index: number;
    key: string;
};

export type BuilderPreviewDocument = {
    token: string;
    revision: string;
    lang: string;
    body_class: string;
    styles: BuilderPreviewStyle[];
    inline_styles: string[];
    body_html: string;
    block_map: BuilderPreviewBlockMap[];
};
