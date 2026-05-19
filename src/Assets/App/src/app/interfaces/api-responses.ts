import { IAjaxify, IToast } from '@blackcube/aurelia2-bleet';

export interface ISlugGeneratorResponse {
    elementId: number;
    url: string;
}

export interface IWebauthnResponse {
    toast?: IToast;
    ajaxify?: IAjaxify;
}

export interface ILoginDeviceResponse {
    redirect: string;
}
