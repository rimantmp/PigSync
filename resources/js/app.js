import Alpine from 'alpinejs';
import { itemRows } from './item-rows';
import { appShell } from './app-shell';
import { formModal } from './form-modal';

window.Alpine = Alpine;

Alpine.data('itemRows', itemRows);
Alpine.data('appShell', appShell);
Alpine.data('formModal', formModal);

Alpine.start();
