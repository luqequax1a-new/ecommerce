import { NgModule } from '@angular/core';
import { HttpClientModule, HTTP_INTERCEPTORS } from '@angular/common/http';
import { LoadingBarInterceptor } from './loading-bar.interceptor';
import { LoadingBarModule } from '@ngx-loading-bar/core';
import * as i0 from "@angular/core";
export class LoadingBarHttpClientModule {
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
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiaHR0cC5tb2R1bGUuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi9wYWNrYWdlcy9odHRwLWNsaWVudC9zcmMvaHR0cC5tb2R1bGUudHMiXSwibmFtZXMiOltdLCJtYXBwaW5ncyI6IkFBQUEsT0FBTyxFQUFFLFFBQVEsRUFBRSxNQUFNLGVBQWUsQ0FBQztBQUN6QyxPQUFPLEVBQUUsZ0JBQWdCLEVBQUUsaUJBQWlCLEVBQUUsTUFBTSxzQkFBc0IsQ0FBQztBQUMzRSxPQUFPLEVBQUUscUJBQXFCLEVBQUUsTUFBTSwyQkFBMkIsQ0FBQztBQUNsRSxPQUFPLEVBQUUsZ0JBQWdCLEVBQUUsTUFBTSx1QkFBdUIsQ0FBQzs7QUFhekQsTUFBTSxPQUFPLDBCQUEwQjsrR0FBMUIsMEJBQTBCO2dIQUExQiwwQkFBMEIsWUFWM0IsZ0JBQWdCLEVBQUUsZ0JBQWdCLGFBQ2xDLGdCQUFnQixFQUFFLGdCQUFnQjtnSEFTakMsMEJBQTBCLGFBUjFCO1lBQ1Q7Z0JBQ0UsT0FBTyxFQUFFLGlCQUFpQjtnQkFDMUIsUUFBUSxFQUFFLHFCQUFxQjtnQkFDL0IsS0FBSyxFQUFFLElBQUk7YUFDWjtTQUNGLFlBUlMsZ0JBQWdCLEVBQUUsZ0JBQWdCLEVBQ2xDLGdCQUFnQixFQUFFLGdCQUFnQjs7NEZBU2pDLDBCQUEwQjtrQkFYdEMsUUFBUTttQkFBQztvQkFDUixPQUFPLEVBQUUsQ0FBQyxnQkFBZ0IsRUFBRSxnQkFBZ0IsQ0FBQztvQkFDN0MsT0FBTyxFQUFFLENBQUMsZ0JBQWdCLEVBQUUsZ0JBQWdCLENBQUM7b0JBQzdDLFNBQVMsRUFBRTt3QkFDVDs0QkFDRSxPQUFPLEVBQUUsaUJBQWlCOzRCQUMxQixRQUFRLEVBQUUscUJBQXFCOzRCQUMvQixLQUFLLEVBQUUsSUFBSTt5QkFDWjtxQkFDRjtpQkFDRiIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IE5nTW9kdWxlIH0gZnJvbSAnQGFuZ3VsYXIvY29yZSc7XG5pbXBvcnQgeyBIdHRwQ2xpZW50TW9kdWxlLCBIVFRQX0lOVEVSQ0VQVE9SUyB9IGZyb20gJ0Bhbmd1bGFyL2NvbW1vbi9odHRwJztcbmltcG9ydCB7IExvYWRpbmdCYXJJbnRlcmNlcHRvciB9IGZyb20gJy4vbG9hZGluZy1iYXIuaW50ZXJjZXB0b3InO1xuaW1wb3J0IHsgTG9hZGluZ0Jhck1vZHVsZSB9IGZyb20gJ0BuZ3gtbG9hZGluZy1iYXIvY29yZSc7XG5cbkBOZ01vZHVsZSh7XG4gIGltcG9ydHM6IFtIdHRwQ2xpZW50TW9kdWxlLCBMb2FkaW5nQmFyTW9kdWxlXSxcbiAgZXhwb3J0czogW0h0dHBDbGllbnRNb2R1bGUsIExvYWRpbmdCYXJNb2R1bGVdLFxuICBwcm92aWRlcnM6IFtcbiAgICB7XG4gICAgICBwcm92aWRlOiBIVFRQX0lOVEVSQ0VQVE9SUyxcbiAgICAgIHVzZUNsYXNzOiBMb2FkaW5nQmFySW50ZXJjZXB0b3IsXG4gICAgICBtdWx0aTogdHJ1ZSxcbiAgICB9LFxuICBdLFxufSlcbmV4cG9ydCBjbGFzcyBMb2FkaW5nQmFySHR0cENsaWVudE1vZHVsZSB7fVxuIl19