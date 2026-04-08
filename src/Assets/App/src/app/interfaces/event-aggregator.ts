import { DragDropAction, DragDropStatus } from '../enums/event-aggregator';

export interface IDragDrop {
    action: DragDropAction;
    id?: string;
}

export interface IDragDropStatus {
    status: DragDropStatus;
    id?: string;
}
