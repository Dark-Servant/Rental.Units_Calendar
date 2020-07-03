var VueComponentParams = {};

$('*[type="text/vue-component"]').each((unitNum, unitObj) => {
    var componentSelector = ($(unitObj).attr('id') || '').replace(/\-component$/i, '');
    if (!componentSelector) return;

    var paramSelector = componentSelector.replace(/\W(\w)/, (...parts) => parts[1].toUpperCase() );
    var params = VueComponentParams[paramSelector] ? VueComponentParams[paramSelector] : {};
    var props = $(unitObj).data('props');

    Vue.component(componentSelector, {
        ...params,
        props: props ? props.trim().split(/\s*,\s*/) : [],
        template: $(unitObj).html().trim().replace(/\s+/g, ' ')
    });
});