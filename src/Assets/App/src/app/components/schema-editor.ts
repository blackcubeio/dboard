import { IPlatform, ILogger, bindable, customElement, INode, resolve } from "aurelia";
import JSONEditor, { JSONEditorOptions } from 'jsoneditor';
import template from './schema-editor.html';

@customElement({ name: 'dboard-schema-editor', template })
export class SchemaEditor {
    private hiddenField!: HTMLInputElement;
    private editorElement!: HTMLElement;
    private jsonSchema!: JSONEditor;

    @bindable() public fieldId: string = '';
    @bindable() public fieldName: string = '';
    @bindable() public schema: string = '';
    @bindable() public language: string = '';
    @bindable() public disabled: boolean = false;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('<dboard-schema-editor>'),
        private readonly platform: IPlatform = resolve(IPlatform),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
    ) {
    }

    public attached(): void {
        this.logger.debug('Attached');
        if (this.fieldId) {
            this.hiddenField.id = this.fieldId;
        }
        if (this.fieldName) {
            this.hiddenField.name = this.fieldName;
        }
        if (this.schema) {
            this.hiddenField.value = this.schema;
        }
        this.buildEditor();
    }

    private buildEditor(): void {
        // @ts-ignore
        let config: JSONEditorOptions = {
            mode: "tree",
            modes: ["tree", "text"],
            search: false,
            navigationBar: false,
            statusBar: true,
            mainMenuBar: true,
            enableSort: false,
            enableTransform: false,
            language: "en",
            templates: [
                {
                    text: 'text',
                    title: 'Insert Text property',
                    className: 'jsoneditor-type-object',
                    field: 'text',
                    value: {
                        type: 'string',
                        title: 'Field title',
                        description: 'Field description',
                        minLength: 1,
                        maxLength: 255
                    }
                },
                {
                    text: 'Wysiwyg',
                    title: 'Insert Wysiwyg property',
                    className: 'jsoneditor-type-object',
                    field: "wysiwyg",
                    value: {
                        type: 'string',
                        format: 'wysiwyg',
                        options: {
                            theme: "snow",
                            modules: {
                                toolbar: [
                                    ["bold", "italic", "underline"],
                                    [
                                        {
                                            list: "bullet"
                                        }
                                    ],
                                    ["link"]
                                ]
                            },
                            formats: ["bold", "italic", "link", "underline", "list"]
                        },
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Route',
                    title: 'Insert Route property',
                    className: 'jsoneditor-type-object',
                    field: "routes",
                    value: {
                        type: 'string',
                        field: "routes",
                        title: "Target",
                        description: "Internal route"
                    }
                },
                {
                    text: 'Checkbox',
                    title: 'Insert Checkbox property',
                    className: 'jsoneditor-type-object',
                    field: 'checkbox',
                    value: {
                        type: 'string',
                        format: 'checkbox',
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Image',
                    title: 'Insert Image property',
                    className: 'jsoneditor-type-object',
                    field: "image",
                    value: {
                        type: 'string',
                        format: 'file',
                        fileType: 'png,jpg',
                        imageWidth: 600,
                        imageHeight: 200,
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'File',
                    title: 'Insert File property',
                    className: 'jsoneditor-type-object',
                    field: "file",
                    value: {
                        type: 'string',
                        format: 'file',
                        fileType: 'pdf',
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Email',
                    title: 'Insert Email property',
                    className: 'jsoneditor-type-object',
                    field: 'email',
                    value: {
                        type: 'string',
                        format: 'email',
                        minLength: 1,
                        maxLength: 255,
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Textarea',
                    title: 'Insert Textarea property',
                    className: 'jsoneditor-type-object',
                    field: "textarea",
                    value: {
                        type: 'string',
                        format: 'textarea',
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Radios',
                    title: 'Insert Radios property',
                    className: 'jsoneditor-type-object',
                    field: 'radiolist',
                    value: {
                        type: 'string',
                        format: 'radiolist',
                        items: [
                            {
                                title: 'Item 1',
                                value: 'item1'
                            }
                        ],
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Dropdown',
                    title: 'Insert Dropdown property',
                    className: 'jsoneditor-type-object',
                    field: 'dropdown',
                    value: {
                        type: 'string',
                        multiple: false,
                        format: 'dropdownlist',
                        items: [
                            {
                                title: 'Item 1',
                                value: 'item1'
                            }
                        ],
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Regexp',
                    title: 'Insert Regexp property',
                    className: 'jsoneditor-type-object',
                    field: "regexp",
                    value: {
                        type: 'string',
                        pattern: '^[a-z0-9]+$',
                        minLength: 1,
                        maxLength: 255,
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Images',
                    title: 'Insert Multiple images property',
                    className: 'jsoneditor-type-object',
                    field: "image",
                    value: {
                        type: 'string',
                        format: 'files',
                        fileType: 'png,jpg',
                        imageWidth: 600,
                        imageHeight: 200,
                        title: 'Field title',
                        description: 'Field description'
                    }
                },
                {
                    text: 'Files',
                    title: 'Insert Multiple files property',
                    className: 'jsoneditor-type-object',
                    field: "files",
                    value: {
                        type: 'string',
                        format: 'files',
                        fileType: 'pdf',
                        title: 'Field title',
                        description: 'Field description'
                    }
                }
            ]
        };
        if (this.language) {
            config.language = this.language;
        }
        if (this.disabled) {
            config.onEditable = () => false;
            // config.mainMenuBar = false;
        } else {
            config.onChangeJSON = (jsonData: any) => {
                // @ts-ignore
                this.hiddenField.value = JSON.stringify(jsonData, null, 4);
            };
            config.onChangeText = (jsonString: string) => {
                // @ts-ignore
                this.hiddenField.value = jsonString;
            };
        }
        this.jsonSchema = new JSONEditor(this.editorElement, config);
        this.jsonSchema.setText(this.schema);
        this.jsonSchema.expandAll();
    }
}
