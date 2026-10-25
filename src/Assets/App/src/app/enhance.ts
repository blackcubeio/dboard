import {ILogger, resolve} from 'aurelia';

export class Enhance {
    constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('Enhance'),
    ) {
        this.logger.debug('constructor');
    }

    public attaching()
    {
        this.logger.debug('Attaching');
    }
}