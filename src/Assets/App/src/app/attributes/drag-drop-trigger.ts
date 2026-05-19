import { bindable, customAttribute, IEventAggregator, ILogger, INode, resolve } from "aurelia";
import { Channels, DragDropAction } from '../enums/event-aggregator';
import { IDragDrop } from '../interfaces/event-aggregator';

/**
 * Trigger for drag-drop mode toggle.
 * Publishes EA event on click.
 *
 * Usage: <button dboard-drag-drop-trigger="tag-blocs-list">Réordonner</button>
 */
@customAttribute({ name: 'dboard-drag-drop-trigger', defaultProperty: 'id' })
export class BlapDragDropTriggerCustomAttribute {
    @bindable id: string = '';
    @bindable() action: DragDropAction = DragDropAction.Toggle;

    public constructor(
        private readonly logger: ILogger = resolve(ILogger).scopeTo('dboard-drag-drop-trigger'),
        private readonly element: HTMLElement = resolve(INode) as HTMLElement,
        private readonly ea: IEventAggregator = resolve(IEventAggregator),
    ) {
        this.logger.trace('constructor');
    }

    public attached() {
        this.logger.trace('attached', { id: this.id, action: this.action });
        this.element.addEventListener('click', this.onClick);
    }

    public detached() {
        this.logger.trace('detached');
        this.element.removeEventListener('click', this.onClick);
    }

    private onClick = (event: Event) => {
        event.preventDefault();
        event.stopPropagation();

        this.logger.debug('onClick', { id: this.id, action: this.action });

        this.ea.publish(Channels.DragDrop, <IDragDrop>{
            action: this.action,
            id: this.id,
        });
    };
}
