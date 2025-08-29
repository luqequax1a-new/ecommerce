import { InjectionToken, Provider } from '@angular/core';
export interface LoadingBarConfig {
    latencyThreshold?: number;
}
export declare const LOADING_BAR_CONFIG: InjectionToken<LoadingBarConfig>;
export declare function provideLoadingBar(config: LoadingBarConfig): Provider;
