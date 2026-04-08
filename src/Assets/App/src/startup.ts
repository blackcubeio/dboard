import './css/main.css';

import Aurelia, { ConsoleSink, LoggerConfiguration, LogLevel } from 'aurelia';
import {Enhance} from "./app/enhance";
import { BleetConfiguration } from '@blackcube/aurelia2-bleet';
import * as globalComponents from './app/components/index';
import * as globalAttributes from './app/attributes/index';

declare var webpackBaseUrl: string;
declare var __webpack_public_path__: string;
if ((window as any).webpackBaseUrl) {
    __webpack_public_path__ = webpackBaseUrl;
} else {
    __webpack_public_path__ = '';
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
au.register(globalComponents);
au.register(globalAttributes);
au.enhance({
    host: page,
    component: Enhance
});
