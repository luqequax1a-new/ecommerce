import { Injectable } from '@angular/core';
import { HttpContextToken } from '@angular/common/http';
import { finalize, tap } from 'rxjs/operators';
import { HTTP_INTERCEPTORS } from '@angular/common/http';
import * as i0 from "@angular/core";
import * as i1 from "@ngx-loading-bar/core";
export const NGX_LOADING_BAR_IGNORED = new HttpContextToken(() => false);
export class LoadingBarInterceptor {
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
export function provideLoadingBarInterceptor() {
    return {
        provide: HTTP_INTERCEPTORS,
        useClass: LoadingBarInterceptor,
        multi: true,
    };
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibG9hZGluZy1iYXIuaW50ZXJjZXB0b3IuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi9wYWNrYWdlcy9odHRwLWNsaWVudC9zcmMvbG9hZGluZy1iYXIuaW50ZXJjZXB0b3IudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQ0EsT0FBTyxFQUFFLFVBQVUsRUFBRSxNQUFNLGVBQWUsQ0FBQztBQUMzQyxPQUFPLEVBQXdELGdCQUFnQixFQUFFLE1BQU0sc0JBQXNCLENBQUM7QUFFOUcsT0FBTyxFQUFFLFFBQVEsRUFBRSxHQUFHLEVBQUUsTUFBTSxnQkFBZ0IsQ0FBQztBQUUvQyxPQUFPLEVBQUUsaUJBQWlCLEVBQUUsTUFBTSxzQkFBc0IsQ0FBQzs7O0FBRXpELE1BQU0sQ0FBQyxNQUFNLHVCQUF1QixHQUFHLElBQUksZ0JBQWdCLENBQVUsR0FBRyxFQUFFLENBQUMsS0FBSyxDQUFDLENBQUM7QUFHbEYsTUFBTSxPQUFPLHFCQUFxQjtJQUNoQyxZQUFvQixNQUF5QjtRQUF6QixXQUFNLEdBQU4sTUFBTSxDQUFtQjtJQUFHLENBQUM7SUFFakQsU0FBUyxDQUFDLEdBQXFCLEVBQUUsSUFBaUI7UUFDaEQsSUFBSSxHQUFHLENBQUMsT0FBTyxDQUFDLEdBQUcsQ0FBQyx1QkFBdUIsQ0FBQyxLQUFLLElBQUksRUFBRTtZQUNyRCxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUMsR0FBRyxDQUFDLENBQUM7U0FDekI7UUFFRCxJQUFJLE9BQU8sR0FBRyxLQUFLLENBQUM7UUFDcEIsTUFBTSxHQUFHLEdBQUcsSUFBSSxDQUFDLE1BQU0sQ0FBQyxNQUFNLENBQUMsTUFBTSxDQUFDLENBQUM7UUFDdkMsT0FBTyxJQUFJLENBQUMsTUFBTSxDQUFDLEdBQUcsQ0FBQyxDQUFDLElBQUksQ0FDMUIsR0FBRyxDQUFDLEdBQUcsRUFBRTtZQUNQLElBQUksQ0FBQyxPQUFPLEVBQUU7Z0JBQ1osR0FBRyxDQUFDLEtBQUssRUFBRSxDQUFDO2dCQUNaLE9BQU8sR0FBRyxJQUFJLENBQUM7YUFDaEI7UUFDSCxDQUFDLENBQUMsRUFDRixRQUFRLENBQUMsR0FBRyxFQUFFLENBQUMsT0FBTyxJQUFJLEdBQUcsQ0FBQyxRQUFRLEVBQUUsQ0FBQyxDQUMxQyxDQUFDO0lBQ0osQ0FBQzsrR0FuQlUscUJBQXFCO21IQUFyQixxQkFBcUI7OzRGQUFyQixxQkFBcUI7a0JBRGpDLFVBQVU7O0FBdUJYLE1BQU0sVUFBVSw0QkFBNEI7SUFDMUMsT0FBTztRQUNMLE9BQU8sRUFBRSxpQkFBaUI7UUFDMUIsUUFBUSxFQUFFLHFCQUFxQjtRQUMvQixLQUFLLEVBQUUsSUFBSTtLQUNaLENBQUM7QUFDSixDQUFDIiwic291cmNlc0NvbnRlbnQiOlsiaW1wb3J0IHsgTG9hZGluZ0JhclNlcnZpY2UgfSBmcm9tICdAbmd4LWxvYWRpbmctYmFyL2NvcmUnO1xuaW1wb3J0IHsgSW5qZWN0YWJsZSB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHsgSHR0cEV2ZW50LCBIdHRwSW50ZXJjZXB0b3IsIEh0dHBIYW5kbGVyLCBIdHRwUmVxdWVzdCwgSHR0cENvbnRleHRUb2tlbiB9IGZyb20gJ0Bhbmd1bGFyL2NvbW1vbi9odHRwJztcbmltcG9ydCB7IE9ic2VydmFibGUgfSBmcm9tICdyeGpzJztcbmltcG9ydCB7IGZpbmFsaXplLCB0YXAgfSBmcm9tICdyeGpzL29wZXJhdG9ycyc7XG5pbXBvcnQgeyBQcm92aWRlciB9IGZyb20gJ0Bhbmd1bGFyL2NvcmUnO1xuaW1wb3J0IHsgSFRUUF9JTlRFUkNFUFRPUlMgfSBmcm9tICdAYW5ndWxhci9jb21tb24vaHR0cCc7XG5cbmV4cG9ydCBjb25zdCBOR1hfTE9BRElOR19CQVJfSUdOT1JFRCA9IG5ldyBIdHRwQ29udGV4dFRva2VuPGJvb2xlYW4+KCgpID0+IGZhbHNlKTtcblxuQEluamVjdGFibGUoKVxuZXhwb3J0IGNsYXNzIExvYWRpbmdCYXJJbnRlcmNlcHRvciBpbXBsZW1lbnRzIEh0dHBJbnRlcmNlcHRvciB7XG4gIGNvbnN0cnVjdG9yKHByaXZhdGUgbG9hZGVyOiBMb2FkaW5nQmFyU2VydmljZSkge31cblxuICBpbnRlcmNlcHQocmVxOiBIdHRwUmVxdWVzdDxhbnk+LCBuZXh0OiBIdHRwSGFuZGxlcik6IE9ic2VydmFibGU8SHR0cEV2ZW50PGFueT4+IHtcbiAgICBpZiAocmVxLmNvbnRleHQuZ2V0KE5HWF9MT0FESU5HX0JBUl9JR05PUkVEKSA9PT0gdHJ1ZSkge1xuICAgICAgcmV0dXJuIG5leHQuaGFuZGxlKHJlcSk7XG4gICAgfVxuXG4gICAgbGV0IHN0YXJ0ZWQgPSBmYWxzZTtcbiAgICBjb25zdCByZWYgPSB0aGlzLmxvYWRlci51c2VSZWYoJ2h0dHAnKTtcbiAgICByZXR1cm4gbmV4dC5oYW5kbGUocmVxKS5waXBlKFxuICAgICAgdGFwKCgpID0+IHtcbiAgICAgICAgaWYgKCFzdGFydGVkKSB7XG4gICAgICAgICAgcmVmLnN0YXJ0KCk7XG4gICAgICAgICAgc3RhcnRlZCA9IHRydWU7XG4gICAgICAgIH1cbiAgICAgIH0pLFxuICAgICAgZmluYWxpemUoKCkgPT4gc3RhcnRlZCAmJiByZWYuY29tcGxldGUoKSksXG4gICAgKTtcbiAgfVxufVxuXG5leHBvcnQgZnVuY3Rpb24gcHJvdmlkZUxvYWRpbmdCYXJJbnRlcmNlcHRvcigpOiBQcm92aWRlciB7XG4gIHJldHVybiB7XG4gICAgcHJvdmlkZTogSFRUUF9JTlRFUkNFUFRPUlMsXG4gICAgdXNlQ2xhc3M6IExvYWRpbmdCYXJJbnRlcmNlcHRvcixcbiAgICBtdWx0aTogdHJ1ZSxcbiAgfTtcbn1cbiJdfQ==