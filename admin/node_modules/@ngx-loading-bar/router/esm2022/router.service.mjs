import { APP_INITIALIZER } from '@angular/core';
import { Router, NavigationStart, NavigationError, NavigationEnd, NavigationCancel, } from '@angular/router';
import { LoadingBarService } from '@ngx-loading-bar/core';
function getCurrentNavigationState(router) {
    // `getCurrentNavigation` only available in angular `7.2`
    const currentNavigation = router.getCurrentNavigation && router.getCurrentNavigation();
    if (currentNavigation && currentNavigation.extras) {
        return currentNavigation.extras.state;
    }
    return {};
}
export function registerRouterListener(router, loader) {
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
export function provideLoadingBarRouter() {
    return {
        provide: APP_INITIALIZER,
        useFactory: registerRouterListener,
        deps: [Router, LoadingBarService],
        multi: true,
    };
}
//# sourceMappingURL=data:application/json;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoicm91dGVyLnNlcnZpY2UuanMiLCJzb3VyY2VSb290IjoiIiwic291cmNlcyI6WyIuLi8uLi8uLi8uLi9wYWNrYWdlcy9yb3V0ZXIvc3JjL3JvdXRlci5zZXJ2aWNlLnRzIl0sIm5hbWVzIjpbXSwibWFwcGluZ3MiOiJBQUFBLE9BQU8sRUFBRSxlQUFlLEVBQXdCLE1BQU0sZUFBZSxDQUFDO0FBQ3RFLE9BQU8sRUFDTCxNQUFNLEVBQ04sZUFBZSxFQUNmLGVBQWUsRUFDZixhQUFhLEVBQ2IsZ0JBQWdCLEdBRWpCLE1BQU0saUJBQWlCLENBQUM7QUFDekIsT0FBTyxFQUFFLGlCQUFpQixFQUFFLE1BQU0sdUJBQXVCLENBQUM7QUFFMUQsU0FBUyx5QkFBeUIsQ0FBQyxNQUFXO0lBQzVDLHlEQUF5RDtJQUN6RCxNQUFNLGlCQUFpQixHQUFHLE1BQU0sQ0FBQyxvQkFBb0IsSUFBSSxNQUFNLENBQUMsb0JBQW9CLEVBQUUsQ0FBQztJQUN2RixJQUFJLGlCQUFpQixJQUFJLGlCQUFpQixDQUFDLE1BQU0sRUFBRTtRQUNqRCxPQUFPLGlCQUFpQixDQUFDLE1BQU0sQ0FBQyxLQUFLLENBQUM7S0FDdkM7SUFFRCxPQUFPLEVBQUUsQ0FBQztBQUNaLENBQUM7QUFFRCxNQUFNLFVBQVUsc0JBQXNCLENBQUMsTUFBYyxFQUFFLE1BQXlCO0lBQzlFLE9BQU8sR0FBRyxFQUFFO1FBQ1YsTUFBTSxHQUFHLEdBQUcsTUFBTSxDQUFDLE1BQU0sQ0FBQyxRQUFRLENBQUMsQ0FBQztRQUNwQyxNQUFNLENBQUMsTUFBTSxDQUFDLFNBQVMsQ0FBQyxDQUFDLEtBQUssRUFBRSxFQUFFO1lBQ2hDLE1BQU0sUUFBUSxHQUFHLHlCQUF5QixDQUFDLE1BQU0sQ0FBQyxDQUFDO1lBQ25ELElBQUksUUFBUSxJQUFJLFFBQVEsQ0FBQyxnQkFBZ0IsRUFBRTtnQkFDekMsT0FBTzthQUNSO1lBRUQsSUFBSSxLQUFLLFlBQVksZUFBZSxFQUFFO2dCQUNwQyxHQUFHLENBQUMsS0FBSyxFQUFFLENBQUM7YUFDYjtZQUVELElBQUksS0FBSyxZQUFZLGVBQWUsSUFBSSxLQUFLLFlBQVksYUFBYSxJQUFJLEtBQUssWUFBWSxnQkFBZ0IsRUFBRTtnQkFDM0csR0FBRyxDQUFDLFFBQVEsRUFBRSxDQUFDO2FBQ2hCO1FBQ0gsQ0FBQyxDQUFDLENBQUM7SUFDTCxDQUFDLENBQUM7QUFDSixDQUFDO0FBRUQsTUFBTSxVQUFVLHVCQUF1QjtJQUNyQyxPQUFPO1FBQ0wsT0FBTyxFQUFFLGVBQWU7UUFDeEIsVUFBVSxFQUFFLHNCQUFzQjtRQUNsQyxJQUFJLEVBQUUsQ0FBQyxNQUFNLEVBQUUsaUJBQWlCLENBQUM7UUFDakMsS0FBSyxFQUFFLElBQUk7S0FDWixDQUFDO0FBQ0osQ0FBQyIsInNvdXJjZXNDb250ZW50IjpbImltcG9ydCB7IEFQUF9JTklUSUFMSVpFUiwgSW5qZWN0YWJsZSwgUHJvdmlkZXIgfSBmcm9tICdAYW5ndWxhci9jb3JlJztcbmltcG9ydCB7XG4gIFJvdXRlcixcbiAgTmF2aWdhdGlvblN0YXJ0LFxuICBOYXZpZ2F0aW9uRXJyb3IsXG4gIE5hdmlnYXRpb25FbmQsXG4gIE5hdmlnYXRpb25DYW5jZWwsXG4gIFJPVVRFUl9JTklUSUFMSVpFUixcbn0gZnJvbSAnQGFuZ3VsYXIvcm91dGVyJztcbmltcG9ydCB7IExvYWRpbmdCYXJTZXJ2aWNlIH0gZnJvbSAnQG5neC1sb2FkaW5nLWJhci9jb3JlJztcblxuZnVuY3Rpb24gZ2V0Q3VycmVudE5hdmlnYXRpb25TdGF0ZShyb3V0ZXI6IGFueSkge1xuICAvLyBgZ2V0Q3VycmVudE5hdmlnYXRpb25gIG9ubHkgYXZhaWxhYmxlIGluIGFuZ3VsYXIgYDcuMmBcbiAgY29uc3QgY3VycmVudE5hdmlnYXRpb24gPSByb3V0ZXIuZ2V0Q3VycmVudE5hdmlnYXRpb24gJiYgcm91dGVyLmdldEN1cnJlbnROYXZpZ2F0aW9uKCk7XG4gIGlmIChjdXJyZW50TmF2aWdhdGlvbiAmJiBjdXJyZW50TmF2aWdhdGlvbi5leHRyYXMpIHtcbiAgICByZXR1cm4gY3VycmVudE5hdmlnYXRpb24uZXh0cmFzLnN0YXRlO1xuICB9XG5cbiAgcmV0dXJuIHt9O1xufVxuXG5leHBvcnQgZnVuY3Rpb24gcmVnaXN0ZXJSb3V0ZXJMaXN0ZW5lcihyb3V0ZXI6IFJvdXRlciwgbG9hZGVyOiBMb2FkaW5nQmFyU2VydmljZSkge1xuICByZXR1cm4gKCkgPT4ge1xuICAgIGNvbnN0IHJlZiA9IGxvYWRlci51c2VSZWYoJ3JvdXRlcicpO1xuICAgIHJvdXRlci5ldmVudHMuc3Vic2NyaWJlKChldmVudCkgPT4ge1xuICAgICAgY29uc3QgbmF2U3RhdGUgPSBnZXRDdXJyZW50TmF2aWdhdGlvblN0YXRlKHJvdXRlcik7XG4gICAgICBpZiAobmF2U3RhdGUgJiYgbmF2U3RhdGUuaWdub3JlTG9hZGluZ0Jhcikge1xuICAgICAgICByZXR1cm47XG4gICAgICB9XG5cbiAgICAgIGlmIChldmVudCBpbnN0YW5jZW9mIE5hdmlnYXRpb25TdGFydCkge1xuICAgICAgICByZWYuc3RhcnQoKTtcbiAgICAgIH1cblxuICAgICAgaWYgKGV2ZW50IGluc3RhbmNlb2YgTmF2aWdhdGlvbkVycm9yIHx8IGV2ZW50IGluc3RhbmNlb2YgTmF2aWdhdGlvbkVuZCB8fCBldmVudCBpbnN0YW5jZW9mIE5hdmlnYXRpb25DYW5jZWwpIHtcbiAgICAgICAgcmVmLmNvbXBsZXRlKCk7XG4gICAgICB9XG4gICAgfSk7XG4gIH07XG59XG5cbmV4cG9ydCBmdW5jdGlvbiBwcm92aWRlTG9hZGluZ0JhclJvdXRlcigpOiBQcm92aWRlciB7XG4gIHJldHVybiB7XG4gICAgcHJvdmlkZTogQVBQX0lOSVRJQUxJWkVSLFxuICAgIHVzZUZhY3Rvcnk6IHJlZ2lzdGVyUm91dGVyTGlzdGVuZXIsXG4gICAgZGVwczogW1JvdXRlciwgTG9hZGluZ0JhclNlcnZpY2VdLFxuICAgIG11bHRpOiB0cnVlLFxuICB9O1xufVxuIl19