import { Injectable, Optional } from '@angular/core';
import { PLATFORM_ID, Inject } from '@angular/core';
import { isPlatformBrowser } from '@angular/common';
import { LoadingBarState } from './loading-bar.state';
import { Subject, combineLatest, Observable } from 'rxjs';
import { switchMap, map, startWith } from 'rxjs/operators';
import { LOADING_BAR_CONFIG } from './loading-bar.config';
import * as i0 from "@angular/core";
export class LoadingBarService {
    /** @deprecated use `value$` instead. */
    get progress$() {
        return this.value$;
    }
    constructor(platformId, config = {}, zone) {
        this.platformId = platformId;
        this.config = config;
        this.zone = zone;
        this.refs = {};
        this.streams$ = new Subject();
        this.value$ = this.streams$.pipe(startWith(null), switchMap(() => combineLatest(Object.keys(this.refs).map((s) => this.refs[s].value$))), runInZone(this.zone), map((v) => Math.max(0, ...v)));
    }
    /** @deprecated use `useRef` instead. */
    start(initialValue = 2) {
        this.useRef().start(initialValue);
    }
    /** @deprecated use `useRef` instead. */
    set(value) {
        this.useRef().set(value);
    }
    /** @deprecated use `useRef` instead. */
    increment(value) {
        this.useRef().increment(value);
    }
    /** @deprecated use `useRef` instead. */
    complete() {
        this.useRef().complete();
    }
    /** @deprecated use `useRef` instead. */
    stop() {
        this.useRef().stop();
    }
    useRef(id = 'default') {
        if (!this.refs[id]) {
            this.refs[id] = new LoadingBarState(this.config);
            this.streams$.next();
            if (!isPlatformBrowser(this.platformId)) {
                this.refs[id].disable();
            }
        }
        return this.refs[id];
    }
    static { this.ɵfac = i0.ɵɵngDeclareFactory({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarService, deps: [{ token: PLATFORM_ID }, { token: LOADING_BAR_CONFIG, optional: true }, { token: i0.NgZone, optional: true }], target: i0.ɵɵFactoryTarget.Injectable }); }
    static { this.ɵprov = i0.ɵɵngDeclareInjectable({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarService, providedIn: 'root' }); }
}
i0.ɵɵngDeclareClassMetadata({ minVersion: "12.0.0", version: "16.2.12", ngImport: i0, type: LoadingBarService, decorators: [{
            type: Injectable,
            args: [{ providedIn: 'root' }]
        }], ctorParameters: function () { return [{ type: Object, decorators: [{
                    type: Inject,
                    args: [PLATFORM_ID]
                }] }, { type: undefined, decorators: [{
                    type: Optional
                }, {
                    type: Inject,
                    args: [LOADING_BAR_CONFIG]
                }] }, { type: i0.NgZone, decorators: [{
                    type: Optional
                }] }]; } });
