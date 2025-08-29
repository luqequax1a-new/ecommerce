import * as i0 from '@angular/core';
import { APP_INITIALIZER, NgModule } from '@angular/core';
import * as i1 from '@angular/router';
import { NavigationStart, NavigationError, NavigationEnd, NavigationCancel, Router, RouterModule } from '@angular/router';
import * as i2 from '@ngx-loading-bar/core';
import { LoadingBarService, LoadingBarModule } from '@ngx-loading-bar/core';

function getCurrentNavigationState(router) {
    // `getCurrentNavigation` only available in angular `7.2`
    const currentNavigation = router.getCurrentNavigation && router.getCurrentNavigation();
    if (currentNavigation && currentNavigation.extras) {
        return currentNavigation.extras.state;
    }
    return {};
}
function registerRouterListener(router, loader) {
    return () => {
        const ref = loader.useRef('router');
        router.events.subscribe((event) => {
            const navState = getCurrentNavigationState(router);
            if (navState && navState.ignoreLoadingBar) {
                return;
            }
            if (event instanceof NavigationStart) {
                ref.start();
            }
            if (event instanceof NavigationError || event instanceof NavigationEnd || event instanceof NavigationCancel) {
                ref.complete();
            }
        });
    };
}
function provideLoadingBarRouter() {
    return {
        provide: APP_INITIALIZER,
        useFactory: registerRouterListener,
        deps: [Router, LoadingBarService],
        multi: true,
    };
}

class LoadingBarRouterModule {
    constructor(router, loader) {
        registerRouterListener(router, loader)();
    }
    static { this.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarRouterModule, deps: [{ token: i1.Router }, { token: i2.LoadingBarService }], target: i0.ɵɵFactoryTarget.NgModule }); }
    static { this.ɵmod = i0.ɵɵngDeclareNgModule({ minVersion: "14.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarRouterModule, imports: [RouterModule, LoadingBarModule], exports: [RouterModule, LoadingBarModule] }); }
    static { this.ɵinj = i0.ɵɵngDeclareInjector({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarRouterModule, imports: [RouterModule, LoadingBarModule, RouterModule, LoadingBarModule] }); }
}
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarRouterModule, decorators: [{
            type: NgModule,
            args: [{
                    imports: [RouterModule, LoadingBarModule],
                    exports: [RouterModule, LoadingBarModule],
                }]
        }], ctorParameters: function () { return [{ type: i1.Router }, { type: i2.LoadingBarService }]; } });

/**
 * Generated bundle index. Do not edit.
 */

export { LoadingBarRouterModule, provideLoadingBarRouter };
//# sourceMappingURL=ngx-loading-bar-router.mjs.map
