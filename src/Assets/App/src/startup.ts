import './css/main.css';

import Aurelia, { ConsoleSink, LoggerConfiguration, LogLevel } from 'aurelia';
import {Enhance} from "./app/enhance";
import { BleetConfiguration } from '@blackcube/aurelia-kit';
import { QuillConfiguration } from '@blackcube/aurelia-bridge-quill';
import { ResumableConfiguration } from '@blackcube/aurelia-bridge-resumablejs';
import { JsonEditorConfiguration } from '@blackcube/aurelia-bridge-jsoneditor';
import * as globalComponents from './app/components/index';
import * as globalAttributes from './app/attributes/index';

declare var webpackBaseUrl: string;
declare var __webpack_public_path__: string;
// Only webpack provides __webpack_public_path__; the vite build loads its files relative to base './'.
if (typeof __webpack_public_path__ !== 'undefined') {
    if ((window as any).webpackBaseUrl) {
        __webpack_public_path__ = webpackBaseUrl;
    } else {
        __webpack_public_path__ = '';
    }
}

declare var PRODUCTION:boolean;

const page = document.querySelector('body') as HTMLElement;
const au = new Aurelia();

// au.register(globalAttributes);
if(typeof PRODUCTION === 'undefined' || PRODUCTION == false) {
    au.register(LoggerConfiguration.create({
        level: LogLevel.trace,
        colorOptions: 'colors',
        sinks: [ConsoleSink]
    }));
} else {
    au.register(LoggerConfiguration.create({
        level: LogLevel.error,
        colorOptions: 'colors',
        sinks: [ConsoleSink]
    }));
}
au.register(BleetConfiguration);
au.register(QuillConfiguration);
au.register(ResumableConfiguration);
au.register(JsonEditorConfiguration);
au.register(globalComponents);
au.register(globalAttributes);
au.enhance({
    host: page,
    component: Enhance
});
