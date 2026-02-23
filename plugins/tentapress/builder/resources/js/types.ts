export type EditorResource = 'pages' | 'posts';

export type BlockField = {
    key: string;
    label: string;
    type: string;
    help?: string;
    placeholder?: string;
    options?: Array<{ value: string; label: string } | string>;
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
};
