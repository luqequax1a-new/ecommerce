import { LoadingBarService } from './loading-bar.service';
import * as i0 from "@angular/core";
export declare class NgxLoadingBar {
    private loader;
    includeSpinner: boolean;
    includeBar: boolean;
    fixed: boolean;
    color: string;
    value: number;
    ref: string;
    height: string;
    diameter: string;
    get value$(): import("rxjs").Observable<number>;
    constructor(loader: LoadingBarService);
    static ɵfac: i0.ɵɵFactoryDeclaration<NgxLoadingBar, never>;
    static ɵcmp: i0.ɵɵComponentDeclaration<NgxLoadingBar, "ngx-loading-bar", never, { "includeSpinner": { "alias": "includeSpinner"; "required": false; }; "includeBar": { "alias": "includeBar"; "required": false; }; "fixed": { "alias": "fixed"; "required": false; }; "color": { "alias": "color"; "required": false; }; "value": { "alias": "value"; "required": false; }; "ref": { "alias": "ref"; "required": false; }; "height": { "alias": "height"; "required": false; }; "diameter": { "alias": "diameter"; "required": false; }; }, {}, never, never, true, never>;
}
