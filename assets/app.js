import './bootstrap.js';
/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css';

console.log('This log comes from assets/app.js - welcome to AssetMapper! 🎉');

function initCategorySelect() {
    const categorySelect = document.querySelector('select[multiple][name$="[categories][]"]');
    if (categorySelect && typeof $(categorySelect).select2 === "function") {
        $(categorySelect).select2({ placeholder: 'Select categories', allowClear: true });
    }
}
document.addEventListener('DOMContentLoaded', initCategorySelect);
document.addEventListener('turbo:render', initCategorySelect);
document.addEventListener('turbo:frame-load', initCategorySelect);
