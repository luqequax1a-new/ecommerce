import * as i0 from '@angular/core';
import { Injectable, NgModule } from '@angular/core';
import { HttpContextToken, HTTP_INTERCEPTORS, HttpClientModule } from '@angular/common/http';
import { tap, finalize } from 'rxjs/operators';
import * as i1 from '@ngx-loading-bar/core';
import { LoadingBarModule } from '@ngx-loading-bar/core';

const NGX_LOADING_BAR_IGNORED = new HttpContextToken(() => false);
class LoadingBarInterceptor {
    constructor(loader) {
        this.loader = loader;
    }
    intercept(req, next) {
        if (req.context.get(NGX_LOADING_BAR_IGNORED) === true) {
            return next.handle(req);
        }
        let started = false;
        const ref = this.loader.useRef('http');
        return next.handle(req).pipe(tap(() => {
            if (!started) {
                ref.start();
                started = true;
            }
        }), finalize(() => started && ref.complete()));
    }
    static { this.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarInterceptor, deps: [{ token: i1.LoadingBarService }], target: i0.ɵɵFactoryTarget.Injectable }); }
    static { this.ɵprov = i0.ɵɵngDeclareInjectable({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarInterceptor }); }
}
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarInterceptor, decorators: [{
            type: Injectable
        }], ctorParameters: function () { return [{ type: i1.LoadingBarService }]; } });
function provideLoadingBarInterceptor() {
    return {
        provide: HTTP_INTERCEPTORS,
        useClass: LoadingBarInterceptor,
        multi: true,
    };
}

class LoadingBarHttpClientModule {
    static { this.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarHttpClientModule, deps: [], target: i0.ɵɵFactoryTarget.NgModule }); }
    static { this.ɵmod = i0.ɵɵngDeclareNgModule({ minVersion: "14.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarHttpClientModule, imports: [HttpClientModule, LoadingBarModule], exports: [HttpClientModule, LoadingBarModule] }); }
    static { this.ɵinj = i0.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarHttpClientModule, providers: [
            {
                provide: HTTP_INTERCEPTORS,
                useClass: LoadingBarInterceptor,
                multi: true,
            },
        ], imports: [HttpClientModule, LoadingBarModule, HttpClientModule, LoadingBarModule] }); }
}
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarHttpClientModule, decorators: [{
            type: NgModule,
            args: [{
                    imports: [HttpClientModule, LoadingBarModule],
                    exports: [HttpClientModule, LoadingBarModule],
                    providers: [
                        {
                            provide: HTTP_INTERCEPTORS,
                            useClass: LoadingBarInterceptor,
                            multi: true,
                        },
                    ],
                }]
        }] });

/**
 * Generated bundle index. Do not edit.
 */

export { LoadingBarHttpClientModule, NGX_LOADING_BAR_IGNORED, provideLoadingBarInterceptor };
//# sourceMappingURL=ngx-loading-bar-http-client.mjs.map
