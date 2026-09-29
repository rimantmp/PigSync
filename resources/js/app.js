import Alpine from 'alpinejs';
import { itemRows } from './item-rows';

window.Alpine = Alpine;

Alpine.data('itemRows', itemRows);

Alpine.start();