// https://stackoverflow.com/a/57452361/1406096
export function runInZone(zone) {
    if (!zone) {
        return (source) => source;
    }
    return (source) => new Observable((observer) => source.subscribe((value) => zone.run(() => observer.next(value)), (e) => zone.run(() => observer.error(e)), () => zone.run(() => observer.complete())));
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoibG9hZGluZy1iYXIuc2VydmljZS5qcyIsInNvdXJjZVJvb3QiOiIiLCJzb3VyY2VzIjpbIi4uLy4uLy4uLy4uL3BhY2thZ2VzL2NvcmUvc3JjL2xvYWRpbmctYmFyLnNlcnZpY2UudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUEsT0FBTyxFQUFFLFVBQVUsRUFBVSxRQUFRLEVBQUUsTUFBTSxlQUFlLENBQUM7QUFDN0QsT0FBTyxFQUFFLFdBQVcsRUFBRSxNQUFNLEVBQUUsTUFBTSxlQUFlLENBQUM7QUFDcEQsT0FBTyxFQUFFLGlCQUFpQixFQUFFLE1BQU0saUJBQWlCLENBQUM7QUFDcEQsT0FBTyxFQUFFLGVBQWUsRUFBRSxNQUFNLHFCQUFxQixDQUFDO0FBQ3RELE9BQU8sRUFBRSxPQUFPLEVBQUUsYUFBYSxFQUFFLFVBQVUsRUFBb0IsTUFBTSxNQUFNLENBQUM7QUFDNUUsT0FBTyxFQUFFLFNBQVMsRUFBRSxHQUFHLEVBQUUsU0FBUyxFQUFFLE1BQU0sZ0JBQWdCLENBQUM7QUFDM0QsT0FBTyxFQUFFLGtCQUFrQixFQUFvQixNQUFNLHNCQUFzQixDQUFDOztBQUc1RSxNQUFNLE9BQU8saUJBQWlCO0lBVTVCLHdDQUF3QztJQUN4QyxJQUFJLFNBQVM7UUFDWCxPQUFPLElBQUksQ0FBQyxNQUFNLENBQUM7SUFDckIsQ0FBQztJQUVELFlBQytCLFVBQWtCLEVBQ0MsU0FBMkIsRUFBRSxFQUN6RCxJQUFhO1FBRkosZUFBVSxHQUFWLFVBQVUsQ0FBUTtRQUNDLFdBQU0sR0FBTixNQUFNLENBQXVCO1FBQ3pELFNBQUksR0FBSixJQUFJLENBQVM7UUFqQmxCLFNBQUksR0FBc0MsRUFBRSxDQUFDO1FBQzdDLGFBQVEsR0FBRyxJQUFJLE9BQU8sRUFBUSxDQUFDO1FBQ3ZDLFdBQU0sR0FBRyxJQUFJLENBQUMsUUFBUSxDQUFDLElBQUksQ0FDbEMsU0FBUyxDQUFDLElBQUksQ0FBQyxFQUNmLFNBQVMsQ0FBQyxHQUFHLEVBQUUsQ0FBQyxhQUFhLENBQUMsTUFB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LFFBQVEsRUFBRSxDQUFDLENBQzFDLENBQ0YsQ0FBQztBQUNOLENBQUMiLCJzb3VyY2VzQ29udGVudCI6WyJpbXBvcnQgeyBJbmplY3RhYmxlLCBOZ1pvbmUsIE9wdGlvbmFsIH0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQgeyBQTEFURk9STV9JRCwgSW5qZWN0IH0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQgeyBpc1BsYXRmb3JtQnJvd3NlciB9IGZyb20gJ0Bhbmd1bGFyL2NvbW1vbic7XG5pbXBvcnQgeyBMb2FkaW5nQmFyU3RhdGUgfSBmcm9tICcuL2xvYWRpbmctYmFyLnN0YXRlJztcbmltcG9ydCB7IFN1YmplY3QsIGNvbWJpbmVMYXRlc3QsIE9ic2VydmFibGUsIE9wZXJhdG9yRnVuY3Rpb24gfSBmcm9tICdyeGpzJztcbmltcG9ydCB7IHN3aXRjaE1hcCwgbWFwLCBzdGFydFdpdGggfSBmcm9tICdyeGpzL29wZXJhdG9ycyc7XG5pbXBvcnQgeyBMT0FESU5HX0JBUl9DT05GSUcsIExvYWRpbmdCYXJDb25maWcgfSBmcm9tICcuL2xvYWRpbmctYmFyLmNvbmZpZyc7XG5cbkBJbmplY3RhYmxlKHsgcHJvdmlkZWRJbjogJ3Jvb3QnIH0pXG5leHBvcnQgY2xhc3MgTG9hZGluZ0JhclNlcnZpY2Uge1xuICBwcml2YXRlIHJlYWRvbmx5IHJlZnM6IHsgW2lkOiBzdHJpbmddOiBMb2FkaW5nQmFyU3RhdGUgfSA9IHt9O1xuICBwcml2YXRlIHJlYWRvbmx5IHN0cmVhbXMkID0gbmV3IFN1YmplY3Q8dm9pZD4oKTtcbiAgcmVhZG9ubHkgdmFsdWUkID0gdGhpcy5zdHJlYW1zJC5waXBlKFxuICAgIHN0YXJ0V2l0aChudWxsKSxcbiAgICBzd2l0Y2hNYXAoKCkgPT4gY29tYmluZUxhdGVzdChPYmplY3Qua2V5cyh0aGlzLnJlZnMpLm1hcCgocykgPT4gdGhpcy5yZWZzW3NdLnZhbHVlJCkpKSxcbiAgICBydW5JblpvbmUodGhpcy56b25lKSxcbiAgICBtYXAoKHYpID0+IE1hdGgubWF4KDAsIC4uLnYpKSxcbiAgKTtcblxuICAvKiogQGRlcHJlY2F0ZWQgdXNlIGB2YWx1ZSRgIGluc3RlYWQuICovXG4gIGdldCBwcm9ncmVzcyQoKSB7XG4gICAgcmV0dXJuIHRoaXMudmFsdWUkO1xuICB9XG5cbiAgY29uc3RydWN0b3IoXG4gICAgQEluamVjdChQTEFURk9STV9JRCkgcHJpdmF0ZSBwbGF0Zm9ybUlkOiBPYmplY3QsXG4gICAgQE9wdGlvbmFsKCkgQEluamVjdChMT0FESU5HX0JBUl9DT05GSUcpIHByaXZhdGUgY29uZmlnOiBMb2FkaW5nQmFyQ29uZmlnID0ge30sXG4gICAgQE9wdGlvbmFsKCkgcHJpdmF0ZSB6b25lPzogTmdab25lLFxuICApIHt9XG5cbiAgLyoqIEBkZXByZWNhdGVkIHVzZSBgdXNlUmVmYCBpbnN0ZWFkLiAqL1xuICBzdGFydChpbml0aWFsVmFsdWUgPSAyKSB7XG4gICAgdGhpcy51c2VSZWYoKS5zdGFydChpbml0aWFsVmFsdWUpO1xuICB9XG5cbiAgLyoqIEBkZXByZWNhdGVkIHVzZSBgdXNlUmVmYCBpbnN0ZWFkLiAqL1xuICBzZXQodmFsdWU6IG51bWJlcikge1xuICAgIHRoaXMudXNlUmVmKCkuc2V0KHZhbHVlKTtcbiAgfVxuXG4gIC8qKiBAZGVwcmVjYXRlZCB1c2UgYHVzZVJlZmAgaW5zdGVhZC4gKi9cbiAgaW5jcmVtZW50KHZhbHVlPzogbnVtYmVyKSB7XG4gICAgdGhpcy51c2VSZWYoKS5pbmNyZW1lbnQodmFsdWUpO1xuICB9XG5cbiAgLyoqIEBkZXByZWNhdGVkIHVzZSBgdXNlUmVmYCBpbnN0ZWFkLiAqL1xuICBjb21wbGV0ZSgpIHtcbiAgICB0aGlzLnVzZVJlZigpLmNvbXBsZXRlKCk7XG4gIH1cblxuICAvKiogQGRlcHJlY2F0ZWQgdXNlIGB1c2VSZWZgIGluc3RlYWQuICovXG4gIHN0b3AoKSB7XG4gICAgdGhpcy51c2VSZWYoKS5zdG9wKCk7XG4gIH1cblxuICB1c2VSZWYoaWQ6IHN0cmluZyA9ICdkZWZhdWx0Jyk6IExvYWRpbmdCYXJTdGF0ZSB7XG4gICAgaWYgKCF0aGlzLnJlZnNbaWRdKSB7XG4gICAgICB0aGlzLnJlZnNbaWRdID0gbmV3IExvYWRpbmdCYXJTdGF0ZSh0aGlzLmNvbmZpZyk7XG4gICAgICB0aGlzLnN0cmVhbXMkLm5leHQoKTtcblxuICAgICAgaWYgKCFpc1BsYXRmb3JtQnJvd3Nlcih0aGlzLnBsYXRmb3JtSWQpKSB7XG4gICAgICAgIHRoaXMucmVmc1tpZF0uZGlzYWJsZSgpO1xuICAgICAgfVxuICAgIH1cblxuICAgIHJldHVybiB0aGlzLnJlZnNbaWRdO1xuICB9XG59XG5cbi8vIGh0dHBzOi8vc3RhY2tvdmVyZmxvdy5jb20vYS81NzQ1MjM2MS8xNDA2MDk2XG5leHBvcnQgZnVuY3Rpb24gcnVuSW5ab25lPFQ+KHpvbmU6IE5nWm9uZSk6IE9wZXJhdG9yRnVuY3Rpb248VCwgVD4ge1xuICBpZiAoIXpvbmUpIHtcbiAgICByZXR1cm4gKHNvdXJjZSkgPT4gc291cmNlO1xuICB9XG5cbiAgcmV0dXJuIChzb3VyY2UpID0+XG4gICAgbmV3IE9ic2VydmFibGUoKG9ic2VydmVyKSA9PlxuICAgICAgc291cmNlLnN1YnNjcmliZShcbiAgICAgICAgKHZhbHVlOiBUKSA9PiB6b25lLnJ1bigoKSA9PiBvYnNlcnZlci5uZXh0KHZhbHVlKSksXG4gICAgICAgIChlOiBhbnkpID0+IHpvbmUucnVuKCgpID0+IG9ic2VydmVyLmVycm9yKGUpKSxcbiAgICAgICAgKCkgPT4gem9uZS5ydW4oKCkgPT4gb2JzZXJ2ZXIuY29tcGxldGUoKSksXG4gICAgICApLFxuICAgICk7XG59XG4iXX0=