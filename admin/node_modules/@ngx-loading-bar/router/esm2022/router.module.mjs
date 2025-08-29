import { NgModule } from '@angular/core';
import { RouterModule } from '@angular/router';
import { LoadingBarModule } from '@ngx-loading-bar/core';
import { registerRouterListener } from './router.service';
import * as i0 from "@angular/core";
import * as i1 from "@angular/router";
import * as i2 from "@ngx-loading-bar/core";
export class LoadingBarRouterModule {
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicm91dGVyLm1vZHVsZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uL3BhY2thZ2VzL3JvdXRlci9zcmMvcm91dGVyLm1vZHVsZS50cyJdLCJuYW1lcyI6W10sIm1hcHBpbmdzIjoiQUFBQSxPQUFPLEVBQUUsUUFBUSxFQUFFLE1BQU0sZUFBZSxDQUFDO0FBQ3pDLE9BQU8sRUFBRSxZQUFZLEVBQVUsTUFBTSxpQkFBaUIsQ0FBQztBQUN2RCxPQUFPLEVBQUUsZ0JBQWdCLEVBQXFCLE1BQU0sdUJBQXVCLENBQUM7QUFDNUUsT0FBTyxFQUFFLHNCQUFzQixFQUFFLE1BQU0sa0JBQWtCLENBQUM7Ozs7QUFNMUQsTUFBTSxPQUFPLHNCQUFzQjtJQUNqQyxZQUFZLE1BQWMsRUFBRSxNQUF5QjtRQUNuRCxzQkFBc0IsQ0FBQyxNQUFNLEVBQUUsTUFBTSxDQUFDLEVBQUUsQ0FBQztJQUMzQyxDQUFDOytHQUhVLHNCQUFzQjtnSEFBdEIsc0JBQXNCLFlBSHZCLFlBQVksRUFBRSxnQkFBZ0IsYUFDOUIsWUFBWSxFQUFFLGdCQUFnQjtnSEFFN0Isc0JBQXNCLFlBSHZCLFlBQVksRUFBRSxnQkFBZ0IsRUFDOUIsWUFBWSxFQUFFLGdCQUFnQjs7NEZBRTdCLHNCQUFzQjtrQkFKbEMsUUFBUTttQkFBQztvQkFDUixPQUFPLEVBQUUsQ0FBQyxZQUFZLEVBQUUsZ0JBQWdCLENBQUM7b0JBQ3pDLE9BQU8sRUFBRSxDQUFDLFlBQVksRUFBRSxnQkFBZ0IsQ0FBQztpQkFDMUMiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBOZ01vZHVsZSB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHsgUm91dGVyTW9kdWxlLCBSb3V0ZXIgfSBmcm9tICdAYW5ndWxhci9yb3V0ZXInO1xuaW1wb3J0IHsgTG9hZGluZ0Jhck1vZHVsZSwgTG9hZGluZ0JhclNlcnZpY2UgfSBmcm9tICdAbmd4LWxvYWRpbmctYmFyL2NvcmUnO1xuaW1wb3J0IHsgcmVnaXN0ZXJSb3V0ZXJMaXN0ZW5lciB9IGZyb20gJy4vcm91dGVyLnNlcnZpY2UnO1xuXG5ATmdNb2R1bGUoe1xuICBpbXBvcnRzOiBbUm91dGVyTW9kdWxlLCBMb2FkaW5nQmFyTW9kdWxlXSxcbiAgZXhwb3J0czogW1JvdXRlck1vZHVsZSwgTG9hZGluZ0Jhck1vZHVsZV0sXG59KVxuZXhwb3J0IGNsYXNzIExvYWRpbmdCYXJSb3V0ZXJNb2R1bGUge1xuICBjb25zdHJ1Y3Rvcihyb3V0ZXI6IFJvdXRlciwgbG9hZGVyOiBMb2FkaW5nQmFyU2VydmljZSkge1xuICAgIHJlZ2lzdGVyUm91dGVyTGlzdGVuZXIocm91dGVyLCBsb2FkZXIpKCk7XG4gIH1cbn1cbiJdfQ==