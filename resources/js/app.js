import Alpine from 'alpinejs';
import { itemRows } from './item-rows';
import { appShell } from './app-shell';

window.Alpine = Alpine;

Alpine.data('itemRows', itemRows);
Alpine.data('appShell', appShell);

Alpine.start();
