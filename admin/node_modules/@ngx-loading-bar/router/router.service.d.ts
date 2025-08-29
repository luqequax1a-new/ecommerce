import { Provider } from '@angular/core';
import { Router } from '@angular/router';
import { LoadingBarService } from '@ngx-loading-bar/core';
export declare function registerRouterListener(router: Router, loader: LoadingBarService): () => void;
export declare function provideLoadingBarRouter(): Provider;
